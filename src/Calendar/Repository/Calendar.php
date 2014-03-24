<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Repository
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Repository;

use Contact\Entity\Access;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Calendar\Entity;
use Calendar\Service\CalendarService;
use Contact\Entity\Contact;

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
    public function findCalendarItems($which, $filterForAccess = true, Contact $contact = null, $year = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c');
        $qb->from("Calendar\Entity\Calendar", 'c');

        switch ($which) {
            case CalendarService::WHICH_UPCOMING:
                $qb->andWhere('c.dateFrom >= ?1');
                $qb->orderBy('c.dateFrom', 'ASC');
                $qb->setParameter(1, new \DateTime());
                break;
            case CalendarService::WHICH_PAST:
                $qb->andWhere('c.dateFrom <= ?1');
                $qb->orderBy('c.dateEnd', 'DESC');
                $qb->setParameter(1, new \DateTime());
                break;
            case CalendarService::WHICH_REVIEWS:
                $qb->andWhere('c.dateEnd >= ?1');
                $qb->orderBy('c.dateFrom', 'ASC');

                $qb->setParameter(1, new \DateTime());

                $projectCalendarSubSelect = $this->_em->createQueryBuilder();
                $projectCalendarSubSelect->select('calendar.id');
                $projectCalendarSubSelect->from('Project\Entity\Calendar\Calendar', 'projectCalendar');
                $projectCalendarSubSelect->join('projectCalendar.calendar', 'calendar');

                $qb->andWhere($qb->expr()->in('c.id', $projectCalendarSubSelect->getDQL()));
                break;
            case CalendarService::WHICH_UPDATED;
                $qb->orderBy('c.dateUpdated', 'DESC');
                break;
            case CalendarService::WHICH_ON_HOMEPAGE;
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
                $access->setAccess('public');
                $contact->setAccess(array($access));
            }

            $qb = $this->filterForAccess($qb, $contact);
        }

        if (!is_null($year)) {
            $emConfig = $this->getEntityManager()->getConfiguration();
            $emConfig->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');

            $qb->andWhere('YEAR(c.dateEnd) = ?8');
            $qb->setParameter(8, (int) $year);
        }

        return $qb->getQuery();
    }

    /**
     * Function which returns true/false based ont he fact if a user can view the calendar
     *
     * @param Entity\Calendar $calendar
     * @param Contact         $contact
     *
     * @return bool
     */
    public function canViewCalendar(Entity\Calendar $calendar, Contact $contact)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c');
        $qb->from("Calendar\Entity\Calendar", 'c');

        $qb = $this->filterForAccess($qb, $contact);

        $qb->andWhere('c = ?100');
        $qb->setParameter(100, $calendar);

        return !is_null($qb->getQuery()->getOneOrNullResult());
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
        $subSelect->andWhere($qb->expr()->in('access.access', $contact->getRoles()));

        $subSelectCalendarContact = $this->_em->createQueryBuilder();
        $subSelectCalendarContact->select('calendar2');
        $subSelectCalendarContact->from('Calendar\Entity\Calendar', 'calendar2');
        $subSelectCalendarContact->join('calendar2.calendarContact', 'calenderContact2');
        $subSelectCalendarContact->join('calenderContact2.contact', 'contact2');
        $subSelectCalendarContact->andWhere('contact2.id = ' . $contact);

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->in('c.type', $subSelect->getDQL()),
                $qb->expr()->in('c', $subSelectCalendarContact->getDQL())
            )
        );

        return $qb;
    }
}
