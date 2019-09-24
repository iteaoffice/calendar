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
use Calendar\Acl\Assertion\Calendar as CalendarAssertion;
use Calendar\Controller\Plugin\RenderCalendarContactList;
use Calendar\Controller\Plugin\RenderReviewCalendar;
use Calendar\Entity\Contact;
use Calendar\Entity\ContactRole;
use Calendar\Entity\ContactStatus;
use Calendar\Entity\Document;
use Calendar\Entity\DocumentObject;
use Calendar\Form\CreateCalendarDocument;
use Calendar\Form\SelectAttendee;
use Calendar\Form\SendMessage;
use Calendar\Search\Service\CalendarSearchService;
use Calendar\Service\CalendarService;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use General\Service\EmailService;
use General\Service\GeneralService;
use Project\Service\ActionService;
use Project\Service\ProjectService;
use Project\Service\WorkpackageService;
use Search\Form\SearchResult;
use Search\Paginator\Adapter\SolariumPaginator;
use setasign\Fpdi\Tcpdf\Fpdi;
use Solarium\QueryType\Select\Query\Query as SolariumQuery;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\Mvc\Plugin\Identity\Identity;
use Zend\Paginator\Paginator;
use Zend\Validator\File\FilesSize;
use Zend\Validator\File\MimeType;
use Zend\View\Model\ViewModel;
use ZipArchive;
use function array_merge_recursive;
use function ceil;
use function file_exists;
use function file_get_contents;
use function filesize;
use function http_build_query;
use function implode;
use function nl2br;
use function sprintf;
use function strlen;
use function sys_get_temp_dir;
use function unlink;

/**
 * @package Calendar\Controller
 * @method Identity|\Contact\Entity\Contact identity()
 * @method FlashMessenger flashMessenger()
 * @method RenderReviewCalendar|Fpdi renderReviewCalendar(array $calendarItems)
 * @method RenderCalendarContactList renderCalendarContactList()
 */
final class CommunityController extends AbstractActionController
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
     * @var GeneralService
     */
    private $generalService;
    /**
     * @var ContactService
     */
    private $contactService;
    /**
     * @var ProjectService
     */
    private $projectService;
    /**
     * @var WorkpackageService
     */
    private $workpackageService;
    /**
     * @var ActionService
     */
    private $actionService;
    /**
     * @var AssertionService
     */
    private $assertionService;
    /**
     * @var EmailService
     */
    private $emailService;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        CalendarService $calendarService,
        CalendarSearchService $searchService,
        GeneralService $generalService,
        ContactService $contactService,
        ProjectService $projectService,
        WorkpackageService $workpackageService,
        ActionService $actionService,
        AssertionService $assertionService,
        EmailService $emailService,
        TranslatorInterface $translator,
        EntityManager $entityManager
    ) {
        $this->calendarService = $calendarService;
        $this->searchService = $searchService;
        $this->generalService = $generalService;
        $this->contactService = $contactService;
        $this->projectService = $projectService;
        $this->workpackageService = $workpackageService;
        $this->actionService = $actionService;
        $this->assertionService = $assertionService;
        $this->emailService = $emailService;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    public function overviewAction(): ViewModel
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $page = $this->params('page', 1);
        $which = $this->params('which', 'upcoming');

        $visibleItems = $this->calendarService->findVisibleItems($this->identity());

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
            $this->searchService->setCommunitySearch(
                $data['query'],
                $searchFields,
                $data['order'],
                $data['direction'],
                $which === 'upcoming',
                $which === 'past',
                $visibleItems,
                $this->identity()->isOffice()
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

    public function reviewCalendarAction(): ViewModel
    {
        $calendarItems = $this->calendarService
            ->findCalendarItems(
                $this->identity(),
                true,
                true
            )
            ->getQuery()->getResult();

        return new ViewModel(
            [
                'calendarItems' => $calendarItems,
            ]
        );
    }

    public function contactAction(): ViewModel
    {
        $calendarContacts = $this->calendarService->findCalendarContactByContact($this->identity());
        return new ViewModel(
            [
                'calendarContacts' => $calendarContacts,
            ]
        );
    }

    public function downloadReviewCalendarAction(): Response
    {
        $calendarItems = $this->calendarService
            ->findCalendarItems(
                $this->identity(),
                true,
                true
            )
            ->getQuery()->getResult();

        $reviewCalendar = $this->renderReviewCalendar($calendarItems);

        /** @var Response $response */
        $response = $this->getResponse();
        $response->getHeaders()
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="Review calendar.pdf"')
            ->addHeaderLine('Content-Type: application/pdf; charset="UTF-8')
            ->addHeaderLine('Content-Length', strlen($reviewCalendar->getPDFData()));
        $response->setContent($reviewCalendar->getPDFData());

        return $response;
    }

    public function calendarAction()
    {
        $calendar = $this->calendarService->findCalendarById((int)$this->params('id'));

        if (null === $calendar) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );

        $form = new CreateCalendarDocument($this->entityManager);
        $form->bind(new Document());

        $form->setData($data);
        if ($this->getRequest()->isPost() && $form->isValid()) {
            /** @var Document $document */
            $document = $form->getData();
            $document->setCalendar($calendar);
            $document->setContact($this->identity());

            /*
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


            /** If no name is given, take the name of the file */
            if (empty($data['document'])) {
                $document->setDocument($file['name']);
            }
            $documentObject = new DocumentObject();
            $documentObject->setDocument($document);
            $documentObject->setObject(file_get_contents($file['tmp_name']));
            $this->calendarService->save($documentObject);
            $this->flashMessenger()
                ->addSuccessMessage(
                    sprintf(
                        $this->translator->translate(
                            "txt-calendar-document-%s-for-calendar-%s-has-successfully-been-uploaded"
                        ),
                        $document->getDocument(),
                        $calendar->getCalendar()
                    )
                );

            return $this->redirect()->toRoute(
                'community/calendar/calendar',
                [
                    'id' => $calendar->getId(),
                ]
            );
        }

        /*
         * Add the resource on the fly because it is not triggered via the link Generator
         */
        $this->assertionService->addResource($calendar, CalendarAssertion::class);

        $results = null;
        $openActions = [];
        if ($calendar->getProjectCalendar()) {
            $results = $this->projectService->findResultsByProjectAndContact(
                $calendar->getProjectCalendar()->getProject(),
                $this->identity()
            );
            $openActions = $this->actionService->findOpenActionsByProject(
                $calendar->getProjectCalendar()->getProject()
            );
        }

        return new ViewModel(
            [
                'calendarService'    => $this->calendarService,
                'assertionService'   => $this->assertionService,
                'calendar'           => $calendar,
                'workpackageService' => $this->workpackageService,
                'projectService'     => $this->projectService,
                'form'               => $form,
                'results'            => $results,
                'openActions'        => $openActions,
                'actionService'      => $this->actionService
            ]
        );
    }


    public function selectAttendeesAction()
    {
        $calendar = $this->calendarService->findCalendarById((int)$this->params('id'));
        if (null === $calendar) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();

        $form = new SelectAttendee($calendar, $this->contactService);
        $formValues = [];
        $formValues['contact'] = [];
        foreach ($calendar->getCalendarContact() as $calendarContact) {
            $formValues['contact'][] = $calendarContact->getContact()->getId();
        }
        $form->setData($formValues);

        if ($this->getRequest()->isPost() && $form->setData($data) && $form->isValid()) {
            $formValues = $form->getData();

            if (isset($formValues['cancel'])) {
                return $this->redirect()
                    ->toRoute(
                        'community/calendar/calendar',
                        ['id' => $calendar->getId()],
                        ['fragment' => 'attendees']
                    );
            }

            $calendarContacts = $calendar->getCalendarContact();

            if (isset($formValues['contact'])) {
                foreach ($formValues['contact'] as $contactId) {
                    //Try to find the object.
                    $calendarContact = $this->calendarService
                        ->findCalendarContactByContactAndCalendar(
                            $this->contactService->findContactById((int)$contactId),
                            $calendar
                        );

                    $calendarContacts->removeElement($calendarContact);

                    /*
                     * Save a new one.
                     */
                    if (null === $calendarContact) {
                        $calendarContact = new Contact();
                        $calendarContact->setContact($this->contactService->findContactById((int)$contactId));
                        /** @var ContactRole $role */
                        $role = $this->calendarService
                            ->find(ContactRole::class, ContactRole::ROLE_ATTENDEE);
                        $calendarContact->setRole($role);
                        /** @var ContactStatus $status */
                        $status = $this->calendarService
                            ->find(ContactStatus::class, ContactStatus::STATUS_TENTATIVE);
                        $calendarContact->setStatus($status);
                        $calendarContact->setCalendar($calendar);
                        $this->calendarService->save($calendarContact);
                    }
                }
            }

            //Remove the difference in the leftovers in the $calendarContacts, but only when they are attendee
            foreach ($calendarContacts as $calendarContact) {
                if ($calendarContact->getRole()->getId() === ContactRole::ROLE_ATTENDEE) {
                    $this->calendarService->delete($calendarContact);
                }
            }

            $this->calendarService->save($calendar);

            $this->flashMessenger()
                ->addInfoMessage(
                    sprintf(
                        $this->translator->translate("txt-calendar-attendees-for-%s-have-been-updated"),
                        $calendar->getCalendar()
                    )
                );

            return $this->redirect()
                ->toRoute(
                    'community/calendar/calendar',
                    ['id' => $calendar->getId()],
                    ['fragment' => 'attendees']
                );
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

    public function presenceListAction(): Response
    {
        $calendar = $this->calendarService->findCalendarById((int)$this->params('id'));

        /** @var Response $response */
        $response = $this->getResponse();

        if (null === $calendar) {
            $response->setStatusCode(Response::STATUS_CODE_404);

            return $response;
        }

        $presenceList = $this->renderCalendarContactList()->renderPresenceList($calendar);


        $response->getHeaders()
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="Attendees list ' . $calendar->getCalendar() . '.pdf"'
            )
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', strlen($presenceList->getPDFData()));
        $response->setContent($presenceList->getPDFData());

        return $response;
    }

    public function signatureListAction(): Response
    {
        $calendar = $this->calendarService->findCalendarById((int)$this->params('id'));

        /** @var Response $response */
        $response = $this->getResponse();

        if (null === $calendar) {
            $response->setStatusCode(Response::STATUS_CODE_404);

            return $response;
        }

        $presenceList = $this->renderCalendarContactList()->renderSignatureList($calendar);


        $response->getHeaders()
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="Signature list ' . $calendar->getCalendar() . '.pdf"'
            )
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', strlen($presenceList->getPDFData()));
        $response->setContent($presenceList->getPDFData());

        return $response;
    }

    public function sendMessageAction()
    {
        $calendar = $this->calendarService->findCalendarById((int)$this->params('id'));

        if (null === $calendar) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();

        $form = new SendMessage();
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            $formValues = $form->getData();

            if (isset($formValues['cancel'])) {
                return $this->redirect()
                    ->toRoute(
                        'community/calendar/calendar',
                        ['id' => $calendar->getId()],
                        ['fragment' => 'attendees']
                    );
            }

            $this->emailService->setWebInfo('calendar/message');

            foreach ($calendar->getCalendarContact() as $calendarContact) {
                $this->emailService->addTo($calendarContact->getContact());
            }

            //Use HTML Entities to be sure that all chars are escaped
            $this->emailService->setTemplateVariable('message', nl2br($form->getData()['message']));
            $this->emailService->setTemplateVariable('calendar', $calendar->getCalendar());
            $this->emailService->setTemplateVariable('sender_name', $this->identity()->parseFullName());

            $this->emailService->send();

            $this->flashMessenger()
                ->addSuccessMessage(
                    sprintf(
                        $this->translator->translate("txt-message-to-attendees-for-%s-has-been-sent"),
                        $calendar->getCalendar()
                    )
                );

            return $this->redirect()
                ->toRoute(
                    'community/calendar/calendar',
                    ['id' => $calendar->getId()],
                    ['fragment' => 'attendees']
                );
        }

        return new ViewModel(
            [
                'form'            => $form,
                'calendarService' => $this->calendarService,
                'calendar'        => $calendar,
            ]
        );
    }

    public function downloadBinderAction(): Response
    {
        set_time_limit(0);

        $calendar = $this->calendarService->findCalendarById((int)$this->params('id'));

        /** @var Response $response */
        $response = $this->getResponse();

        if (null === $calendar) {
            $response->setStatusCode(Response::STATUS_CODE_404);

            return $response;
        }

        $fileName = $calendar->getDocRef() . '-binder.zip';

        /*
         * throw the filename away
         */
        if (file_exists(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName)) {
            unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName);
        }

        $zip = new ZipArchive();
        $res = $zip->open(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName, ZipArchive::CREATE);

        if ($res === true) {
            foreach ($calendar->getDocument() as $document) {
                $zip->addFromString(
                    $document->parseFileName(),
                    stream_get_contents($document->getObject()->first()->getObject())
                );
            }
            $zip->close();
        }


        $response->getHeaders()
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName)
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine('Content-Type: application/octet-stream')
            ->addHeaderLine('Content-Length: ' . filesize(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName));
        $response->setContent(file_get_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName));

        return $response;
    }
}
