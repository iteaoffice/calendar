<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Controller;

use Application\Service\AssertionService;
use Calendar\Entity\Calendar;
use Calendar\Entity\ContactRole;
use Calendar\Entity\Document;
use Calendar\Entity\DocumentObject;
use Calendar\Form\CalendarContacts;
use Calendar\Form\CreateCalendarDocument;
use Calendar\Search\Service\CalendarSearchService;
use Calendar\Service\CalendarService;
use Calendar\Service\FormService;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use General\Service\GeneralService;
use Project\Service\ActionService;
use Project\Service\ProjectService;
use Search\Form\SearchResult;
use Search\Paginator\Adapter\SolariumPaginator;
use Solarium\QueryType\Select\Query\Query as SolariumQuery;
use Zend\Http\Request;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\Mvc\Plugin\Identity\Identity;
use Zend\Paginator\Paginator;
use Zend\Validator\File\FilesSize;
use Zend\Validator\File\MimeType;
use Zend\View\Model\ViewModel;
use function array_merge;
use function implode;
use function sprintf;

/**
 * @method FlashMessenger flashMessenger()
 * @method Identity|Contact identity()
 */
final class ManagerController extends AbstractActionController
{
    private CalendarService $calendarService;
    private CalendarSearchService $searchService;
    private FormService $formService;
    private ProjectService $projectService;
    private ActionService $actionService;
    private ContactService $contactService;
    private GeneralService $generalService;
    private AssertionService $assertionService;
    private EntityManager $entityManager;
    private TranslatorInterface $translator;

    public function __construct(
        CalendarService $calendarService,
        CalendarSearchService $searchService,
        FormService $formService,
        ProjectService $projectService,
        ActionService $actionService,
        ContactService $contactService,
        GeneralService $generalService,
        AssertionService $assertionService,
        EntityManager $entityManager,
        TranslatorInterface $translator
    ) {
        $this->calendarService = $calendarService;
        $this->searchService = $searchService;
        $this->formService = $formService;
        $this->projectService = $projectService;
        $this->actionService = $actionService;
        $this->contactService = $contactService;
        $this->generalService = $generalService;
        $this->assertionService = $assertionService;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function overviewAction(): ViewModel
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $page = $this->params('page', 1);
        $which = $this->params('which', 'upcoming');

        $form = new SearchResult();
        $data = array_merge(
            [
                'order'     => '',
                'direction' => '',
                'query'     => '',
                'facet'     => [],
            ],
            $request->getQuery()->toArray()
        );
        $searchFields = [
            'calendar_search', //To search for numbers
            'description_search',
            'highlight_description_search',
            'location_search',
            'type_search'
        ];

        if ($request->isGet()) {
            $this->searchService->setAdminSearch(
                $data['query'],
                $searchFields,
                $data['order'],
                $data['direction'],
                $which === 'upcoming',
                $which === 'past'
            );
            if (isset($data['facet'])) {
                foreach ($data['facet'] as $facetField => $values) {
                    $quotedValues = [];
                    foreach ($values as $value) {
                        $quotedValues[] = sprintf('"%s"', $value);
                    }

                    $this->searchService->addFilterQuery(
                        $facetField,
                        implode(' ' . SolariumQuery::QUERY_OPERATOR_OR . ' ', $quotedValues)
                    );
                }
            }

            $form->addSearchResults(
                $this->searchService->getQuery()->getFacetSet(),
                $this->searchService->getResultSet()->getFacetSet()
            );
            $form->setData($data);
        }

        $paginator = new Paginator(
            new SolariumPaginator($this->searchService->getSolrClient(), $this->searchService->getQuery())
        );
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? 1000 : 25);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        return new ViewModel(
            [
                'form'            => $form,
                'order'           => $data['order'],
                'direction'       => $data['direction'],
                'query'           => $data['query'],
                'badges'          => $form->getBadges(),
                'arguments'       => http_build_query($form->getFilteredData()),
                'paginator'       => $paginator,
                'calendarService' => $this->calendarService,
                'which'           => $which
            ]
        );
    }

    public function newAction()
    {
        $project = null;

        $preData = [];

        if (null !== $this->params('project')) {
            $project = $this->projectService->findProjectById((int)$this->params('project'));

            if (null === $project) {
                return $this->notFoundAction();
            }

            $preData['calendar_entity_calendar']['calendar'] = $project->getProject();
            $preData['calendar_entity_calendar']['type'] = 6;
        }

        $data = array_merge($preData, $this->getRequest()->getPost()->toArray());

        $form = $this->formService->prepare(Calendar::class, $data);
        $form->remove('delete');

        if ($this->getRequest()->isPost()) {
            /*
             * Return when cancel is pressed
             */
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/calendar/overview');
            }

            if ($form->isValid()) {
                /**
                 * @var $calendar Calendar
                 */
                $calendar = $form->getData();

                $calendar->setContact($this->identity());
                $this->calendarService->save($calendar);

                if (null !== $project) {
                    $projectCalendar = new \Project\Entity\Calendar\Calendar();
                    $projectCalendar->setProject($project);
                    $projectCalendar->setCalendar($calendar);
                    $this->projectService->save($projectCalendar);
                }

                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate('txt-calendar-item-%s-has-been-created-successfully'),
                        $calendar->getCalendar()
                    )
                );

                return $this->redirect()->toRoute('zfcadmin/calendar/calendar', ['id' => $calendar->getId()]);
            }
        }

        return new ViewModel(
            [
                'form'    => $form,
                'project' => $project,
            ]
        );
    }

    public function editAction()
    {
        $calendar = $this->calendarService->findCalendarById((int)$this->params('id'));

        if (null === $calendar) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();

        $form = $this->formService->prepare($calendar, $data);

        if (!$this->calendarService->canDeleteCalendar($calendar)) {
            $form->remove('delete');
        }

        if ($this->getRequest()->isPost()) {
            /*
             * Return when cancel is pressed
             */
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/calendar/calendar', ['id' => $calendar->getId()]);
            }
            /*
             * Return when cancel is pressed
             */
            if (isset($data['delete']) && $this->calendarService->canDeleteCalendar($calendar)) {
                $this->calendarService->delete($calendar);

                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate('txt-calendar-item-%s-has-been-deleted-successfully'),
                        $calendar->getCalendar()
                    )
                );

                return $this->redirect()->toRoute('zfcadmin/calendar/overview');
            }

            if ($form->isValid()) {
                /**
                 * @var $calendar Calendar
                 */
                $calendar = $form->getData();
                $calendar->setContact($this->identity());

                //Empty the call when the form is not set
                if (!isset($data['calendar_entity_calendar']['call'])) {
                    $calendar->setCall([]);
                }

                $this->calendarService->save($calendar);
                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate('txt-calendar-item-%s-has-been-updated-successfully'),
                        $calendar->getCalendar()
                    )
                );

                return $this->redirect()->toRoute('zfcadmin/calendar/calendar', ['id' => $calendar->getId()]);
            }
        }

        return new ViewModel(['form' => $form]);
    }

    public function selectAttendeesAction()
    {
        $calendar = $this->calendarService->findCalendarById((int)$this->params('id'));

        if (null === $calendar) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();

        $form = new CalendarContacts($this->entityManager);
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/calendar/calendar', ['id' => $calendar->getId()]);
            }

            $this->flashMessenger()->addSuccessMessage(
                sprintf(
                    $this->translator->translate('txt-contacts-of-calendar-have-been-updated-successfully'),
                    $calendar->getCalendar()
                )
            );


            $this->calendarService->updateCalendarContacts($calendar, $data);

            return $this->redirect()->toRoute('zfcadmin/calendar/calendar', ['id' => $calendar->getId()]);
        }

        return new ViewModel(
            [
                'calendarService' => $this->calendarService,
                'contactService'  => $this->contactService,
                'calendar'        => $calendar,
                'form'            => $form,
            ]
        );
    }

    public function calendarAction()
    {
        $calendar = $this->calendarService->findCalendarById((int)$this->params('id'));

        if (null === $calendar) {
            return $this->notFoundAction();
        }

        $data = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());

        $form = new CreateCalendarDocument($this->entityManager);
        $form->bind(new Document());
        //Add the missing form fields
        $data['calendar'] = $calendar->getId();
        $form->setData($data);
        if ($this->getRequest()->isPost() && $form->isValid()) {

            /**
             * @var $document Document
             */
            $document = $form->getData();
            $document->setCalendar($calendar);
            $document->setContact($this->identity());
            /**
             * Add the file
             */
            $file = $data['file'];
            $fileSizeValidator = new FilesSize(PHP_INT_MAX);
            $fileSizeValidator->isValid($file);
            $document->setSize($fileSizeValidator->size);

            $fileTypeValidator = new MimeType();
            $fileTypeValidator->isValid($file);
            $document->setContentType(
                $this->generalService->findContentTypeByContentTypeName($fileTypeValidator->type)
            );

            $documentObject = new DocumentObject();
            $documentObject->setDocument($document);
            $documentObject->setObject(file_get_contents($file['tmp_name']));

            if (empty($data['document'])) {
                $document->setDocument($file['name']);
            }

            $this->calendarService->save($documentObject);
            $this->flashMessenger()
                ->addInfoMessage(
                    sprintf(
                        $this->translator->translate(
                            'txt-calendar-document-%s-for-calendar-%s-has-successfully-been-uploaded'
                        ),
                        $document->getDocument(),
                        $calendar->getCalendar()
                    )
                );

            return $this->redirect()->toRoute(
                'zfcadmin/calendar/calendar',
                [
                    'id' => $calendar->getId(),
                ]
            );
        }
        return new ViewModel(
            [
                'calendarService'  => $this->calendarService,
                'contactService'   => $this->contactService,
                'actionService'    => $this->actionService,
                'calendar'         => $calendar,
                'form'             => $form,
                'assertionService' => $this->assertionService
            ]
        );
    }

    public function addContactAction()
    {
        $contact = $this->contactService->findContactById((int)$this->params('contactId'));

        if (null === $contact) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();


        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/contact/view/calendar', ['id' => $contact->getId()]);
            }

            $calendarContacts = $this->calendarService->addContactToCalendars($contact, $data);

            $this->flashMessenger()->addSuccessMessage(
                sprintf(
                    $this->translator->translate('txt-contact-%s-has-been-added-to-%d-calendars-successfully'),
                    $contact->parseFullName(),
                    count($calendarContacts),
                )
            );

            return $this->redirect()->toRoute('zfcadmin/contact/view/calendar', ['id' => $contact->getId()]);
        }

        return new ViewModel(
            [
                'contact'          => $contact,
                'calendarService'  => $this->calendarService,
                'upcomingCalendar' => $this->calendarService->findUpcomingCalendar(),
                'contactRoles'     => $this->calendarService->findAll(ContactRole::class),
            ]
        );
    }
}
