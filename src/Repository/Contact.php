<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Repository;

use Calendar\Entity;
use Calendar\Service\CalendarService;
use Contact\Entity\Contact as ContactEntity;
use Doctrine\ORM\EntityRepository;

/**
 * @category    Calendar
 */
class Contact extends EntityRepository
{
    /**
     * @param string $which
     * @param ContactEntity $contact
     *
     * @return Entity\Contact[]
     */
    public function findCalendarContactByContact($which, ContactEntity $contact = null): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_contact');
        $qb->from(Entity\Contact::class, 'calendar_entity_contact');
        $qb->join('calendar_entity_contact.calendar', "calendar_entity_calendar");
        $qb->join('calendar_entity_contact.contact', "contact");

        switch ($which) {
            case CalendarService::WHICH_UPCOMING:
                $qb->andWhere('calendar_entity_calendar.dateFrom >= ?1');
                $qb->orderBy('calendar_entity_calendar.dateFrom', 'ASC');
                $qb->setParameter(1, new \DateTime());
                break;
            case CalendarService::WHICH_PAST:
                $qb->andWhere('calendar_entity_calendar.dateFrom <= ?1');
                $qb->orderBy('calendar_entity_calendar.dateEnd', 'DESC');
                $qb->setParameter(1, new \DateTime());
                break;
            case CalendarService::WHICH_REVIEWS:
                $qb->andWhere('calendar_entity_calendar.dateEnd >= ?1');
                $qb->orderBy('calendar_entity_calendar.dateFrom', 'ASC');
                $qb->setParameter(1, new \DateTime());
                $projectCalendarSubSelect = $this->_em->createQueryBuilder();
                $projectCalendarSubSelect->select('calendar.id');
                $projectCalendarSubSelect->from('Project\Entity\Calendar\Calendar', 'projectCalendar');
                $projectCalendarSubSelect->join('projectCalendar.calendar', 'calendar');
                $qb->andWhere($qb->expr()->in('calendar_entity_calendar.id', $projectCalendarSubSelect->getDQL()));
                break;
            case CalendarService::WHICH_UPDATED:
                $qb->orderBy('calendar_entity_calendar.dateUpdated', 'DESC');
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

        $qb->andWhere('calendar_entity_contact.contact = ?10');
        $qb->addOrderBy('contact.lastName', 'ASC');
        $qb->setParameter(10, $contact);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param ContactEntity $contact
     * @param Entity\Calendar $calendar
     *
     * @return Entity\Contact
     */
    public function findCalendarContactByContactAndCalendar(
        ContactEntity $contact,
        Entity\Calendar $calendar
    ):?Entity\Contact {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_contact');
        $qb->from(Entity\Contact::class, 'calendar_entity_contact');

        $qb->andWhere('calendar_entity_contact.contact = ?10');
        $qb->setParameter(10, $contact);

        $qb->andWhere('calendar_entity_contact.calendar = ?11');
        $qb->setParameter(11, $calendar);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Entity\Calendar $calendar
     * @param int $status
     *
     * @return Entity\Contact[]|array
     */
    public function findCalendarContactsByCalendar(Entity\Calendar $calendar, $status): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_contact');
        $qb->from(Entity\Contact::class, 'calendar_entity_contact');
        $qb->join("calendar_entity_contact.contact", 'contact');

        $qb->andWhere('calendar_entity_contact.calendar = ?11');
        $qb->setParameter(11, $calendar);
        $qb->addOrderBy('contact.lastName', 'ASC');

        if ($status === Entity\Contact::STATUS_NO_DECLINED) {
            $qb->join("calendar_entity_contact.status", 'status');
            $qb->andWhere('status.id <> :status');
            $qb->setParameter('status', Entity\ContactStatus::STATUS_DECLINE);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Entity\Calendar $calendar
     *
     * @return Entity\Contact[]
     */
    public function findGeneralCalendarContactByCalendar(Entity\Calendar $calendar): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_contact');
        $qb->from(Entity\Contact::class, 'calendar_entity_contact');
        $qb->join('calendar_entity_contact.contact', 'contact');
        $qb->andWhere('calendar_entity_contact.calendar = :calendar');

        //Remove all the contacts which are already in the project as associate or otherwise affected
        /** @var \Contact\Repository\Contact $contactRepository */
        $contactRepository = $this->_em->getRepository(ContactEntity::class);

        $qb->andWhere($qb->expr()->notIn(
            'calendar_entity_contact.contact',
            $contactRepository->findContactByProjectIdQueryBuilder()->getDQL()
        ));

        $qb->setParameter(1, $calendar->getProjectCalendar()->getProject()->getId());
        $qb->setParameter('calendar', $calendar);
        $qb->addOrderBy('contact.lastName', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
