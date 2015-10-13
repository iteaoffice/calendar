<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Calendar\Controller;

use Calendar\Entity\Calendar;
use Calendar\Entity\Document;
use Calendar\Entity\DocumentObject;
use Calendar\Form\CreateCalendarDocument;
use Calendar\Service\CalendarService;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\Validator\File\FilesSize;
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
        $page = $this->getEvent()->getRouteMatch()->getParam('page', 1);
        $birthDays = $this->getContactService()->findContactsWithDateOfBirth();
        $calendarItems = $this->getCalendarService()->findCalendarItems(
            $which,
            $this->zfcUserAuthentication()->getIdentity()
        )->getResult();
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
                    _("Birthday of %s (%s)"),
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

        return new ViewModel(
            [
                'which'       => $which,
                'paginator'   => $paginator,
                'whichValues' => $whichValues,
            ]
        );
    }

    /**
     * Action for the creation of a new project.
     *
     * @return ViewModel
     */
    public function newAction()
    {
        $calendar = new Calendar();
        $data = $this->getRequest()->getPost()->toArray();
        $form = $this->getFormService()->prepare('calendar', $calendar, $data);
        if ($this->getRequest()->isPost() && $form->isValid()) {
            /*
             * Return when cancel is pressed
             */
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/calendar-manager/overview');
            }
            /**
             * @var $calendar Calendar
             */
            $calendar = $form->getData();
            $calendar->setContact($this->zfcUserAuthentication()->getIdentity());
            $calendar = $this->getCalendarService()->newEntity($calendar);
            $this->flashMessenger()->setNamespace('success')->addMessage(
                sprintf(
                    $this->translate("txt-calendar-item-%s-has-been-created-successfully"),
                    $calendar->getCalendar()
                )
            );

            return $this->redirect()->toRoute('zfcadmin/calendar-manager/calendar', ['id' => $calendar->getId()]);
        }

        return new ViewModel(['form' => $form]);
    }

    /**
     * Action to edit a calendar element.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $calendarService = $this->getCalendarService()->setCalendarId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        $data = $this->getRequest()->getPost()->toArray();

        $form = $this->getFormService()->prepare('calendar', $calendarService->getCalendar(), $data);
        if ($this->getRequest()->isPost()) {
            /*
             * Return when cancel is pressed
             */
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/calendar-manager/calendar',
                    ['id' => $calendarService->getCalendar()->getId()]
                );
            }
            /*
             * Return when cancel is pressed
             */
            if (isset($data['delete'])) {
                $this->getCalendarService()->removeEntity($calendarService->getCalendar());

                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(
                        $this->translate("txt-calendar-item-%s-has-been-deleted-successfully"),
                        $calendarService->getCalendar()->getCalendar()
                    )
                );

                return $this->redirect()->toRoute('zfcadmin/calendar-manager/overview');
            }

            if ($form->isValid()) {
                /**
                 * @var $calendar Calendar
                 */
                $calendar = $form->getData();
                $calendar->setContact($this->zfcUserAuthentication()->getIdentity());
                $calendar = $this->getCalendarService()->newEntity($calendar);
                $this->flashMessenger()->setNamespace('success')->addMessage(
                    sprintf(
                        $this->translate("txt-calendar-item-%s-has-been-updated-successfully"),
                        $calendar->getCalendar()
                    )
                );

                return $this->redirect()->toRoute('zfcadmin/calendar-manager/calendar', ['id' => $calendar->getId()]);
            }
        }

        return new ViewModel(['form' => $form]);
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
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $form = new CreateCalendarDocument($entityManager);
        $form->bind(new Document());
        //Add the missing form fields
        $data['calendar'] = $calendarService->getCalendar()->getId();
        $form->setData($data);
        if ($this->getRequest()->isPost() && $form->isValid()) {
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
            $documentObject = new DocumentObject();
            $documentObject->setDocument($document);
            $documentObject->setObject(file_get_contents($file['tmp_name']));
            $this->getCalendarService()->updateEntity($documentObject);
            $this->flashMessenger()->addInfoMessage(
                sprintf(
                    $this->translate("txt-calendar-document-%s-for-calendar-%s-has-successfully-been-uploaded"),
                    $document->getDocument(),
                    $calendarService->getCalendar()->getCalendar()
                )
            );

            /*
             * Document uploaded
             */

            return $this->redirect()->toRoute(
                'zfcadmin/calendar-manager/calendar',
                [
                    'id' => $calendarService->getCalendar()->getId(),
                ]
            );
        }

        return new ViewModel(
            [
                'calendarService' => $calendarService,
                'form'            => $form,
            ]
        );
    }
}
