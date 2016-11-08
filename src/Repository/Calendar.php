<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category  Calendar
 * @package   Repository
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */
namespace Calendar\Repository;

use Admin\Entity\Access;
use Calendar\Entity;
use Calendar\Service\CalendarService;
use Contact\Entity\Contact;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Project\Entity\Project;

/**
 * @category    Calendar
 * @package     Repository
 */
class Calendar extends EntityRepository
{
    /**
     * @param         $which
     * @param bool    $filterForAccess
     * @param Contact $contact
     * @param null    $year
     *
     * @return \Doctrine\ORM\Query
     */
    public function findCalendarItems(
        $which,
        $filterForAccess = true,
        Contact $contact = null,
        $year = null,
        $type = null
    ) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c');
        $qb->from("Calendar\Entity\Calendar", 'c');


        switch ($which) {
            case CalendarService::WHICH_UPCOMING:
                $qb->andWhere('c.dateEnd >= ?1');
                $qb->orderBy('c.dateFrom', 'ASC');
                $qb->setParameter(1, new \DateTime());
                $qb->andWhere('c.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                break;
            case CalendarService::WHICH_PAST:
                $qb->andWhere('c.dateEnd < ?1');

                if (null !== $type) {
                    $qb->andWhere('c.type = ?9');
                    $qb->setParameter(9, $type);
                }
                $qb->orderBy('c.dateEnd', 'DESC');
                $qb->setParameter(1, new \DateTime());
                break;
            case CalendarService::WHICH_REVIEWS:
                $qb->andWhere('c.dateEnd >= ?1');
                $qb->orderBy('c.dateFrom', 'ASC');
                $qb->setParameter(1, new \DateTime());
                $qb->andWhere('c.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                $projectCalendarSubSelect = $this->_em->createQueryBuilder();
                $projectCalendarSubSelect->select('calendar.id');
                $projectCalendarSubSelect->from('Project\Entity\Calendar\Calendar', 'projectCalendar');
                $projectCalendarSubSelect->join('projectCalendar.calendar', 'calendar');
                $qb->andWhere($qb->expr()->in('c.id', $projectCalendarSubSelect->getDQL()));
                break;
            case CalendarService::WHICH_UPDATED:
                $qb->orderBy('c.dateUpdated', 'DESC');
                $qb->andWhere('c.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                break;
            case CalendarService::WHICH_ON_HOMEPAGE:
                $qb->andWhere('c.dateEnd >= ?1');
                $qb->setParameter(1, new \DateTime());
                $qb->andWhere('c.onHomepage = ?2');
                $qb->setParameter(2, Entity\Calendar::ON_HOMEPAGE);
                $qb->andWhere('c.final = ?3');
                $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
                $qb->orderBy('c.sequence', 'ASC');
                $qb->addOrderBy('c.dateFrom', 'ASC');
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
        if (! is_null($year)) {
            $emConfig = $this->getEntityManager()->getConfiguration();
            $emConfig->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');
            $qb->andWhere('YEAR(c.dateEnd) = ?8');
            $qb->setParameter(8, (int)$year);
        }

        return $qb->getQuery();
    }

    /**
     * @param QueryBuilder $qb
     * @param Contact      $contact
     *
     * @return QueryBuilder $qb
     */
    public function filterForAccess(QueryBuilder $qb, Contact $contact)
    {
        //Filter based on the type access type
        $subSelect = $this->_em->createQueryBuilder();
        $subSelect->select('type');
        $subSelect->from('Calendar\Entity\Type', 'type');
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
                    'c.type',
                    $subSelect->getDQL()
                ),
                $qb->expr()->in('c', $subSelectCalendarContact->getDQL())
            )
        );

        return $qb;
    }

    /**
     * @param Project $project
     *
     * @return Calendar|null
     */
    public function findLatestProjectCalendar(Project $project)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c');
        $qb->from("Calendar\Entity\Calendar", 'c');

        $qb->join('c.projectCalendar', 'pc');
        $qb->andWhere('pc.project = :project');
        $qb->andWhere('c.dateEnd < ?1');
        $qb->orderBy('c.dateFrom', 'DESC');
        $qb->setParameter(1, new \DateTime());
        $qb->andWhere('c.final = ?3');
        $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);
        $qb->setParameter('project', $project);

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Project   $project
     * @param \DateTime $dateTime
     *
     * @return Calendar|null
     */
    public function findNextProjectCalendar(Project $project, \DateTime $dateTime)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c');
        $qb->from("Calendar\Entity\Calendar", 'c');

        $qb->join('c.projectCalendar', 'pc');
        $qb->andWhere('pc.project = :project');
        $qb->andWhere('c.dateEnd > ?1');
        $qb->andWhere('c.final = ?3');
        $qb->orderBy('c.dateFrom', 'ASC');

        $qb->setParameter(1, $dateTime);
        $qb->setParameter('project', $project);
        $qb->setParameter(3, Entity\Calendar::FINAL_FINAL);

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Project   $project
     * @param \DateTime $dateTime
     *
     * @return Calendar|null
     */
    public function findPreviousProjectCalendar(Project $project, \DateTime $dateTime)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c');
        $qb->from("Calendar\Entity\Calendar", 'c');

        $qb->join('c.projectCalendar', 'pc');
        $qb->andWhere('pc.project = :project');
        $qb->andWhere('c.dateEnd < ?1');
        $qb->andWhere('c.final = ?3');
        $qb->orderBy('c.dateFrom', 'DESC');

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
     * @param Contact         $contact
     *
     * @return bool
     */
    public function canViewCalendar(Entity\Calendar $calendar, Contact $contact = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c');
        $qb->from("Calendar\Entity\Calendar", 'c');

        if ($contact->isEmpty()) {
            $contact = new Contact();
            $contact->setId(0);
            $access = new Access();
            $access->setAccess(strtolower(Access::ACCESS_PUBLIC));
            $contact->setAccess([$access]);
        }

        $qb = $this->filterForAccess($qb, $contact);
        $qb->andWhere('c = ?100');
        $qb->setParameter(100, $calendar);


        return ! is_null($qb->getQuery()->getOneOrNullResult());
    }
}
