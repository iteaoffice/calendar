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

use Calendar\Entity\Calendar;
use Calendar\Entity\Contact;
use Calendar\Entity\ContactRole;
use Calendar\Entity\Document;
use Calendar\Entity\DocumentObject;
use Calendar\Form\CalendarContacts;
use Calendar\Form\CreateCalendarDocument;
use Calendar\Service\CalendarService;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\Validator\File\FilesSize;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 *
 */
class CalendarManagerController extends CalendarAbstractController
{
    /**
     * Display the calendar on the website.
     *
     * @return ViewModel
     */
    public function overviewAction()
    {
        $which = $this->getEvent()->getRouteMatch()->getParam('which', CalendarService::WHICH_UPCOMING);
        $page = $this->params('page', 1);
        $birthDays = $this->getContactService()->findContactsWithDateOfBirth();
        $calendarItems = $this->getCalendarService()
            ->findCalendarItems($which, $this->zfcUserAuthentication()->getIdentity())->getResult();
        $calender = [];

        $today = new \DateTime();

        foreach ($birthDays as $birthDay) {
            /*
             * Produce a index which holds the current year
             */
            $dateOfBirth = $birthDay->getDateOfBirth();

            $birthDayDate = \DateTime::createFromFormat(
                'Y-m-d',
                sprintf("%s-%s", date('Y'), $dateOfBirth->format('m-d'))
            );

            if ($birthDayDate < $today) {
                continue;
            }

            $index = $birthDayDate->format('Y-m-d');

            $calender[$index][] = [
                'item' => sprintf(
                    $this->translate("Birthday of %s (%s)"),
                    $birthDay->getDisplayName(),
                    $birthDay->getDateOfBirth()->format("Y")
                ),
                'date' => $birthDayDate,
            ];
        }
        /**
         * @var $calendarItem Calendar
         */
        foreach ($calendarItems as $calendarItem) {
            if ($calendarItem->getDateFrom() < $today) {
                continue;
            }

            $index = $calendarItem->getDateFrom()->format('Y-m-d');

            $calender[$index][] = [
                'item'     => $calendarItem->getCalendar(),
                'calendar' => $calendarItem,
                'date'     => null,
            ];
        }

        ksort($calender);

        $paginator = new Paginator(new ArrayAdapter($calender));
        $paginator->setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : PHP_INT_MAX);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator->getDefaultItemCountPerPage()));
        $whichValues = $this->getCalendarService()->getWhichValues();

        return new ViewModel([
            'which'           => $which,
            'paginator'       => $paginator,
            'whichValues'     => $whichValues,
            'calendarService' => $this->getCalendarService(),
        ]);
    }

    /**
     * Action for the creation of a new project.
     *
     * @return ViewModel
     */
    public function newAction()
    {
        $project = null;
        if (!is_null($this->params('project'))) {
            $project = $this->getProjectService()->findProjectById($this->params('project'));
            if ($project->isEmpty()) {
                return $this->notFoundAction();
            }
        }

        $calendar = new Calendar();
        $data = $this->getRequest()->getPost()->toArray();
        $form = $this->getFormService()->prepare($calendar, $calendar, $data);
        $form->remove('delete');
        if ($this->getRequest()->isPost()) {
            /*
             * Return when cancel is pressed
             */
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/calendar-manager/overview');
            }

            if ($form->isValid()) {
                /**
                 * @var $calendar Calendar
                 */
                $calendar = $form->getData();

                $calendar->setContact($this->zfcUserAuthentication()->getIdentity());
                $calendar = $this->getCalendarService()->newEntity($calendar);

                if (!is_null($project)) {
                    $projectCalendar = new \Project\Entity\Calendar\Calendar();
                    $projectCalendar->setProject($project);
                    $projectCalendar->setCalendar($calendar);
                    $this->getProjectService()->updateEntity($projectCalendar);
                }

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(
                        $this->translate("txt-calendar-item-%s-has-been-created-successfully"),
                        $calendar->getCalendar()
                    ));

                return $this->redirect()->toRoute('zfcadmin/calendar-manager/calendar', ['id' => $calendar->getId()]);
            }
        }

        return new ViewModel([
            'form'    => $form,
            'project' => $project
        ]);
    }

    /**
     * Action to edit a calendar element.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $calendar = $this->getCalendarService()->findCalendarById($this->params('id'));
        if (is_null($calendar)) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();
        $form = $this->getFormService()->prepare($calendar, $calendar, $data);

        if ($this->getRequest()->isPost()) {
            /*
             * Return when cancel is pressed
             */
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/calendar-manager/calendar', ['id' => $calendar->getId()]);
            }
            /*
             * Return when cancel is pressed
             */
            if (isset($data['delete'])) {
                $this->getCalendarService()->removeEntity($calendar);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(
                        $this->translate("txt-calendar-item-%s-has-been-deleted-successfully"),
                        $calendar->getCalendar()
                    ));

                return $this->redirect()->toRoute('zfcadmin/calendar-manager/overview');
            }

            if ($form->isValid()) {
                /**
                 * @var $calendar Calendar
                 */
                $calendar = $form->getData();
                $calendar->setContact($this->zfcUserAuthentication()->getIdentity());
                $calendar = $this->getCalendarService()->newEntity($calendar);
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(
                        $this->translate("txt-calendar-item-%s-has-been-updated-successfully"),
                        $calendar->getCalendar()
                    ));

                return $this->redirect()->toRoute('zfcadmin/calendar-manager/calendar', ['id' => $calendar->getId()]);
            }
        }

        return new ViewModel(['form' => $form]);
    }

    /**
     * Action to edit a calendar element.
     *
     * @return ViewModel
     */
    public function selectAttendeesAction()
    {
        $calendar = $this->getCalendarService()->findCalendarById($this->params('id'));
        if (is_null($calendar)) {
            return $this->notFoundAction();
        }

        $data = array_merge($this->getRequest()->getPost()->toArray());


        $form = new CalendarContacts($this->getSelectionService());
        $form->setData($data);


        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/calendar-manager/calendar', ['id' => $calendar->getId()]);
            }


            $this->getCalendarService()->updateCalendarContacts($calendar, $data);

            return $this->redirect()->toRoute('zfcadmin/calendar-manager/calendar', ['id' => $calendar->getId()]);
        }

        return new ViewModel([
            'calendarService' => $this->getCalendarService(),
            'contactService'  => $this->getContactService(),
            'calendar'        => $calendar,
            'form'            => $form
        ]);
    }

    /**
     * Action to edit a calendar element.
     *
     * @return ViewModel
     */
    public function setRolesAction()
    {
        $calendar = $this->getCalendarService()->findCalendarById($this->params('id'));
        if (is_null($calendar)) {
            return $this->notFoundAction();
        }

        $data = $this->getRequest()->getPost()->toArray();

        $form = $this->getFormService()->prepare($calendar, $calendar, $data);
        if ($this->getRequest()->isPost()) {
            /*
             * Return when cancel is pressed
             */
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/calendar-manager/calendar', ['id' => $calendar->getId()]);
            }


            if ($form->isValid()) {
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(
                        $this->translate("txt-calendar-item-%s-has-been-updated-successfully"),
                        $calendar
                    ));

                return $this->redirect()->toRoute('zfcadmin/calendar-manager/calendar', ['id' => $calendar->getId()]);
            }
        }

        return new ViewModel([
            'form'            => $form,
            'calendarService' => $this->getCalendarService(),
            'contactService'  => $this->getContactService(),
            'calendar'        => $calendar,
        ]);
    }

    /**
     * @return JsonModel
     */
    public function getRolesAction()
    {
        /** @var ContactRole[] $calendarRoles */
        $calendarRoles = $this->getCalendarService()->findAll(ContactRole::class);

        $roles = [];
        /**
         * @var $role ContactRole
         */
        foreach ($calendarRoles as $role) {
            $roles[] = [
                'value' => $role->getId(),
                'text'  => $role->getRole(),
            ];
        }

        return new JsonModel($roles);
    }

    public function updateRoleAction()
    {
        $calendarContactId = $this->params()->fromPost('pk');
        $roleId = $this->params()->fromPost('value');

        /**
         * @var Contact $calendarContact
         */
        $calendarContact = $this->getCalendarService()->findEntityById(Contact::class, $calendarContactId);

        if (is_null($calendarContact)) {
            return $this->notFoundAction();
        }
        /**
         * @var ContactRole $role
         */
        $role = $this->getCalendarService()->findEntityById(ContactRole::class, $roleId);

        if (is_null($role)) {
            return $this->notFoundAction();
        }

        $calendarContact->setRole($role);
        $this->getCalendarService()->updateEntity($calendarContact);

        return new JsonModel();
    }

    /**
     * @return ViewModel
     */
    public function calendarAction()
    {
        $calendar = $this->getCalendarService()->findCalendarById($this->params('id'));
        if (is_null($calendar)) {
            return $this->notFoundAction();
        }

        $data = array_merge($this->getRequest()->getPost()->toArray(), $this->getRequest()->getFiles()->toArray());

        $form = new CreateCalendarDocument($this->getEntityManager());
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

            if (empty($data['document'])) {
                $document->setDocument($file['name']);
            }

            $this->getCalendarService()->updateEntity($documentObject);
            $this->flashMessenger()
                ->addInfoMessage(sprintf(
                    $this->translate("txt-calendar-document-%s-for-calendar-%s-has-successfully-been-uploaded"),
                    $document->getDocument(),
                    $calendar->getCalendar()
                ));

            /*
             * Document uploaded
             */

            return $this->redirect()->toRoute('zfcadmin/calendar-manager/calendar', [
                'id' => $calendar->getId(),
            ]);
        }

        return new ViewModel([
            'calendarService' => $this->getCalendarService(),
            'contactService'  => $this->getContactService(),
            'calendar'        => $calendar,
            'form'            => $form,
        ]);
    }
}
