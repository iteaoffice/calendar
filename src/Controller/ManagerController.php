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

use Calendar\Entity\Calendar;
use Calendar\Entity\Document;
use Calendar\Entity\DocumentObject;
use Calendar\Form\CalendarContacts;
use Calendar\Form\CreateCalendarDocument;
use Calendar\Service\CalendarService;
use Calendar\Service\FormService;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use General\Service\GeneralService;
use Project\Service\ActionService;
use Project\Service\ProjectService;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\Mvc\Plugin\Identity\Identity;
use Zend\Paginator\Adapter\ArrayAdapter;
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
class ManagerController extends AbstractActionController
{
    /**
     * @var CalendarService
     */
    protected $calendarService;
    /**
     * @var FormService
     */
    protected $formService;
    /**
     * @var ProjectService
     */
    protected $projectService;
    /**
     * @var ActionService
     */
    protected $actionService;
    /**
     * @var ContactService
     */
    protected $contactService;
    /**
     * @var GeneralService
     */
    protected $generalService;
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * ManagerController constructor.
     *
     * @param CalendarService     $calendarService
     * @param FormService         $formService
     * @param ProjectService      $projectService
     * @param ActionService       $actionService
     * @param ContactService      $contactService
     * @param GeneralService      $generalService
     * @param EntityManager       $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(
        CalendarService $calendarService,
        FormService $formService,
        ProjectService $projectService,
        ActionService $actionService,
        ContactService $contactService,
        GeneralService $generalService,
        EntityManager $entityManager,
        TranslatorInterface $translator
    ) {
        $this->calendarService = $calendarService;
        $this->formService = $formService;
        $this->projectService = $projectService;
        $this->actionService = $actionService;
        $this->contactService = $contactService;
        $this->generalService = $generalService;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }


    /**
     * Display the calendar on the website.
     *
     * @return ViewModel
     */
    public function overviewAction(): ViewModel
    {
        $which = $this->params('which', CalendarService::WHICH_UPCOMING);
        $page = $this->params('page', 1);

        $birthDays = $this->contactService->findContactsWithDateOfBirth();
        $calendarItems = $this->calendarService->findCalendarItems($which, $this->identity())->getResult();
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
                    $this->translator->translate("Birthday of %s (%s)"),
                    $birthDay->getDisplayName(),
                    $birthDay->getDateOfBirth()->format('Y')
                ),
                'date' => $birthDayDate,
            ];
        }
        /**
         * @var $calendarItems Calendar[]
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
        $paginator::setDefaultItemCountPerPage(PHP_INT_MAX);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));
        $whichValues = $this->calendarService->getWhichValues();

        return new ViewModel(
            [
                'which'           => $which,
                'paginator'       => $paginator,
                'whichValues'     => $whichValues,
                'calendarService' => $this->calendarService,
            ]
        );
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Exception
     */
    public function newAction()
    {
        $project = null;

        if (null !== $this->params('project')) {
            $project = $this->projectService->findProjectById($this->params('project'));

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
                    $this->projectService->updateEntity($projectCalendar);
                }

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
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

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function editAction()
    {
        $calendar = $this->calendarService->findCalendarById((int) $this->params('id'));

        if (null === $calendar) {
            return $this->notFoundAction();
        }

        $data = array_merge(
            [
                'calendar_entity_calendar' => [
                    'image' => null !== $calendar->getImage() ? $calendar->getImage()->getId() : null
                ]
            ],
            $this->getRequest()->getPost()->toArray()
        );

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

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
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
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
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

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function selectAttendeesAction()
    {
        $calendar = $this->calendarService->findCalendarById((int) $this->params('id'));

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

            $this->flashMessenger()->setNamespace('success')
                ->addMessage(
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

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function setRolesAction()
    {
        $calendar = $this->calendarService->findCalendarById((int) $this->params('id'));

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
                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
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

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function calendarAction()
    {
        $calendar = $this->calendarService->findCalendarById((int) $this->params('id'));

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

            /*
             * Document uploaded
             */

            return $this->redirect()->toRoute(
                'zfcadmin/calendar/calendar',
                [
                    'id' => $calendar->getId(),
                ]
            );
        }

        return new ViewModel(
            [
                'calendarService' => $this->calendarService,
                'contactService'  => $this->contactService,
                'actionService'   => $this->actionService,
                'calendar'        => $calendar,
                'form'            => $form,
            ]
        );
    }
}
