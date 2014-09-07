<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category  Calendar
 * @package   Controller
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Controller;

use Calendar\Acl\Assertion\Calendar as CalendarAssertion;
use Calendar\Entity\Contact;
use Calendar\Entity\ContactRole;
use Calendar\Entity\ContactStatus;
use Calendar\Entity\Document;
use Calendar\Entity\DocumentObject;
use Calendar\Form\CreateCalendarDocument;
use Calendar\Form\SelectAttendee;
use Calendar\Service\CalendarService;
use Calendar\Service\CalendarServiceAwareInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Project\Service\WorkpackageServiceAwareInterface;
use Zend\Paginator\Paginator;
use Zend\Validator\File\FilesSize;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 *
 */
class CalendarCommunityController extends CalendarAbstractController implements
    CalendarServiceAwareInterface,
    WorkpackageServiceAwareInterface
{
    /**
     * @return ViewModel
     */
    public function overviewAction()
    {
        $which = $this->getEvent()->getRouteMatch()->getParam('which', CalendarService::WHICH_UPCOMING);
        $page = $this->getEvent()->getRouteMatch()->getParam('page', 1);
        $calendarItems = $this->getCalendarService()->findCalendarItems(
            $which,
            $this->zfcUserAuthentication()->getIdentity()
        );
        $paginator = new Paginator(new PaginatorAdapter(new ORMPaginator($calendarItems)));
        $paginator->setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 15);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator->getDefaultItemCountPerPage()));
        $whichValues = $this->getCalendarService()->getWhichValues();

        return new ViewModel(
            [
                'which'       => $which,
                'paginator'   => $paginator,
                'whichValues' => $whichValues
            ]
        );
    }

    /**
     * Controller which gives an overview of upcoming invites
     *
     * @return ViewModel
     */
    public function contactAction()
    {
        $calendarContacts = $this->getCalendarService()->findCalendarContactByContact(
            CalendarService::WHICH_UPCOMING,
            $this->zfcUserAuthentication()->getIdentity()
        );

        return new ViewModel(
            [
                'calendarContacts' => $calendarContacts,
            ]
        );
    }

    /**
     * Special action which produces an HTML version of the review calendar
     *
     * @return ViewModel
     */
    public function reviewCalendarAction()
    {
        $calendarItems = $this->getCalendarService()->findCalendarItems(
            CalendarService::WHICH_REVIEWS,
            $this->zfcUserAuthentication()->getIdentity()
        )->getResult();

        return new ViewModel(
            [
                'calendarItems' => $calendarItems,
            ]
        );
    }

    /**
     * @return ViewModel
     */
    public function calendarAction()
    {
        $calendarService = $this->getCalendarService()->setCalendarId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        if (is_null($calendarService->getCalendar()->getId())) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getFiles()->toArray()
        );
        $entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $form = new CreateCalendarDocument($entityManager);
        $form->bind(new Document());
        //Add the missing form fields
        $data['calendar'] = $calendarService->getCalendar()->getId();
        $form->setData($data);
        if ($this->getRequest()->isPost() && $form->isValid()) {
            $document = $form->getData();
            $document->setCalendar($calendarService->getCalendar());
            $document->setContact($this->zfcUserAuthentication()->getIdentity());
            /**
             * Add the file
             */
            $file = $data['file'];
            $fileSizeValidator = new FilesSize(PHP_INT_MAX);
            $fileSizeValidator->isValid($file);
            $document->setSize($fileSizeValidator->size);
            $document->setContentType($this->getGeneralService()->findContentTypeByContentTypeName($file['type']));
            $documentObject = new DocumentObject();
            $documentObject->setDocument($document);
            $documentObject->setObject(file_get_contents($file['tmp_name']));
            $this->getCalendarService()->updateEntity($documentObject);
            $this->flashMessenger()->addInfoMessage(
                sprintf(
                    _("txt-calendar-document-%s-for-calendar-%s-has-successfully-been-uploaded"),
                    $document->getDocument(),
                    $calendarService->getCalendar()->getCalendar()
                )
            );

            /**
             * Document uploaded
             */

            return $this->redirect()->toRoute(
                'community/calendar/calendar',
                [
                    'id' => $calendarService->getCalendar()->getId()
                ]
            );
        }

        /**
         * Add the resource on the fly because it is not triggered via the link Generator
         */
        $this->getCalendarService()->addResource($calendarService->getCalendar(), CalendarAssertion::class);

        return new ViewModel(
            [
                'calendarService'    => $calendarService,
                'workpackageService' => $this->getWorkpackageService(),
                'form'               => $form
            ]
        );
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function updateStatusAction()
    {
        $calendarContactId = $this->getEvent()->getRequest()->getPost()->get('id');
        $statusId = $this->getEvent()->getRequest()->getPost()->get('status');

        $calendarContact = $this->getCalendarService()->findEntityById('Contact', $calendarContactId);

        $this->getCalendarService()->setCalendar($calendarContact->getCalendar());
        if ($this->getCalendarService()->isEmpty()) {
            return new JsonModel(['result' => 'error']);
        }
        $this->getCalendarService()->updateContactStatus(
            $calendarContact,
            $statusId
        );

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }

    /**
     * Special action which produces an HTML version of the review calendar
     *
     * @return ViewModel
     */
    public function selectAttendeesAction()
    {
        $calendarService = $this->getCalendarService()->setCalendarId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );
        if (is_null($calendarService->getCalendar()->getId())) {
            return $this->notFoundAction();
        }

        $data = array_merge_recursive(
            $this->getRequest()->getPost()->toArray()
        );

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
                return $this->redirect()->toRoute(
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
                    $calendarContact = $this->getCalendarService()->findCalendarContactByContactAndCalendar(
                        $this->getContactService()->setContactId($contactId)->getContact(),
                        $this->getCalendarService()->getCalendar()
                    );

                    $calendarContacts->removeElement($calendarContact);

                    /**
                     * Save a new one.
                     */
                    if (is_null($calendarContact)) {
                        $calendarContact = new Contact();
                        $calendarContact->setContact(
                            $this->getContactService()->setContactId($contactId)->getContact()
                        );
                        $calendarContact->setRole(
                            $this->getCalendarService()->findEntityById('ContactRole', ContactRole::ROLE_ATTENDEE)
                        );
                        $calendarContact->setStatus(
                            $this->getCalendarService()->findEntityById(
                                'ContactStatus',
                                ContactStatus::STATUS_TENTATIVE
                            )
                        );
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

            $this->flashMessenger()->addInfoMessage(
                sprintf(
                    _("txt-calendar-attendees-for-%s-have-been-updated"),
                    $calendarService->getCalendar()->getCalendar()
                )
            );

            return $this->redirect()->toRoute(
                'community/calendar/calendar',
                ['id' => $calendarService->getCalendar()->getId()],
                ['fragment' => 'attendees']
            );

        }

        return new ViewModel(
            [
                'form'            => $form,
                'calendarService' => $calendarService,
            ]
        );
    }
}
