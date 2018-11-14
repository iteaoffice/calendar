<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category  Calendar
 * @package   Repository
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (calendar_entity_calendar) Copyright (c) 2004-2017 ITEA Office (https://itea3.org) (https://itea3.org)
 */
declare(strict_types=1);

namespace Calendar\Repository;

use Admin\Entity\Access;
use Calendar\Entity;
use Calendar\Service\CalendarService;
use Contact\Entity\Contact;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use DoctrineExtensions\Query\Mysql\Year;
use Project\Entity\Project;

/**
 * Class Calendar
 *
 * @package Calendar\Repository
 */
final class Calendar extends EntityRepository
{
    public function findCalendarItems(
        string $which,
        bool $filterForAccess = true,
        Contact $contact = null,
        int $year = null,
        string $type = null,
        ?QueryBuilder $limitQueryBuilder = null
    ): Query {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_calendar');
        $qb->from(Entity\Calendar::class, 'calendar_entity_calendar');

        //Add a sub-select to be able to filter on public
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('calendar_entity_calendar_type');
        $subSelect->from(Entity\Type::class, 'calendar_entity_calendar_type');
        $subSelect->join('calendar_entity_calendar_type.access', 'admin_entity_access');
        $subSelect->andWhere(
            $qb->expr()
                ->in('admin_entity_access.access', [Access::ACCESS_PUBLIC])
        );


        switch ($which) {
            case CalendarService::WHICH_UPCOMING:
                $qb->andWhere('calendar_entity_calendar.dateEnd >= ?1');
                $qb->orderBy('calendar_entity_calendar.dateFrom', 'ASC');
                $qb->setParameter(1, new \DateTime());
                $qb->andWhere('calendar_entity_calendar.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                break;
            case CalendarService::WHICH_PAST:
                $qb->andWhere('calendar_entity_calendar.dateFrom < ?1');

                if (null !== $type) {
                    $qb->andWhere('calendar_entity_calendar.type = ?9');
                    $qb->setParameter(9, $type);
                }
                $qb->orderBy('calendar_entity_calendar.dateFrom', 'DESC');
                $qb->setParameter(1, new \DateTime());
                break;
            case CalendarService::WHICH_REVIEWS:
                $qb->andWhere('calendar_entity_calendar.dateEnd >= ?1');
                $qb->orderBy('calendar_entity_calendar.dateFrom', 'ASC');
                $qb->setParameter(1, new \DateTime());
                $qb->andWhere('calendar_entity_calendar.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);

                $projectCalendarSubSelect = $this->_em->createQueryBuilder();
                $projectCalendarSubSelect->select('calendar.id');
                $projectCalendarSubSelect->from(\Project\Entity\Calendar\Calendar::class, 'projectCalendar');
                $projectCalendarSubSelect->join('projectCalendar.calendar', 'calendar');
                $qb->andWhere($qb->expr()->in('calendar_entity_calendar.id', $projectCalendarSubSelect->getDQL()));
                break;
            case CalendarService::WHICH_FINAL:
                $qb->andWhere('calendar_entity_calendar.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                $qb->orderBy('calendar_entity_calendar.dateFrom', Criteria::DESC);
                $qb->addOrderBy('calendar_entity_calendar.sequence', Criteria::ASC);
                break;
            case CalendarService::WHICH_UPDATED:
                $qb->orderBy('calendar_entity_calendar.dateUpdated', Criteria::DESC);
                $qb->andWhere('calendar_entity_calendar.final = ?3');
                $qb->andWhere($qb->expr()->isNotNull('calendar_entity_calendar.dateUpdated'));
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                break;
            case CalendarService::WHICH_ON_HOMEPAGE:
                $qb->andWhere('calendar_entity_calendar.dateEnd >= ?1');
                $qb->setParameter(1, new \DateTime());
                $qb->andWhere('calendar_entity_calendar.onHomepage = ?2');
                $qb->setParameter(2, Entity\Calendar::ON_HOMEPAGE);
                $qb->andWhere('calendar_entity_calendar.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                $qb->addOrderBy('calendar_entity_calendar.dateFrom', Criteria::ASC);
                $qb->orderBy('calendar_entity_calendar.sequence', Criteria::ASC);

                //We only want public events
                $qb->andWhere(
                    $qb->expr()->in('calendar_entity_calendar.type', $subSelect->getDQL())
                );

                break;
            case CalendarService::WHICH_HIGHLIGHT:
                $qb->andWhere('calendar_entity_calendar.dateEnd >= ?1');
                $qb->setParameter(1, new \DateTime());
                $qb->andWhere('calendar_entity_calendar.onHomepage = ?2');
                $qb->setParameter(2, Entity\Calendar::ON_HOMEPAGE);
                $qb->andWhere('calendar_entity_calendar.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                $qb->andWhere('calendar_entity_calendar.highlight = ?3');
                $qb->setParameter(3, Entity\Calendar::HIGHLIGHT);
                $qb->orderBy('calendar_entity_calendar.sequence', Criteria::ASC);
                $qb->addOrderBy('calendar_entity_calendar.dateFrom', Criteria::ASC);

                //We only want public events
                $qb->andWhere(
                    $qb->expr()->in('calendar_entity_calendar.type', $subSelect->getDQL())
                );
                break;
        }

        if ($filterForAccess) {
            /**
             * When no contact is given, simply return all the public calendar items
             */
            if (null === $contact) {
                $contact = new Contact();
                $contact->setId(0);
                $access = new Access();
                $access->setAccess(Access::ACCESS_PUBLIC);
                $contact->setAccess([$access]);
            }
            $qb = $this->filterForAccess($qb, $contact, $limitQueryBuilder);
        }

        if (null !== $year) {
            $emConfig = $this->getEntityManager()->getConfiguration();
            $emConfig->addCustomDatetimeFunction('YEAR', Year::class);
            $qb->andWhere('YEAR(calendar_entity_calendar.dateEnd) = ?8');
            $qb->setParameter(8, (int)$year);
        }

        return $qb->getQuery();
    }

    public function filterForAccess(
        QueryBuilder $qb,
        Contact $contact,
        ?QueryBuilder $limitQueryBuilder = null
    ): QueryBuilder {
        //Filter based on the type access type
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('type');
        $subSelect->from(Entity\Type::class, 'type');
        $subSelect->join('type.access', 'access');
        $subSelect->andWhere(
            $qb->expr()
                ->in('access.access', array_merge([strtolower(Access::ACCESS_PUBLIC)], $contact->getRoles()))
        );

        /**
         * When the permit gives no result, do nothing
         */
        if (null === $limitQueryBuilder) {
            $qb->andWhere(
                $qb->expr()->in('calendar_entity_calendar.type', $subSelect->getDQL())
            );
        } else {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in(
                        'calendar_entity_calendar.type',
                        $subSelect->getDQL()
                    ),
                    $qb->expr()->in('calendar_entity_calendar', $limitQueryBuilder->getDQL())
                )
            );
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
        $qb->setParameter(1, new \DateTime());
        $qb->andWhere('calendar_entity_calendar.final = ?3');
        $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
        $qb->setParameter('project', $project);

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findNextProjectCalendar(Project $project, \DateTime $dateTime): ?Entity\Calendar
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

    public function findPreviousProjectCalendar(Project $project, \DateTime $dateTime): ?Entity\Calendar
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

    public function canViewCalendar(Entity\Calendar $calendar, Contact $contact = null): bool
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_calendar');
        $qb->from(Entity\Calendar::class, 'calendar_entity_calendar');

        if (null === $contact) {
            $contact = new Contact();
            $contact->setId(0);
            $access = new Access();
            $access->setAccess(strtolower(Access::ACCESS_PUBLIC));
            $contact->setAccess([$access]);
        }

        $qb = $this->filterForAccess($qb, $contact);
        $qb->andWhere('calendar_entity_calendar = ?100');
        $qb->setParameter(100, $calendar);


        return null !== $qb->getQuery()->getOneOrNullResult();
    }
}
