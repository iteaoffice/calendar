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
use Calendar\Service\CalendarService;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use General\Service\EmailService;
use General\Service\GeneralService;
use Project\Service\ProjectService;
use Project\Service\WorkpackageService;
use setasign\Fpdi\TcpdfFpdi;
use Zend\Http\Response;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\Mvc\Plugin\Identity\Identity;
use Zend\Paginator\Paginator;
use Zend\Validator\File\FilesSize;
use Zend\Validator\File\MimeType;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Class CalendarCommunityController
 *
 * @package Calendar\Controller
 * @method Identity|\Contact\Entity\Contact identity()
 * @method FlashMessenger flashMessenger()
 * @method RenderReviewCalendar|TcpdfFpdi renderReviewCalendar(array $calendarItems)
 * @method RenderCalendarContactList renderCalendarContactList()
 */
class CommunityController extends AbstractActionController
{
    /**
     * @var CalendarService
     */
    protected $calendarService;
    /**
     * @var GeneralService
     */
    protected $generalService;
    /**
     * @var ContactService
     */
    protected $contactService;
    /**
     * @var ProjectService
     */
    protected $projectService;
    /**
     * @var WorkpackageService
     */
    protected $workpackageService;
    /**
     * @var AssertionService
     */
    protected $assertionService;
    /**
     * @var EmailService
     */
    protected $emailService;
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * CommunityController constructor.
     *
     * @param CalendarService     $calendarService
     * @param GeneralService      $generalService
     * @param ContactService      $contactService
     * @param ProjectService      $projectService
     * @param WorkpackageService  $workpackageService
     * @param AssertionService    $assertionService
     * @param EmailService        $emailService
     * @param TranslatorInterface $translator
     * @param EntityManager       $entityManager
     */
    public function __construct(
        CalendarService $calendarService,
        GeneralService $generalService,
        ContactService $contactService,
        ProjectService $projectService,
        WorkpackageService $workpackageService,
        AssertionService $assertionService,
        EmailService $emailService,
        TranslatorInterface $translator,
        EntityManager $entityManager
    ) {
        $this->calendarService = $calendarService;
        $this->generalService = $generalService;
        $this->contactService = $contactService;
        $this->projectService = $projectService;
        $this->workpackageService = $workpackageService;
        $this->assertionService = $assertionService;
        $this->emailService = $emailService;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }


    /**
     * @return ViewModel
     */
    public function overviewAction(): ViewModel
    {
        $which = $this->params('which', CalendarService::WHICH_UPCOMING);
        $page = $this->params('page', 1);
        $calendarItems = $this->calendarService
            ->findCalendarItems($which, $this->identity());
        $paginator = new Paginator(new PaginatorAdapter(new ORMPaginator($calendarItems)));
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 25);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));
        $whichValues = $this->calendarService->getWhichValues();

        return new ViewModel(
            [
                'calendarService' => $this->calendarService,
                'which'           => $which,
                'paginator'       => $paginator,
                'whichValues'     => $whichValues,
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function contactAction(): ViewModel
    {
        $calendarContacts = $this->calendarService->findCalendarContactByContact(
            CalendarService::WHICH_UPCOMING,
            $this->identity()
        );

        return new ViewModel(
            [
                'calendarContacts' => $calendarContacts,
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function reviewCalendarAction(): ViewModel
    {
        $calendarItems = $this->calendarService
            ->findCalendarItems(
                CalendarService::WHICH_REVIEWS,
                $this->identity()
            )
            ->getResult();

        return new ViewModel(
            [
                'calendarItems' => $calendarItems,
            ]
        );
    }

    /**
     * @return Response
     */
    public function downloadReviewCalendarAction(): Response
    {
        $calendarItems = $this->calendarService
            ->findCalendarItems(
                CalendarService::WHICH_REVIEWS,
                $this->identity()
            )
            ->getResult();

        $reviewCalendar = $this->renderReviewCalendar($calendarItems);

        /** @var Response $response */
        $response = $this->getResponse();
        $response->getHeaders()
            ->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine('Cache-Control: max-age=36000, must-revalidate')
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="review-calendar.pdf"')
            ->addHeaderLine('Content-Type: application/pdf; charset="UTF-8')
            ->addHeaderLine('Content-Length', \strlen($reviewCalendar->getPDFData()));
        $response->setContent($reviewCalendar->getPDFData());

        return $response;
    }

    /**
     * @return Response|ViewModel
     */
    public function calendarAction()
    {
        $calendar = $this->calendarService->findCalendarById((int)$this->params('id'));

        if (null === $calendar) {
            return $this->notFoundAction();
        }

        $data = \array_merge_recursive(
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

            /*
             * Document uploaded
             */

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
        if ($calendar->getProjectCalendar()) {
            $results = $this->projectService->findResultsByProjectAndContact(
                $calendar->getProjectCalendar()->getProject(),
                $this->identity()
            );
        }

        return new ViewModel(
            [
                'calendarService'    => $this->calendarService,
                'calendar'           => $calendar,
                'workpackageService' => $this->workpackageService,
                'projectService'     => $this->projectService,
                'form'               => $form,
                'results'            => $results,
            ]
        );
    }

    /**
     * @return JsonModel
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateStatusAction(): JsonModel
    {
        $calendarContactId = $this->getEvent()->getRequest()->getPost()->get('id');
        $statusId = $this->getEvent()->getRequest()->getPost()->get('status');

        /** @var Contact $calendarContact */
        $calendarContact = $this->calendarService->find(Contact::class, $calendarContactId);

        if (null === $calendarContact) {
            return new JsonModel(['result' => 'error']);
        }
        $this->calendarService->updateContactStatus($calendarContact, $statusId);

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }

    /**
     * @return Response|ViewModel
     */
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

    /**
     * @return Response
     */
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


        $response->getHeaders()->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine('Cache-Control: max-age=36000, must-revalidate')
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="presence-list-' . $calendar->getCalendar() . '.pdf"'
            )
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', \strlen($presenceList->getPDFData()));
        $response->setContent($presenceList->getPDFData());

        return $response;
    }

    /**
     * @return Response
     */
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


        $response->getHeaders()->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine('Cache-Control: max-age=36000, must-revalidate')
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="presence-list-' . $calendar->getCalendar() . '.pdf"'
            )
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', \strlen($presenceList->getPDFData()));
        $response->setContent($presenceList->getPDFData());

        return $response;
    }

    /**
     * Special action which produces an HTML version of the review calendar.
     *
     * @return ViewModel|Response
     */
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
            /*
             * Send the email tot he office
             */
            $email = $this->emailService->create();
            $email->setPersonal(false);
            $email->setFromContact($this->identity());
            /*
             * Inject the contacts in the email
             */
            foreach ($calendar->getCalendarContact() as $calendarContact) {
                $email->addTo($calendarContact->getContact());
            }

            $email->setSubject(
                sprintf(
                    '[[site]-%s] Message received from %s',
                    $calendar->getCalendar(),
                    $this->identity()->getDisplayName()
                )
            );

            $email->setHtmlLayoutName('signature_twig');
            $email->setMessage(nl2br($form->getData()['message']));

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

    /**
     * Produce a binder of all documents in the call and type.
     *
     * @return Response
     */
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

        $zip = new \ZipArchive();
        $res = $zip->open(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName, \ZipArchive::CREATE);

        if ($res === true) {
            foreach ($calendar->getDocument() as $document) {
                $zip->addFromString(
                    $document->parseFileName(),
                    stream_get_contents($document->getObject()->first()->getObject())
                );
            }
            $zip->close();
        }


        $response->getHeaders()->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine('Cache-Control: max-age=36000, must-revalidate')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName)
            ->addHeaderLine('Pragma: public')
            ->addHeaderLine('Content-Type: application/octet-stream')
            ->addHeaderLine('Content-Length: ' . filesize(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName));
        $response->setContent(file_get_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName));

        return $response;
    }
}
