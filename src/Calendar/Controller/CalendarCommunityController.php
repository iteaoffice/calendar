<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Calendar\Controller;

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
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Zend\Paginator\Paginator;
use Zend\Validator\File\FilesSize;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * @method RenderCalendarContactList renderCalendarContactList()
 * @method RenderReviewCalendar renderReviewCalendar()
 */
class CalendarCommunityController extends CalendarAbstractController
{
    /**
     * @return ViewModel
     */
    public function overviewAction()
    {
        $which = $this->params('which', CalendarService::WHICH_UPCOMING);
        $page = $this->params('page', 1);
        $calendarItems = $this->getCalendarService()
            ->findCalendarItems($which, $this->zfcUserAuthentication()->getIdentity());
        $paginator = new Paginator(new PaginatorAdapter(new ORMPaginator($calendarItems)));
        $paginator->setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 15);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator->getDefaultItemCountPerPage()));
        $whichValues = $this->getCalendarService()->getWhichValues();

        return new ViewModel([
            'enableCalendarContact' => $this->getModuleOptions()->getCommunityCalendarContactEnabled(),
            'which'                 => $which,
            'paginator'             => $paginator,
            'whichValues'           => $whichValues,
        ]);
    }

    /**
     * Controller which gives an overview of upcoming invites.
     *
     * @return ViewModel
     */
    public function contactAction()
    {
        $calendarContacts = $this->getCalendarService()->findCalendarContactByContact(
            CalendarService::WHICH_UPCOMING,
            $this->zfcUserAuthentication()->getIdentity()
        );

        return new ViewModel([
            'calendarContacts' => $calendarContacts,
        ]);
    }

    /**
     * Special action which produces an HTML version of the review calendar.
     *
     * @return ViewModel
     */
    public function reviewCalendarAction()
    {
        $calendarItems = $this->getCalendarService()
            ->findCalendarItems(CalendarService::WHICH_REVIEWS, $this->zfcUserAuthentication()->getIdentity())
            ->getResult();

        return new ViewModel([
            'calendarItems' => $calendarItems,
        ]);
    }

    /**
     * Special action which produces an HTML version of the review calendar.
     *
     * @return ViewModel
     */
    public function downloadReviewCalendarAction()
    {
        $calendarItems = $this->getCalendarService()
            ->findCalendarItems(CalendarService::WHICH_REVIEWS, $this->zfcUserAuthentication()->getIdentity())
            ->getResult();

        $reviewCalendar = $this->renderReviewCalendar()->render($calendarItems);

        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine("Cache-Control: max-age=36000, must-revalidate")->addHeaderLine("Pragma: public")
            ->addHeaderLine('Content-Disposition', 'attachment; filename="review-calendar.pdf"')
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', strlen($reviewCalendar->getPDFData()));
        $response->setContent($reviewCalendar->getPDFData());

        return $response;
    }

    /**
     * @return ViewModel
     */
    public function calendarAction()
    {
        $calendarService = $this->getCalendarService()->setCalendarId($this->params('id'));
        if ($calendarService->isEmpty()) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $form = new CreateCalendarDocument($this->getEntityManager());
        $form->bind(new Document());
        //Add the missing form fields
        $data['calendar'] = $calendarService->getCalendar()->getId();
        $form->setData($data);
        if ($this->getRequest()->isPost() && $form->isValid()) {
            /** @var Document $document */
            $document = $form->getData();
            $document->setCalendar($calendarService->getCalendar());
            $document->setContact($this->zfcUserAuthentication()->getIdentity());


            /*
             * Add the file
             */
            $file = $data['file'];
            $fileSizeValidator = new FilesSize(PHP_INT_MAX);
            $fileSizeValidator->isValid($file);
            $document->setSize($fileSizeValidator->size);
            $document->setContentType($this->getGeneralService()->findContentTypeByContentTypeName($file['type']));

            /** If no name is given, take the name of the file */
            if (empty($data['document'])) {
                $document->setDocument($file['name']);
            }
            $documentObject = new DocumentObject();
            $documentObject->setDocument($document);
            $documentObject->setObject(file_get_contents($file['tmp_name']));
            $this->getCalendarService()->updateEntity($documentObject);
            $this->flashMessenger()
                ->addInfoMessage(sprintf(
                    $this->translate("txt-calendar-document-%s-for-calendar-%s-has-successfully-been-uploaded"),
                    $document->getDocument(),
                    $calendarService->getCalendar()->getCalendar()
                ));

            /*
             * Document uploaded
             */

            return $this->redirect()->toRoute('community/calendar/calendar', [
                'id' => $calendarService->getCalendar()->getId(),
            ]);
        }

        /*
         * Add the resource on the fly because it is not triggered via the link Generator
         */
        $this->getCalendarService()->addResource($calendarService->getCalendar(), CalendarAssertion::class);

        if ($calendarService->getCalendar()->getProjectCalendar()) {
            $results = $this->getProjectService()->findResultsByProjectAndContact($calendarService->getCalendar()
                ->getProjectCalendar()->getProject(), $this->zfcUserAuthentication()->getIdentity());
        } else {
            $results = null;
        }

        return new ViewModel([
            'calendarService'    => $calendarService,
            'workpackageService' => $this->getWorkpackageService(),
            'form'               => $form,
            'results'            => $results,
        ]);
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function updateStatusAction()
    {
        $calendarContactId = $this->getEvent()->getRequest()->getPost()->get('id');
        $statusId = $this->getEvent()->getRequest()->getPost()->get('status');

        /** @var Contact $calendarContact */
        $calendarContact = $this->getCalendarService()->findEntityById('Contact', $calendarContactId);

        $this->getCalendarService()->setCalendar($calendarContact->getCalendar());
        if ($this->getCalendarService()->isEmpty()) {
            return new JsonModel(['result' => 'error']);
        }
        $this->getCalendarService()->updateContactStatus($calendarContact, $statusId);

        return new JsonModel([
            'result' => 'success',
        ]);
    }

    /**
     * Special action which produces an HTML version of the review calendar.
     *
     * @return ViewModel
     */
    public function selectAttendeesAction()
    {
        /** @var CalendarService $calendarService */
        $calendarService = $this->getCalendarService()->setCalendarId($this->params('id'));
        if ($calendarService->isEmpty()) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive($this->getRequest()->getPost()->toArray());

        $form = new SelectAttendee($calendarService, $this->getContactService());
        $formValues = [];
        $formValues['contact'] = [];
        foreach ($calendarService->getCalendar()->getCalendarContact() as $calendarContact) {
            $formValues['contact'][] = $calendarContact->getContact()->getId();
        }
        $form->setData($formValues);

        if ($this->getRequest()->isPost() && $form->setData($data) && $form->isValid()) {
            $formValues = $form->getData();

            if (isset($formValues['cancel'])) {
                return $this->redirect()
                    ->toRoute(
                        'community/calendar/calendar',
                        ['id' => $calendarService->getCalendar()->getId()],
                        ['fragment' => 'attendees']
                    );
            }

            $calendar = $calendarService->getCalendar();
            $calendarContacts = $calendar->getCalendarContact();

            if (isset($formValues['contact'])) {
                foreach ($formValues['contact'] as $contactId) {
                    //Try to find the object.
                    $calendarContact = $this->getCalendarService()
                        ->findCalendarContactByContactAndCalendar($this->getContactService()->setContactId($contactId)
                            ->getContact(), $this->getCalendarService()->getCalendar());

                    $calendarContacts->removeElement($calendarContact);

                    /*
                     * Save a new one.
                     */
                    if (is_null($calendarContact)) {
                        $calendarContact = new Contact();
                        $calendarContact->setContact($this->getContactService()->setContactId($contactId)
                            ->getContact());
                        $calendarContact->setRole($this->getCalendarService()
                            ->findEntityById('ContactRole', ContactRole::ROLE_ATTENDEE));
                        $calendarContact->setStatus($this->getCalendarService()
                            ->findEntityById('ContactStatus', ContactStatus::STATUS_TENTATIVE));
                        $calendarContact->setCalendar($this->getCalendarService()->getCalendar());
                        $this->getCalendarService()->newEntity($calendarContact);
                    }
                }
            }

            //Remove the difference in the leftovers in the $calendarContacts, but only when they are attendee
            foreach ($calendarContacts as $calendarContact) {
                if ($calendarContact->getRole()->getId() === ContactRole::ROLE_ATTENDEE) {
                    $this->getCalendarService()->removeEntity($calendarContact);
                }
            }

            $this->getCalendarService()->updateEntity($calendar);

            $this->flashMessenger()
                ->addInfoMessage(sprintf(
                    $this->translate("txt-calendar-attendees-for-%s-have-been-updated"),
                    $calendarService->getCalendar()->getCalendar()
                ));

            return $this->redirect()
                ->toRoute(
                    'community/calendar/calendar',
                    ['id' => $calendarService->getCalendar()->getId()],
                    ['fragment' => 'attendees']
                );
        }

        return new ViewModel([
            'form'            => $form,
            'calendarService' => $calendarService,
        ]);
    }

    /**
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function presenceListAction()
    {
        $calendarService = $this->getCalendarService()->setCalendarId($this->params('id'));
        if ($calendarService->isEmpty()) {
            return $this->notFoundAction();
        }

        $presenceList = $this->renderCalendarContactList()->render($calendarService);

        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine("Cache-Control: max-age=36000, must-revalidate")->addHeaderLine("Pragma: public")
            ->addHeaderLine(
                'Content-Disposition',
                'attachment; filename="presence-list-' . $calendarService->getCalendar()->getCalendar() . '.pdf"'
            )
            ->addHeaderLine('Content-Type: application/pdf')
            ->addHeaderLine('Content-Length', strlen($presenceList->getPDFData()));
        $response->setContent($presenceList->getPDFData());

        return $response;
    }

    /**
     * Special action which produces an HTML version of the review calendar.
     *
     * @return ViewModel
     */
    public function sendMessageAction()
    {
        $calendarService = $this->getCalendarService()->setCalendarId($this->params('id'));
        if (is_null($calendarService->getCalendar()->getId())) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive($this->getRequest()->getPost()->toArray());

        $form = new SendMessage($calendarService, $this->getContactService());
        $form->setData($data);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            $formValues = $form->getData();

            if (isset($formValues['cancel'])) {
                return $this->redirect()
                    ->toRoute(
                        'community/calendar/calendar',
                        ['id' => $calendarService->getCalendar()->getId()],
                        ['fragment' => 'attendees']
                    );
            }
            /*
             * Send the email tot he office
             */
            $email = $this->getEmailService()->create();
            $email->setPersonal(false);
            $email->setFromContact($this->zfcUserAuthentication()->getIdentity());
            /*
             * Inject the contacts in the email
             */
            foreach ($calendarService->getCalendar()->getCalendarContact() as $calendarContact) {
                $email->addTo($calendarContact->getContact());
            }

            $email->setSubject(sprintf(
                '[[site]-%s] Message received from %s',
                $calendarService->getCalendar()->getCalendar(),
                $this->zfcUserAuthentication()->getIdentity()->getDisplayName()
            ));

            $email->setHtmlLayoutName('signature_twig');
            $email->setMessage(nl2br($form->getData()['message']));

            $this->getEmailService()->send();

            $this->flashMessenger()
                ->addSuccessMessage(sprintf(
                    $this->translate("txt-message-to-attendees-for-%s-has-been-sent"),
                    $calendarService->getCalendar()->getCalendar()
                ));

            return $this->redirect()
                ->toRoute(
                    'community/calendar/calendar',
                    ['id' => $calendarService->getCalendar()->getId()],
                    ['fragment' => 'attendees']
                );
        }

        return new ViewModel([
            'form'            => $form,
            'calendarService' => $calendarService,
        ]);
    }

    /**
     * Produce a binder of all documents in the call and type.
     */
    public function downloadBinderAction()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $calendarService = $this->getCalendarService()->setCalendarId($this->params('id'));

        if (is_null($calendarService->getCalendar()->getId())) {
            return $this->notFoundAction();
        }


        $fileName = $calendarService->getCalendar()->getDocRef() . '-binder.zip';

        /*
         * throw the filename away
         */
        if (file_exists(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName)) {
            unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName);
        }

        $zip = new \ZipArchive();
        $res = $zip->open(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName, \ZipArchive::CREATE);

        if ($res === true) {
            foreach ($calendarService->getCalendar()->getDocument() as $document) {
                $zip->addFromString(
                    $document->parseFileName(),
                    stream_get_contents($document->getObject()->first()->getObject())
                );
            }
            $zip->close();
        }

        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 36000))
            ->addHeaderLine("Cache-Control: max-age=36000, must-revalidate")
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName)
            ->addHeaderLine("Pragma: public")->addHeaderLine('Content-Type: application/octet-stream')
            ->addHeaderLine('Content-Length: ' . filesize(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName));
        $response->setContent(file_get_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName));

        return $this->response;
    }
}
