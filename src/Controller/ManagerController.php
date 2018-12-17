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

namespace Calendar\Controller;

use Application\Service\AssertionService;
use Calendar\Entity\Calendar;
use Calendar\Entity\Document;
use Calendar\Entity\DocumentObject;
use Calendar\Form\CalendarContacts;
use Calendar\Form\CreateCalendarDocument;
use Calendar\Search\Service\CalendarSearchService;
use Calendar\Service\CalendarService;
use Calendar\Service\FormService;
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

/**
 * Class ManagerController
 *
 * @package Calendar\Controller
 * @method FlashMessenger flashMessenger()
 * @method Identity|\Contact\Entity\Contact identity()
 */
final class ManagerController extends AbstractActionController
{
    /**
     * @var CalendarService
     */
    private $calendarService;
    /**
     * @var CalendarSearchService
     */
    private $searchService;
    /**
     * @var FormService
     */
    private $formService;
    /**
     * @var ProjectService
     */
    private $projectService;
    /**
     * @var ActionService
     */
    private $actionService;
    /**
     * @var ContactService
     */
    private $contactService;
    /**
     * @var GeneralService
     */
    private $generalService;
    /**
     * @var AssertionService
     */
    private $assertionService;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var TranslatorInterface
     */
    private $translator;

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
                        $quotedValues[] = \sprintf('"%s"', $value);
                    }

                    $this->searchService->addFilterQuery(
                        $facetField,
                        \implode(' ' . SolariumQuery::QUERY_OPERATOR_OR . ' ', $quotedValues)
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

        // Remove order and direction from the GET params to prevent duplication
        $filteredData = array_filter(
            $data,
            function ($key) {
                return !\in_array($key, ['order', 'direction'], true);
            },
            ARRAY_FILTER_USE_KEY
        );

        return new ViewModel(
            [
                'form'            => $form,
                'order'           => $data['order'],
                'direction'       => $data['direction'],
                'query'           => $data['query'],
                'arguments'       => \http_build_query($filteredData),
                'paginator'       => $paginator,
                'calendarService' => $this->calendarService,
                'which'           => $which
            ]
        );
    }

    public function newAction()
    {
        $project = null;

        if (null !== $this->params('project')) {
            $project = $this->projectService->findProjectById((int)$this->params('project'));

            if (null === $project) {
                return $this->notFoundAction();
            }
        }

        $data = $this->getRequest()->getPost()->toArray();
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
                        $this->translator->translate("txt-calendar-item-%s-has-been-created-successfully"),
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
            if (isset($data['delete'])) {
                $this->calendarService->delete($calendar);

                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate("txt-calendar-item-%s-has-been-deleted-successfully"),
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
                        $this->translator->translate("txt-calendar-item-%s-has-been-updated-successfully"),
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
                    $this->translator->translate("txt-contacts-of-calendar-have-been-updated-successfully"),
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

    public function setRolesAction()
    {
        $calendar = $this->calendarService->findCalendarById((int)$this->params('id'));

        if (null === $calendar) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();

        $form = $this->formService->prepare($calendar, $calendar, $data);
        if ($this->getRequest()->isPost()) {
            /*
             * Return when cancel is pressed
             */
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/calendar/calendar', ['id' => $calendar->getId()]);
            }


            if ($form->isValid()) {
                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        $this->translator->translate("txt-calendar-item-%s-has-been-updated-successfully"),
                        $calendar
                    )
                );

                return $this->redirect()->toRoute('zfcadmin/calendar/calendar', ['id' => $calendar->getId()]);
            }
        }

        return new ViewModel(
            [
                'form'            => $form,
                'calendarService' => $this->calendarService,
                'contactService'  => $this->contactService,
                'calendar'        => $calendar,
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
                            "txt-calendar-document-%s-for-calendar-%s-has-successfully-been-uploaded"
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
}
