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
 * Class Contact
 *
 * @package Calendar\Repository
 */
final class Contact extends EntityRepository
{
    public function findCalendarContactByContact(ContactEntity $contact): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_contact');
        $qb->from(Entity\Contact::class, 'calendar_entity_contact');
        $qb->join('calendar_entity_contact.calendar', 'calendar_entity_calendar');
        $qb->join('calendar_entity_contact.contact', 'contact_entity_contact');

        $qb->andWhere('calendar_entity_contact.contact = ?10');
        $qb->andWhere('calendar_entity_calendar.dateEnd >= :today');
        $qb->setParameter('today', new \DateTime());
        $qb->addOrderBy('contact_entity_contact.lastName', 'ASC');


        $qb->setParameter(10, $contact);

        return $qb->getQuery()->getResult();
    }

    public function findCalendarContactByContactAndCalendar(
        ContactEntity $contact,
        Entity\Calendar $calendar
    ): ?Entity\Contact {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_contact');
        $qb->from(Entity\Contact::class, 'calendar_entity_contact');

        $qb->andWhere('calendar_entity_contact.contact = ?10');
        $qb->setParameter(10, $contact);

        $qb->andWhere('calendar_entity_contact.calendar = ?11');
        $qb->setParameter(11, $calendar);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findCalendarContactsByCalendar(Entity\Calendar $calendar, int $status, string $order): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('calendar_entity_contact');
        $qb->from(Entity\Contact::class, 'calendar_entity_contact');
        $qb->join('calendar_entity_contact.contact', 'contact_entity_contact');

        $qb->andWhere('calendar_entity_contact.calendar = ?11');
        $qb->setParameter(11, $calendar);

        switch ($order) {
            case 'lastname':
                $qb->addOrderBy('contact_entity_contact.lastName', 'ASC');
                break;

            case 'organisation':
                $qb->leftJoin('contact_entity_contact.contactOrganisation', 'contact_entity_contact_organisation');
                $qb->leftJoin('contact_entity_contact_organisation.organisation', 'organisation_entity_organisation');

                $qb->addOrderBy('organisation_entity_organisation.organisation', 'ASC');
                $qb->addOrderBy('contact_entity_contact.lastName', 'ASC');
                break;
        }

        if ($status === Entity\Contact::STATUS_NO_DECLINED) {
            $qb->join('calendar_entity_contact.status', 'status');
            $qb->andWhere('status.id <> :status');
            $qb->setParameter('status', Entity\ContactStatus::STATUS_DECLINE);
        }

        return $qb->getQuery()->getResult();
    }

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

        $qb->andWhere(
            $qb->expr()->notIn(
                'calendar_entity_contact.contact',
                $contactRepository->findContactByProjectIdQueryBuilder()->getDQL()
            )
        );

        $qb->setParameter(1, $calendar->getProjectCalendar()->getProject()->getId());
        $qb->setParameter('calendar', $calendar);
        $qb->addOrderBy('contact.lastName', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
