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
use Calendar\Search\Service\CalendarSearchService;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Contact\Service\SelectionContactService;
use Doctrine\ORM\EntityManager;
use Project\Entity\Project;
use Search\Service\AbstractSearchService;
use Search\Service\SearchUpdateInterface;
use Solarium\Client;
use Solarium\Core\Query\AbstractQuery;

/**
 * Class CalendarService
 *
 * @package Calendar\Service
 */
class CalendarService extends AbstractService implements SearchUpdateInterface
{
    public const WHICH_UPCOMING = 'Upcoming';
    public const WHICH_UPDATED = 'Updated';
    public const WHICH_PAST = 'Past';
    public const WHICH_FINAL = 'Final';
    public const WHICH_REVIEWS = 'Reviews';
    public const WHICH_ON_HOMEPAGE = 'Homepage';
    public const WHICH_HIGHLIGHT = 'Highlight';

    /**
     * @var CalendarSearchService
     */
    private $calendarSearchService;
    /**
     * @var ContactService
     */
    private $contactService;

    public function __construct(
        EntityManager $entityManager,
        SelectionContactService $selectionContactService,
        CalendarSearchService $calendarSearchService,
        ContactService $contactService
    ) {
        parent::__construct($entityManager, $selectionContactService);

        $this->calendarSearchService = $calendarSearchService;
        $this->contactService = $contactService;
    }

    public function findCalendarById(int $id): ?Calendar
    {
        return $this->entityManager->getRepository(Calendar::class)->find($id);
    }

    public function findCalendarByDocRef(string $docRef): ?Calendar
    {
        return $this->entityManager->getRepository(Entity\Calendar::class)->findOneBy(
            [
                'docRef' => $docRef,
            ]
        );
    }

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
                     * Give every new user the status "tentative"
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
            foreach (\explode(',', $data['removed']) as $contactId) {
                foreach ($calendar->getCalendarContact() as $calendarContact) {
                    if ($calendarContact->getContact()->getId() === (int)$contactId) {
                        $this->delete($calendarContact);
                    }
                }
            }
        }
    }

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

    public function save(Entity\AbstractEntity $abstractEntity): Entity\AbstractEntity
    {
        parent::save($abstractEntity);

        if ($abstractEntity instanceof Calendar) {
            $this->updateEntityInSearchEngine($abstractEntity);
        }

        return $abstractEntity;
    }

    /**
     * @param Calendar $calendar
     */
    public function updateEntityInSearchEngine($calendar): void
    {
        $document = $this->prepareSearchUpdate($calendar);

        $this->calendarSearchService->executeUpdateDocument($document);
    }

    /**
     * @param Calendar $calendar
     *
     * @return AbstractQuery
     */
    public function prepareSearchUpdate($calendar): AbstractQuery
    {
        $searchClient = new Client();
        $update = $searchClient->createUpdate();

        // Add the calendar data as the document
        $calendarDocument = $update->createDocument();
        // Calendar properties
        $calendarDocument->id = $calendar->getResourceId();
        $calendarDocument->calendar_id = $calendar->getId();

        $calendarDocument->docref = $calendar->getDocRef();

        $calendarDocument->calendar = $calendar->getCalendar();
        $calendarDocument->calendar_sort = $calendar->getCalendar();
        $calendarDocument->calendar_search = $calendar->getCalendar();

        $calendarDocument->description = $calendar->getDescription();
        $calendarDocument->description_sort = $calendar->getDescription();
        $calendarDocument->description_search = $calendar->getDescription();

        if ($calendar->isHighlight()) {
            $calendarDocument->highlight_description = $calendar->getHighlightDescription();
            $calendarDocument->highlight_description_sort = $calendar->getHighlightDescription();
            $calendarDocument->highlight_description_search = $calendar->getHighlightDescription();
        }

        $calendarDocument->location = $calendar->getLocation();
        $calendarDocument->location_sort = $calendar->getLocation();
        $calendarDocument->location_search = $calendar->getLocation();

        if (null !== $calendar->getDateFrom()) {
            $calendarDocument->date_from = $calendar->getDateFrom()->format(AbstractSearchService::DATE_SOLR);
        }
        if (null !== $calendar->getDateEnd()) {
            $calendarDocument->date_end = $calendar->getDateEnd()->format(AbstractSearchService::DATE_SOLR);
        }
        if (null !== $calendar->getDateCreated()) {
            $calendarDocument->date_created = $calendar->getDateCreated()->format(AbstractSearchService::DATE_SOLR);
        }
        if (null !== $calendar->getDateUpdated()) {
            $calendarDocument->date_updated = $calendar->getDateUpdated()->format(AbstractSearchService::DATE_SOLR);
        }

        $calendarDocument->year = $calendar->getDateFrom()->format('Y');
        $calendarDocument->month = $calendar->getDateFrom()->format('m');

        $calendarDocument->highlight = $calendar->isHighlight();
        $calendarDocument->highlight_text = $calendar->isHighlight() ? 'Yes' : 'No';
        $calendarDocument->own_event = $calendar->isOwnEvent();
        $calendarDocument->own_event_text = $calendar->isOwnEvent() ? 'Yes' : 'No';
        $calendarDocument->is_present = $calendar->isPresent();
        $calendarDocument->is_present_text = $calendar->isPresent() ? 'Yes' : 'No';
        $calendarDocument->on_homepage = $calendar->onHomepage();
        $calendarDocument->on_homepage_text = $calendar->onHomepage() ? 'Yes' : 'No';


        $update->addDocument($calendarDocument);
        $update->addCommit();

        return $update;
    }

    public function delete(Entity\AbstractEntity $abstractEntity): void
    {
        if ($abstractEntity instanceof Calendar) {
            $this->calendarSearchService->deleteDocument($abstractEntity);
        }

        parent::delete($abstractEntity);
    }

    public function findCalendarContactByContact(
        string $which,
        Contact $contact
    ): array {
        /** @var Repository\Contact $repository */
        $repository = $this->entityManager->getRepository(CalendarContact::class);

        return $repository->findCalendarContactByContact($which, $contact);
    }

    public function findCalendarContactByContactAndCalendar(
        Contact $contact,
        Calendar $calendar
    ): ?CalendarContact {
        /** @var Repository\Contact $repository */
        $repository = $this->entityManager->getRepository(CalendarContact::class);

        return $repository->findCalendarContactByContactAndCalendar($contact, $calendar);
    }

    public function findCalendarContactsByCalendar(
        Calendar $calendar,
        $status = CalendarContact::STATUS_ALL,
        string $order = 'lastname'
    ): array {
        /** @var Repository\Contact $repository */
        $repository = $this->entityManager->getRepository(CalendarContact::class);

        return $repository->findCalendarContactsByCalendar($calendar, $status, $order);
    }

    public function canViewCalendar(Calendar $calendar, Contact $contact = null): bool
    {
        /** @var Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        return $repository->canViewCalendar($calendar, $contact);
    }

    public function findCalendarItems(
        string $which = self::WHICH_UPCOMING,
        Contact $contact = null,
        int $year = null,
        string $type = null
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

    public function findLatestProjectCalendar(Project $project): ?Calendar
    {
        /** @var \Calendar\Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        return $repository->findLatestProjectCalendar($project);
    }

    public function findNextProjectCalendar(
        Project $project,
        \DateTime $datetime
    ): ?Calendar {
        /** @var \Calendar\Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        return $repository->findNextProjectCalendar($project, $datetime);
    }

    public function findPreviousProjectCalendar(
        Project $project,
        \DateTime $datetime
    ): ?Calendar {
        /** @var \Calendar\Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        return $repository->findPreviousProjectCalendar($project, $datetime);
    }

    public function updateContactStatus(
        CalendarContact $calendarContact,
        string $status
    ): void {
        /** @var Entity\ContactStatus $contactStatus */
        $contactStatus = $this->entityManager->getReference(Entity\ContactStatus::class, $status);

        $calendarContact->setStatus($contactStatus);
        $this->save($calendarContact);
    }

    public function findGeneralCalendarContactByCalendar(Calendar $calendar): array
    {
        /** @var Repository\Contact $repository */
        $repository = $this->entityManager->getRepository(CalendarContact::class);

        return $repository->findGeneralCalendarContactByCalendar($calendar);
    }

    public function updateCollectionInSearchEngine(bool $clearIndex = false): void
    {
        $calendarItems = $this->findAll(Entity\Calendar::class);
        $collection = [];
        foreach ($calendarItems as $calendar) {
            $collection[] = $this->prepareSearchUpdate($calendar);
        }

        $this->calendarSearchService->updateIndexWithCollection($collection, $clearIndex);
    }

    public function getWhichValues(): array
    {
        return [
            self::WHICH_UPCOMING,
            self::WHICH_UPDATED,
            self::WHICH_FINAL,
            self::WHICH_PAST,
            self::WHICH_REVIEWS,
            self::WHICH_ON_HOMEPAGE
        ];
    }
}
