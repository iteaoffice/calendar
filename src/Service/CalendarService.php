<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\Service;

use Admin\Service\AdminService;
use Calendar\Entity;
use Calendar\Entity\Calendar;
use Calendar\Entity\Contact as CalendarContact;
use Calendar\Repository;
use Calendar\Search\Service\CalendarSearchService;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Contact\Service\SelectionContactService;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Program\Service\CallService;
use Project\Entity\Project;
use Search\Service\AbstractSearchService;
use Search\Service\SearchUpdateInterface;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Http;
use Solarium\Core\Query\AbstractQuery;
use Solarium\QueryType\Update\Query\Document;
use Laminas\I18n\Translator\TranslatorInterface;

use Symfony\Component\EventDispatcher\EventDispatcher;

use function count;
use function date;
use function range;
use function sprintf;

/**
 * Class CalendarService
 *
 * @package Calendar\Service
 */
class CalendarService extends AbstractService implements SearchUpdateInterface
{
    private CalendarSearchService $calendarSearchService;
    private ContactService $contactService;
    private CallService $callService;
    private AdminService $adminService;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManager $entityManager,
        SelectionContactService $selectionContactService,
        CalendarSearchService $calendarSearchService,
        ContactService $contactService,
        CallService $callService,
        AdminService $adminService,
        TranslatorInterface $translator
    ) {
        parent::__construct($entityManager, $selectionContactService);

        $this->calendarSearchService = $calendarSearchService;
        $this->contactService = $contactService;
        $this->callService = $callService;
        $this->adminService = $adminService;
        $this->translator = $translator;
    }

    public function findCalendarByDocRef(string $docRef): ?Calendar
    {
        return $this->entityManager->getRepository(Entity\Calendar::class)->findOneBy(
            [
                'docRef' => $docRef,
            ]
        );
    }

    public function canDeleteCalendar(Entity\Calendar $calendar): bool
    {
        if (null === $calendar->getProjectCalendar()) {
            return true;
        }

        $cannotDeleteCalendar = [];

        if (! $calendar->getProjectCalendar()->getAction()->isEmpty()) {
            $cannotDeleteCalendar[] = 'Calendar has actions';
        }

        if (! $calendar->getProjectCalendar()->getPlannedAction()->isEmpty()) {
            $cannotDeleteCalendar[] = 'Calendar has planned actions';
        }


        return count($cannotDeleteCalendar) === 0;
    }

    public function updateCalendarContacts(Calendar $calendar, array $data): void
    {
        $contacts = $data['contacts'] ?? [];

        //Update the contacts
        foreach ($contacts as $contactId) {
            $contact = $this->contactService->findContactById((int)$contactId);

            if (null !== $contact && ! $this->calendarHasContact($calendar, $contact)) {
                $calendarContact = new CalendarContact();
                $calendarContact->setContact($contact);
                $calendarContact->setCalendar($calendar);

                /**
                 * Add every new user as attendee
                 *
                 * @var Entity\ContactRole $role
                 */
                $role = $this->find(Entity\ContactRole::class, Entity\ContactRole::ROLE_ATTENDEE);
                $calendarContact->setRole($role);

                /**
                 * Give every new user the status "tentative"
                 *
                 * @var Entity\ContactStatus $status
                 */
                $status = $this->find(Entity\ContactStatus::class, Entity\ContactStatus::STATUS_TENTATIVE);
                $calendarContact->setStatus($status);

                $this->save($calendarContact);
            }
        }

        foreach ($calendar->getCalendarContact() as $calendarContact) {
            if (! in_array($calendarContact->getContact()->getId(), $contacts, false)) {
                $this->delete($calendarContact);
            }
        }
    }

    public function calendarHasContact(Calendar $calendar, Contact $contact): bool
    {
        $calendarContact = $this->entityManager->getRepository(CalendarContact::class)->findOneBy(
            [
                'calendar' => $calendar,
                'contact' => $contact,
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
        $searchClient = new Client(new Http(), new EventDispatcher(), []);
        $update = $searchClient->createUpdate();

        /** @var Document $calendarDocument */
        $calendarDocument = $update->createDocument();

        // Calendar properties
        $calendarDocument->setField('id', $calendar->getResourceId());
        $calendarDocument->setField('calendar_id', $calendar->getId());

        $calendarDocument->setField('docref', $calendar->getDocRef());

        $calendarDocument->setField('calendar', $calendar->getCalendar());
        $calendarDocument->setField('calendar_sort', $calendar->getCalendar());
        $calendarDocument->setField('calendar_search', $calendar->getCalendar());

        $calendarDocument->setField('description', $calendar->getDescription());
        $calendarDocument->setField('description_sort', $calendar->getDescription());
        $calendarDocument->setField('description_search', $calendar->getDescription());

        $calendarDocument->setField('highlight_description', $calendar->getHighlightDescription());
        $calendarDocument->setField('highlight_description_sort', $calendar->getHighlightDescription());
        $calendarDocument->setField('highlight_description_search', $calendar->getHighlightDescription());

        $calendarDocument->setField('location', $calendar->getLocation());
        $calendarDocument->setField('location_sort', $calendar->getLocation());
        $calendarDocument->setField('location_search', $calendar->getLocation());

        $calendarDocument->setField('type', $calendar->getType()->getType());
        $calendarDocument->setField('type_sort', $calendar->getType()->getType());
        $calendarDocument->setField('type_search', $calendar->getType()->getType());

        if (null !== $calendar->getDateFrom()) {
            $calendarDocument->setField(
                'date_from',
                $calendar->getDateFrom()->format(AbstractSearchService::DATE_SOLR)
            );
        }
        if (null !== $calendar->getDateEnd()) {
            $calendarDocument->setField('date_end', $calendar->getDateEnd()->format(AbstractSearchService::DATE_SOLR));
        }
        if (null !== $calendar->getDateCreated()) {
            $calendarDocument->setField(
                'date_created',
                $calendar->getDateCreated()->format(AbstractSearchService::DATE_SOLR)
            );
        }
        if (null !== $calendar->getDateUpdated()) {
            $calendarDocument->setField(
                'date_updated',
                $calendar->getDateUpdated()->format(AbstractSearchService::DATE_SOLR)
            );
        }

        if ($calendar->isReview()) {
            $calendarDocument->setField('project_id', $calendar->getProjectCalendar()->getProject()->getId());
            $calendarDocument->setField('project_name', $calendar->getProjectCalendar()->getProject()->parseFullName());
        }

        $calendarDocument->setField('year', $calendar->getDateFrom()->format('Y'));
        $calendarDocument->setField('month', $calendar->getDateFrom()->format('m'));

        $calendarDocument->setField('highlight', $calendar->isHighlight());
        $calendarDocument->setField('highlight_text', $calendar->isHighlight() ? 'Yes' : 'No');
        $calendarDocument->setField('final', $calendar->isFinal());
        $calendarDocument->setField('final_text', $this->translator->translate($calendar->getFinal(true)));
        $calendarDocument->setField('own_event', $calendar->isOwnEvent());
        $calendarDocument->setField('own_event_text', $calendar->isOwnEvent() ? 'Yes' : 'No');
        $calendarDocument->setField('is_present', $calendar->isPresent());
        $calendarDocument->setField('is_present_text', $calendar->isPresent() ? 'Yes' : 'No');
        $calendarDocument->setField('on_homepage', $calendar->onHomepage());
        $calendarDocument->setField('on_homepage_text', $calendar->onHomepage() ? 'Yes' : 'No');

        $calendarDocument->setField('is_project', $calendar->isProject());
        $calendarDocument->setField('is_project_text', $calendar->isProject() ? 'Yes' : 'No');
        $calendarDocument->setField('is_review', $calendar->isReview());
        $calendarDocument->setField('is_review_text', $calendar->isReview() ? 'Yes' : 'No');
        $calendarDocument->setField('is_birthday', $calendar->isBirthday());
        $calendarDocument->setField('is_birthday_text', $calendar->isBirthday() ? 'Yes' : 'No');

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

    public function findCalendarContactByContact(Contact $contact): array
    {
        /** @var Repository\Contact $repository */
        $repository = $this->entityManager->getRepository(CalendarContact::class);

        return $repository->findCalendarContactByContact($contact);
    }

    public function findUpcomingCalendarContactByContact(Contact $contact): array
    {
        /** @var Repository\Contact $repository */
        $repository = $this->entityManager->getRepository(CalendarContact::class);

        return $repository->findUpcomingCalendarContactByContact($contact);
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
        int $status = CalendarContact::STATUS_ALL,
        string $order = 'lastname'
    ): array {
        /** @var Repository\Contact $repository */
        $repository = $this->entityManager->getRepository(CalendarContact::class);

        return $repository->findCalendarContactsByCalendar($calendar, $status, $order);
    }

    public function isPublic(Calendar $calendar): bool
    {
        /** @var Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        return $repository->isPublic($calendar);
    }

    public function findCalendarItems(
        Contact $contact,
        bool $upcoming = true,
        bool $review = false
    ): QueryBuilder {
        /** @var Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        $calendarItems = $repository->findCalendarItems($upcoming, $review);

        $limitQueryBuilder = $this->parseWherePermit(
            new Calendar(),
            'view',
            $contact
        );

        //Find the roles of the contact
        $roles = $this->adminService->findAccessRolesByContactAsArray($contact);

        return $repository->filterForAccess($calendarItems, $roles, $limitQueryBuilder);
    }

    public function findVisibleItems(Contact $contact): array
    {
        /** @var Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        $limitQueryBuilder = $this->parseWherePermit(
            new Calendar(),
            'view',
            $contact
        );

        $roles = $this->adminService->findAccessRolesByContactAsArray($contact);
        return $repository->findVisibleItems($roles, $limitQueryBuilder);
    }

    public function findCalendarByProject(Project $project, $onlyFinal = true): array
    {
        $calendar = [];
        /**
         * Add the calendar items from the project
         */
        foreach ($project->getProjectCalendar() as $calendarItem) {
            if (
                ! $onlyFinal
                || $calendarItem->getCalendar()->isFinal()
            ) {
                $calendar[$calendarItem->getCalendar()->getId()]
                    = $calendarItem->getCalendar();
            }
        }
        foreach ($project->getCall()->getCalendar() as $calendarItem) {
            if (
                ! $onlyFinal
                || $calendarItem->isFinal()
            ) {
                if ($calendarItem->getDateEnd() > new DateTime()) {
                    $calendar[$calendarItem->getId()] = $calendarItem;
                }
            }
        }

        return $calendar;
    }

    public function findLatestProjectCalendar(Project $project): ?Calendar
    {
        /** @var Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        return $repository->findLatestProjectCalendar($project);
    }

    public function findNextProjectCalendar(
        Project $project,
        DateTime $datetime
    ): ?Calendar {
        /** @var Repository\Calendar $repository */
        $repository = $this->entityManager->getRepository(Entity\Calendar::class);

        return $repository->findNextProjectCalendar($project, $datetime);
    }

    public function findPreviousProjectCalendar(
        Project $project,
        DateTime $datetime
    ): ?Calendar {
        /** @var Repository\Calendar $repository */
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

    public function findUpcomingCalendar(): Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->gt('dateFrom', new DateTime()))
            ->orderBy(['dateFrom' => Criteria::ASC]);

        return $this->entityManager->getRepository(Entity\Calendar::class)->matching($criteria);
    }

    public function addContactToCalendars(Contact $contact, array $formData): array
    {
        //We do need to handle the $formData array, which has the following form
        /**
         *
         * /var/www/dev1/vendor/iteaoffice/calendar/src/Controller/ManagerController.php:496:
         * array (size=1)
         * 'calendar' =>
         * array (size=40)
         * 1 =>
         * array (size=2)
         * 'calendar' => string '3465' (length=4)
         * 'role' => string '1' (length=1)
         * 2 =>
         * array (size=2)
         * 'calendar' => string '3402' (length=4)
         * 'role' => string '4' (length=1)
         * 3 =>
         *
         */

        //Short-circuit the service when we have no element calendar at all.
        if (! isset($formData['calendar'])) {
            return [];
        }

        $calendarContacts = [];

        foreach ($formData['calendar'] as $calendarItem) {
            //If the checkbox is not set, we can skip it.
            if (! isset($calendarItem['calendar'])) {
                continue;
            }

            $calendar = $this->findCalendarById((int)$calendarItem['calendar']);
            $role = $this->find(Entity\ContactRole::class, (int)$calendarItem['role']);
            $status = $this->find(Entity\ContactStatus::class, Entity\ContactStatus::STATUS_TENTATIVE);

            if (null !== $calendar && null !== $role) {
                $calendarContact = new Entity\Contact();
                $calendarContact->setContact($contact);
                $calendarContact->setCalendar($calendar);
                $calendarContact->setStatus($status);
                $calendarContact->setRole($role);
                $this->save($calendarContact);

                $calendarContacts[] = $calendarContact;
            }
        }

        return $calendarContacts;
    }

    public function findCalendarById(int $id): ?Calendar
    {
        return $this->entityManager->getRepository(Calendar::class)->find($id);
    }

    public function updateCollectionInSearchEngine(bool $clearIndex = false): void
    {
        $calendarItems = $this->findAll(Entity\Calendar::class);
        $collection = [];
        foreach ($calendarItems as $calendar) {
            $collection[] = $this->prepareSearchUpdate($calendar);
        }

        //Add the contacts which have their birthday
        $birthDays = $this->contactService->findContactsWithDateOfBirth();
        foreach ($birthDays as $contactWithBirthday) {
            $collection[] = $this->prepareSearchUpdateForBirthday($contactWithBirthday);
        }

        $this->calendarSearchService->updateIndexWithCollection($collection, $clearIndex);
    }

    public function prepareSearchUpdateForBirthday(Contact $contact): AbstractQuery
    {
        $searchClient = new Client(new Http(), new EventDispatcher(), []);
        $update = $searchClient->createUpdate();

        $currentYear = (int)date('Y');
        $yearSpan = range($currentYear - 3, $currentYear + 3);

        foreach ($yearSpan as $year) {

            /** @var Document $calendarDocument */
            $calendarDocument = $update->createDocument();
            // Calendar properties
            $calendarDocument->setField('id', 'birthday_' . $contact->getId() . '_' . $year);
            $name = sprintf(
                'Birthday of %s (%s)',
                $contact->getDisplayName(),
                $year - $contact->getDateOfBirth()->format('Y')
            );

            $calendarDocument->setField('calendar', $name);
            $calendarDocument->setField('calendar_sort', $name);
            $calendarDocument->setField('calendar_search', $name);

            $calendarDocument->setField('description', $name);
            $calendarDocument->setField('description_sort', $name);
            $calendarDocument->setField('description_search', $name);


            $calendarDocument->setField('highlight_description', $name);
            $calendarDocument->setField('highlight_description_sort', $name);
            $calendarDocument->setField('highlight_description_search', $name);

            $calendarDocument->setField('type', $this->translator->translate('txt-birthday'));
            $calendarDocument->setField('type_search', $this->translator->translate('txt-birthday'));
            $calendarDocument->setField('type_sort', $this->translator->translate('txt-birthday'));


            $calendarDocument->setField('location', 'NLD, Eindhoven');
            $calendarDocument->setField('location_sort', 'NLD, Eindhoven');
            $calendarDocument->setField('location_search', 'NLD, Eindhoven');

            $dateFrom = DateTime::createFromFormat(
                'd-m-Y',
                sprintf($contact->getDateOfBirth()->format('d-m-' . $year))
            );

            $calendarDocument->setField(
                'date_from',
                $dateFrom->format(AbstractSearchService::DATE_SOLR)
            );
            $calendarDocument->setField(
                'date_end',
                $dateFrom->format(AbstractSearchService::DATE_SOLR)
            );

            $calendarDocument->setField('year', $dateFrom->format('Y'));
            $calendarDocument->setField('month', $dateFrom->format('m'));

            $calendarDocument->setField('highlight', false);
            $calendarDocument->setField('highlight_text', 'No');
            $calendarDocument->setField('is_project', false);
            $calendarDocument->setField('is_project_text', 'No');
            $calendarDocument->setField('final', true);
            $calendarDocument->setField('final_text', 'Final');
            $calendarDocument->setField('is_call', false);
            $calendarDocument->setField('is_call_text', 'No');
            $calendarDocument->setField('is_review', false);
            $calendarDocument->setField('is_review_text', 'No');
            $calendarDocument->setField('is_birthday', true);
            $calendarDocument->setField('is_birthday_text', 'Yes');
            $calendarDocument->setField('own_event', false);
            $calendarDocument->setField('own_event_text', 'Yes');
            $calendarDocument->setField('is_present', false);
            $calendarDocument->setField('is_present_text', 'No');
            $calendarDocument->setField('on_homepage', false);
            $calendarDocument->setField('on_homepage_text', 'No');

            $update->addDocument($calendarDocument);
        }
        $update->addCommit();

        return $update;
    }
}
