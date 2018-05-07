<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category  Calendar
 * @package   Service
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
declare(strict_types=1);

namespace Calendar\Service;

use Calendar\Entity;
use Calendar\Entity\Calendar;
use Calendar\Entity\Contact as CalendarContact;
use Calendar\Repository;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Contact\Service\SelectionContactService;
use Doctrine\ORM\EntityManager;
use Project\Entity\Project;

/**
 * Class CalendarService
 *
 * @package Calendar\Service
 */
class CalendarService extends AbstractService
{
    /**
     * Constant to determine which affiliations must be taken from the database
     */
    public const WHICH_UPCOMING = 'upcoming';
    public const WHICH_UPDATED = 'updated';
    public const WHICH_PAST = 'past';
    public const WHICH_FINAL = 'final';
    public const WHICH_REVIEWS = 'project-reviews';
    public const WHICH_ON_HOMEPAGE = 'on-homepage';
    /**
     * @var ContactService
     */
    protected $contactService;

    /**
     * CalendarService constructor.
     *
     * @param EntityManager           $entityManager
     * @param SelectionContactService $selectionContactService
     * @param ContactService          $contactService
     */
    public function __construct(
        EntityManager $entityManager,
        SelectionContactService $selectionContactService,
        ContactService $contactService
    ) {
        parent::__construct($entityManager, $selectionContactService);

        $this->contactService = $contactService;
    }

    /**
     * @param int $id
     *
     * @return Calendar|null
     */
    public function findCalendarById(int $id): ?Calendar
    {
        return $this->entityManager->getRepository(Calendar::class)->find($id);
    }

    /**
     * @param $docRef
     *
     * @return null|Entity\Calendar
     */
    public function findCalendarByDocRef(string $docRef): ?Calendar
    {
        return $this->entityManager->getRepository(Entity\Calendar::class)->findOneBy(
            [
                'docRef' => $docRef,
            ]
        );
    }

    /**
     * @param array    $data
     * @param Calendar $calendar
     *
     * array (size=5)
     * 'type' => string '2' (length=1)
     * 'added' => string '20388' (length=5)
     * 'removed' => string '' (length=0)
     * 'sql' => string '' (length=0)
     *
     *
     */
    public function updateCalendarContacts(Calendar $calendar, array $data): void
    {
        //Update the contacts
        if (!empty($data['added'])) {
            foreach (explode(',', $data['added']) as $contactId) {
                $contact = $this->contactService->findContactById((int)$contactId);

                if (null !== $contact && !$this->calendarHasContact($calendar, $contact)) {
                    $calendarContact = new CalendarContact();
                    $calendarContact->setContact($contact);
                    $calendarContact->setCalendar($calendar);

                    /**
                     * Add every new user as attendee
                     *
                     * @var $role Entity\ContactRole
                     */
                    $role = $this->find(Entity\ContactRole::class, Entity\ContactRole::ROLE_ATTENDEE);
                    $calendarContact->setRole($role);

                    /**
                     * Add every new user as attendee
                     *
                     * @var $status Entity\ContactStatus
                     */
                    $status = $this->find(Entity\ContactStatus::class, Entity\ContactStatus::STATUS_TENTATIVE);
                    $calendarContact->setStatus($status);

                    $this->save($calendarContact);
                }
            }
        }

        //Update the contacts
        if (!empty($data['removed'])) {
            foreach (explode(',', $data['removed']) as $contactId) {
                foreach ($calendar->getCalendarContact() as $calendarContact) {
                    if ($calendarContact->getContact()->getId() === (int)$contactId) {
                        $this->delete($calendarContact);
                    }
                }
            }
        }
    }

    /**
     * @param Calendar $calendar
     * @param Contact  $contact
     *
     * @return bool
     */
    public function calendarHasContact(Calendar $calendar, Contact $contact): bool
    {
        $calendarContact = $this->entityManager->getRepository(CalendarContact::class)->findOneBy(
            [
                'calendar' => $calendar,
                'contact'  => $contact,
            ]
        );

        return null !== $calendarContact;
    }

    /**
     * @param  string  $which
     * @param  Contact $contact
     *
     * @return CalendarContact[]
     */
    public function findCalendarContactByContact(
        $which = self::WHICH_UPCOMING,
        Contact $contact = null
    ): array {
        /** @var Repository\Contact $repository */
        $repository = $this->entityManager->getRepository(CalendarContact::class);

        return $repository->findCalendarContactByContact($which, $contact);
    }

    /**
     * @param Contact  $contact
     * @param Calendar $calendar
     *
     * @return CalendarContact
     */
    public function findCalendarContactByContactAndCalendar(
        Contact $contact,
        Calendar $calendar
    ): ?CalendarContact {
        /** @var Repository\Contact $repository */
        $repository = $this->entityManager->getRepository(CalendarContact::class);

        return $repository->findCalendarContactByContactAndCalendar($contact, $calendar);
    }

    /**
     * @param Calendar $calendar
     * @param int      $status
     * @param string   $order
     *
     * @return array
     */
    public function findCalendarContactsByCalendar(
        Calendar $calendar,
        $status = CalendarContact::STATUS_ALL,
        string $order = 'lastname'
    ): array {
        /** @var Repository\Contact $repository */
        $repository = $this->entityManager->getRepository(CalendarContact::class);

        return $repository->findCalendarContactsByCalendar($calendar, $status, $order);
    }

    /**
     * This function will return a boolean value to see if a contact can view the calendar
     *
     * @param Calendar     $calendar
     * @param Contact|null $contact
     *
     * @return bool
     */
    public function canViewCalendar(Calendar $calendar, Contact $contact = null): bool
    {
        /** @var Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        return $repository->canViewCalendar($calendar, $contact);
    }

    /**
     * @param string       $which
     * @param Contact|null $contact
     * @param null         $year
     * @param null         $type
     *
     * @return \Doctrine\ORM\Query
     */
    public function findCalendarItems(
        $which = self::WHICH_UPCOMING,
        Contact $contact = null,
        $year = null,
        $type = null
    ): \Doctrine\ORM\Query {
        /** @var \Calendar\Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        $limitQueryBuilder = null;
        if (null !== $contact) {
            /*
             * Grab the limiting query-builder from the AdminService
             */
            $limitQueryBuilder = $this->parseWherePermit(
                new Calendar(),
                'view',
                $contact
            );
        }


        return $repository->findCalendarItems($which, true, $contact, $year, $type, $limitQueryBuilder);
    }

    /**
     * @param bool    $onlyFinal
     * @param Project $project
     *
     * @return Calendar[]
     */
    public function findCalendarByProject(Project $project, $onlyFinal = true): array
    {
        $calendar = [];
        /**
         * Add the calendar items from the project
         */
        foreach ($project->getProjectCalendar() as $calendarItem) {
            if (!$onlyFinal
                || $calendarItem->getCalendar()->getFinal() === Calendar::FINAL_FINAL
            ) {
                $calendar[$calendarItem->getCalendar()->getId()]
                    = $calendarItem->getCalendar();
            }
        }
        foreach ($project->getCall()->getCalendar() as $calendarItem) {
            if (!$onlyFinal
                || $calendarItem->getFinal() === Calendar::FINAL_FINAL
            ) {
                if ($calendarItem->getDateEnd() > new \DateTime()) {
                    $calendar[$calendarItem->getId()] = $calendarItem;
                }
            }
        }

        return $calendar;
    }

    /**
     * return the review-meeting corresponding to a calendar item
     *
     * @param Project $project
     *
     * @return Calendar|null
     */
    public function findLatestProjectCalendar(Project $project): ?Calendar
    {
        /** @var \Calendar\Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        return $repository->findLatestProjectCalendar($project);
    }

    /**
     * Return the news review meeting
     *
     * @param Project   $project
     * @param \DateTime $datetime
     *
     * @return Calendar|null
     */
    public function findNextProjectCalendar(
        Project $project,
        \DateTime $datetime
    ): ?Calendar {
        /** @var \Calendar\Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        return $repository->findNextProjectCalendar($project, $datetime);
    }

    /**
     * @param Project   $project
     * @param \DateTime $datetime
     *
     * @return Calendar|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findPreviousProjectCalendar(
        Project $project,
        \DateTime $datetime
    ): ?Calendar {
        /** @var \Calendar\Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        return $repository->findPreviousProjectCalendar($project, $datetime);
    }

    /**
     * @param CalendarContact $calendarContact
     * @param string          $status
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateContactStatus(
        CalendarContact $calendarContact,
        string $status
    ): void {
        /** @var Entity\ContactStatus $contactStatus */
        $contactStatus = $this->entityManager->getReference(Entity\ContactStatus::class, $status);

        $calendarContact->setStatus($contactStatus);
        $this->save($calendarContact);
    }

    /**
     * @param Calendar $calendar
     *
     * @return CalendarContact[]
     */
    public function findGeneralCalendarContactByCalendar(Calendar $calendar): array
    {
        /** @var Repository\Contact $repository */
        $repository = $this->entityManager->getRepository(CalendarContact::class);

        return $repository->findGeneralCalendarContactByCalendar($calendar);
    }

    /**
     * @return \stdClass
     */
    public function findMinAndMaxYear(): \stdClass
    {
        /** @var Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        $yearSpanResult = $repository->findMinAndMaxYear();
        $yearSpan = new \stdClass();
        $yearSpan->minYear = (int)$yearSpanResult['minYear'];
        $yearSpan->maxYear = (int)$yearSpanResult['maxYear'];

        return $yearSpan;
    }

    /**
     * Return an array of all which-values
     *
     * @return array
     */
    public function getWhichValues(): array
    {
        return [
            self::WHICH_UPCOMING,
            self::WHICH_UPDATED,
            self::WHICH_FINAL,
            self::WHICH_PAST,
            self::WHICH_REVIEWS,
        ];
    }
}
