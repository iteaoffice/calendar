<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category  Calendar
 * @package   Repository
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Repository;

use Calendar\Entity;
use Calendar\Service\CalendarService;
use Contact\Entity\Contact as ContactEntity;
use Doctrine\ORM\EntityRepository;

/**
 * @category    Calendar
 * @package     Repository
 */
class Contact extends EntityRepository
{
    /**
     * @param                   $which
     * @param  ContactEntity    $contact
     * @return Entity\Contact[]
     */
    public function findCalendarContactByContact($which, ContactEntity $contact = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('cc');
        $qb->from("Calendar\Entity\Contact", 'cc');
        $qb->join('cc.calendar', "c");
        $qb->join('cc.contact', "contact");

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
        case CalendarService::WHICH_UPDATED:
            $qb->orderBy('c.dateUpdated', 'DESC');
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

        $qb->andWhere('cc.contact = ?10');
        $qb->addOrderBy('contact.lastName', 'ASC');
        $qb->setParameter(10, $contact);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param  ContactEntity   $contact
     * @param  Entity\Calendar $calendar
     * @return Entity\Contact
     */
    public function findCalendarContactByContactAndCalendar(ContactEntity $contact, Entity\Calendar $calendar)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('cc');
        $qb->from("Calendar\Entity\Contact", 'cc');

        $qb->andWhere('cc.contact = ?10');
        $qb->setParameter(10, $contact);

        $qb->andWhere('cc.calendar = ?11');
        $qb->setParameter(11, $calendar);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Entity\Calendar $calendar
     *
     * @return Entity\Contact[]
     */
    public function findCalendarContactsByCalendar(Entity\Calendar $calendar)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('cc');
        $qb->from("Calendar\Entity\Contact", 'cc');
        $qb->join("cc.contact", 'contact');

        $qb->andWhere('cc.calendar = ?11');
        $qb->setParameter(11, $calendar);
        $qb->addOrderBy('contact.lastName', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param  Entity\Calendar  $calendar
     * @return Entity\Contact[]
     */
    public function findGeneralCalendarContactByCalendar(Entity\Calendar $calendar)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('cc');
        $qb->from("Calendar\Entity\Contact", 'cc');
        $qb->join('cc.contact', 'contact');
        $qb->andWhere('cc.calendar = :calendar');

        //Remove all the contacts which are already in the project as associate or otherwise affected
        $findContactByProjectIdQueryBuilder = $this->_em->getRepository(
            'Contact\Entity\Contact'
        )->findContactByProjectIdQueryBuilder();
        $qb->andWhere($qb->expr()->notIn('cc.contact', $findContactByProjectIdQueryBuilder->getDQL()));

        $qb->setParameter(1, $calendar->getProjectCalendar()->getProject()->getId());
        $qb->setParameter('calendar', $calendar);
        $qb->addOrderBy('contact.lastName', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
