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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Project\Entity\Project;

/**
 * @category    Calendar
 * @package     Repository
 */
class Calendar extends EntityRepository
{
    /**
     * @param              $which
     * @param bool $filterForAccess
     * @param Contact|null $contact
     * @param null $year
     * @param null $type
     *
     * @return Query
     */
    public function findCalendarItems(
        $which,
        $filterForAccess = true,
        Contact $contact = null,
        $year = null,
        $type = null
    ): Query {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_calendar');
        $qb->from(Entity\Calendar::class, 'calendar_entity_calendar');


        switch ($which) {
            case CalendarService::WHICH_UPCOMING:
                $qb->andWhere('calendar_entity_calendar.dateEnd >= ?1');
                $qb->orderBy('calendar_entity_calendar.dateFrom', 'ASC');
                $qb->setParameter(1, new \DateTime());
                $qb->andWhere('calendar_entity_calendar.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                break;
            case CalendarService::WHICH_PAST:
                $qb->andWhere('calendar_entity_calendar.dateEnd < ?1');

                if (null !== $type) {
                    $qb->andWhere('calendar_entity_calendar.type = ?9');
                    $qb->setParameter(9, $type);
                }
                $qb->orderBy('calendar_entity_calendar.dateEnd', 'DESC');
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
                $projectCalendarSubSelect->from('Project\Entity\Calendar\Calendar', 'projectCalendar');
                $projectCalendarSubSelect->join('projectCalendar.calendar', 'calendar');
                $qb->andWhere($qb->expr()->in('calendar_entity_calendar.id', $projectCalendarSubSelect->getDQL()));
                break;
            case CalendarService::WHICH_FINAL:
                $qb->andWhere('calendar_entity_calendar.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                $qb->orderBy('calendar_entity_calendar.dateFrom', 'ASC');
                $qb->addOrderBy('calendar_entity_calendar.sequence', 'ASC');
                break;
            case CalendarService::WHICH_UPDATED:
                $qb->orderBy('calendar_entity_calendar.dateUpdated', 'DESC');
                $qb->andWhere('calendar_entity_calendar.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                break;
            case CalendarService::WHICH_ON_HOMEPAGE:
                $qb->andWhere('calendar_entity_calendar.dateEnd >= ?1');
                $qb->setParameter(1, new \DateTime());
                $qb->andWhere('calendar_entity_calendar.onHomepage = ?2');
                $qb->setParameter(2, Entity\Calendar::ON_HOMEPAGE);
                $qb->andWhere('calendar_entity_calendar.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                $qb->orderBy('calendar_entity_calendar.sequence', 'ASC');
                $qb->addOrderBy('calendar_entity_calendar.dateFrom', 'ASC');
                break;
        }


        if ($filterForAccess) {
            /**
             * When no contact is given, simply return all the public calendar items
             */
            if (is_null($contact)) {
                $contact = new Contact();
                $contact->setId(0);
                $access = new Access();
                $access->setAccess(Access::ACCESS_PUBLIC);
                $contact->setAccess([$access]);
            }
            $qb = $this->filterForAccess($qb, $contact);
        }

        if (!is_null($year)) {
            $emConfig = $this->getEntityManager()->getConfiguration();
            $emConfig->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');
            $qb->andWhere('YEAR(calendar_entity_calendar.dateEnd) = ?8');
            $qb->setParameter(8, (int)$year);
        }

        return $qb->getQuery();
    }

    /**
     * @param QueryBuilder $qb
     * @param Contact $contact
     *
     * @return QueryBuilder $qb
     */
    public function filterForAccess(QueryBuilder $qb, Contact $contact)
    {
        //Filter based on the type access type
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('type');
        $subSelect->from(Entity\Type::class, 'type');
        $subSelect->join('type.access', 'access');
        $subSelect->andWhere(
            $qb->expr()
                ->in('access.access', array_merge([strtolower(Access::ACCESS_PUBLIC)], $contact->getRoles()))
        );

        $subSelectCalendarContact = $this->_em->createQueryBuilder();
        $subSelectCalendarContact->select('calendar2');
        $subSelectCalendarContact->from('Calendar\Entity\Calendar', 'calendar2');
        $subSelectCalendarContact->join('calendar2.calendarContact', 'calenderContact2');
        $subSelectCalendarContact->join('calenderContact2.contact', 'contact2');
        $subSelectCalendarContact->andWhere('contact2.id = ' . $contact);

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->in(
                    'calendar_entity_calendar.type',
                    $subSelect->getDQL()
                ),
                $qb->expr()->in('calendar_entity_calendar', $subSelectCalendarContact->getDQL())
            )
        );

        return $qb;
    }

    /**
     * @param Project $project
     *
     * @return Entity\Calendar|null
     */
    public function findLatestProjectCalendar(Project $project)
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

    /**
     * @return array
     */
    public function findMinAndMaxYear(): array
    {
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');

        $dql
            = 'SELECT 
                        MIN(YEAR(calendar_entity_calendar.dateFrom)) AS minYear,
                        MAX(YEAR(calendar_entity_calendar.dateFrom)) AS maxYear
                   FROM Calendar\Entity\Calendar calendar_entity_calendar';


        $result = $this->_em->createQuery($dql)->getScalarResult();

        return array_shift($result);
    }

    /**
     * @param Project $project
     * @param \DateTime $dateTime
     *
     * @return Entity\Calendar|null
     */
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

    /**
     * @param Project $project
     * @param \DateTime $dateTime
     * @return Entity\Calendar|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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

    /**
     * Function which returns true/false based ont he fact if a user can view the calendar
     *
     * @param Entity\Calendar $calendar
     * @param Contact $contact
     *
     * @return bool
     */
    public function canViewCalendar(Entity\Calendar $calendar, Contact $contact): bool
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_calendar');
        $qb->from(Entity\Calendar::class, 'calendar_entity_calendar');

        if ($contact->isEmpty()) {
            $contact = new Contact();
            $contact->setId(0);
            $access = new Access();
            $access->setAccess(strtolower(Access::ACCESS_PUBLIC));
            $contact->setAccess([$access]);
        }

        $qb = $this->filterForAccess($qb, $contact);
        $qb->andWhere('calendar_entity_calendar = ?100');
        $qb->setParameter(100, $calendar);


        return !is_null($qb->getQuery()->getOneOrNullResult());
    }
}
