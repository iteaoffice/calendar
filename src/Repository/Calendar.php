<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category  Calendar
 * @package   Repository
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (calendar_entity_calendar) Copyright (c) 2019 ITEA Office (https://itea3.org) (https://itea3.org)
 */
declare(strict_types=1);

namespace Calendar\Repository;

use Admin\Entity\Access;
use Calendar\Entity;
use Contact\Entity\Contact;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Project\Entity\Project;
use function strtolower;

/**
 * Class Calendar
 *
 * @package Calendar\Repository
 */
final class Calendar extends EntityRepository
{
    public function findCalendarItems(
        bool $upcoming = true,
        bool $reviews = true
    ): QueryBuilder {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_calendar');
        $qb->from(Entity\Calendar::class, 'calendar_entity_calendar');

        if ($upcoming) {
            $qb->andWhere('calendar_entity_calendar.dateEnd >= ?1');
            $qb->orderBy('calendar_entity_calendar.dateFrom', 'ASC');
            $qb->setParameter(1, new DateTime());
            $qb->andWhere('calendar_entity_calendar.final = ?3');
            $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
        }

        if ($reviews) {
            $qb->andWhere('calendar_entity_calendar.dateEnd >= ?1');
            $qb->orderBy('calendar_entity_calendar.dateFrom', 'ASC');
            $qb->setParameter(1, new DateTime());
            $qb->andWhere('calendar_entity_calendar.final = ?3');
            $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);

            $projectCalendarSubSelect = $this->_em->createQueryBuilder();
            $projectCalendarSubSelect->select('calendar.id');
            $projectCalendarSubSelect->from(\Project\Entity\Calendar\Calendar::class, 'projectCalendar');
            $projectCalendarSubSelect->join('projectCalendar.calendar', 'calendar');
            $qb->andWhere($qb->expr()->in('calendar_entity_calendar.id', $projectCalendarSubSelect->getDQL()));
        }

        return $qb;
    }

    public function findLatestProjectCalendar(Project $project): ?Entity\Calendar
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_calendar');
        $qb->from(Entity\Calendar::class, 'calendar_entity_calendar');

        $qb->join('calendar_entity_calendar.projectCalendar', 'pc');
        $qb->andWhere('pc.project = :project');
        $qb->andWhere('calendar_entity_calendar.dateEnd < ?1');
        $qb->orderBy('calendar_entity_calendar.dateFrom', 'DESC');
        $qb->setParameter(1, new DateTime());
        $qb->andWhere('calendar_entity_calendar.final = ?3');
        $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
        $qb->setParameter('project', $project);

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findNextProjectCalendar(Project $project, DateTime $dateTime): ?Entity\Calendar
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_calendar');
        $qb->from(Entity\Calendar::class, 'calendar_entity_calendar');

        $qb->join('calendar_entity_calendar.projectCalendar', 'pc');
        $qb->andWhere('pc.project = :project');
        $qb->andWhere('calendar_entity_calendar.dateEnd > ?1');
        $qb->andWhere('calendar_entity_calendar.final = ?3');
        $qb->orderBy('calendar_entity_calendar.dateFrom', 'ASC');

        $qb->setParameter(1, $dateTime);
        $qb->setParameter('project', $project);
        $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findPreviousProjectCalendar(Project $project, DateTime $dateTime): ?Entity\Calendar
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_calendar');
        $qb->from(Entity\Calendar::class, 'calendar_entity_calendar');

        $qb->join('calendar_entity_calendar.projectCalendar', 'pc');
        $qb->andWhere('pc.project = :project');
        $qb->andWhere('calendar_entity_calendar.dateEnd < ?1');
        $qb->andWhere('calendar_entity_calendar.final = ?3');
        $qb->orderBy('calendar_entity_calendar.dateFrom', 'DESC');

        $qb->setParameter(1, $dateTime);
        $qb->setParameter('project', $project);
        $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function isPublic(Entity\Calendar $calendar): bool
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_calendar');
        $qb->from(Entity\Calendar::class, 'calendar_entity_calendar');
        $qb->join('calendar_entity_calendar.type', 'calendar_entity_calendar_type');
        $qb->join('calendar_entity_calendar_type.access', 'admin_entity_access');
        $qb->andWhere('admin_entity_access.access = :access');
        $qb->andWhere('calendar_entity_calendar = :calendar');
        $qb->setParameter('access', strtolower(Access::ACCESS_PUBLIC));
        $qb->setParameter('calendar', $calendar);

        return null !== $qb->getQuery()->getOneOrNullResult();
    }

    public function findVisibleItems(
        array $roles,
        QueryBuilder $limitQueryBuilder
    ): array {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_calendar.id');
        $qb->from(Entity\Calendar::class, 'calendar_entity_calendar');

        $qb = $this->filterForAccess($qb, $roles, $limitQueryBuilder);

        $hiddenElements = [];
        foreach ($qb->getQuery()->getArrayResult() as $element) {
            $hiddenElements[] = $element['id'];
        }

        return $hiddenElements;
    }

    public function filterForAccess(
        QueryBuilder $qb,
        array $roles,
        QueryBuilder $limitQueryBuilder
    ): QueryBuilder {
        //Filter based on the type access type
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('calendar_entity_calendar_type');
        $subSelect->from(Entity\Type::class, 'calendar_entity_calendar_type');
        $subSelect->join('calendar_entity_calendar_type.access', 'admin_entity_access');
        $subSelect->andWhere($qb->expr()->in('admin_entity_access.access', $roles));

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->in(
                    'calendar_entity_calendar.type',
                    $subSelect->getDQL()
                ),
                $qb->expr()->in('calendar_entity_calendar', $limitQueryBuilder->getDQL())
            )
        );

        return $qb;
    }
}
