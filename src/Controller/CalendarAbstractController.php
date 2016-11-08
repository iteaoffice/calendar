<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (https://itea3.org)
 */

namespace Calendar\Controller;

use BjyAuthorize\Controller\Plugin\IsAllowed;
use Calendar\Controller\Plugin\RenderCalendarContactList;
use Calendar\Controller\Plugin\RenderReviewCalendar;
use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use Calendar\Service\FormService;
use Contact\Service\ContactService;
use Contact\Service\SelectionService;
use Doctrine\ORM\EntityManager;
use General\Service\EmailService;
use General\Service\GeneralService;
use Project\Service\ProjectService;
use Project\Service\WorkpackageService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;

/**
 * @method ZfcUserAuthentication zfcUserAuthentication()
 * @method FlashMessenger flashMessenger()
 * @method IsAllowed isAllowed($resource, $action)
 * @method RenderCalendarContactList renderCalendarContactList()
 * @method RenderReviewCalendar renderReviewCalendar()
 */
abstract class CalendarAbstractController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var TwigRenderer
     */
    protected $renderer;
    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;
    /**
     * @var FormService
     */
    protected $formService;
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
     * @var CalendarService;
     */
    protected $calendarService;
    /**
     * @var GeneralService
     */
    protected $generalService;
    /**
     * @var EmailService
     */
    protected $emailService;
    /**
     * @var SelectionService
     */
    protected $selectionService;
    /**
     * @var HelperPluginManager
     */
    protected $viewHelperManager;

    /**
     * @return \Calendar\Service\FormService
     */
    public function getFormService()
    {
        return $this->formService;
    }

    /**
     * @param $formService
     *
     * @return CalendarAbstractController
     */
    public function setFormService($formService)
    {
        $this->formService = $formService;

        return $this;
    }

    /**
     * @return ContactService
     */
    public function getContactService()
    {
        return $this->contactService;
    }

    /**
     * @param $contactService
     *
     * @return CalendarAbstractController
     */
    public function setContactService(ContactService $contactService)
    {
        $this->contactService = $contactService;

        return $this;
    }

    /**
     * @return CalendarService
     */
    public function getCalendarService()
    {
        return $this->calendarService;
    }

    /**
     * @param $calendarService
     *
     * @return CalendarAbstractController
     */
    public function setCalendarService(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;

        return $this;
    }

    /**
     * @return GeneralService
     */
    public function getGeneralService()
    {
        return $this->generalService;
    }

    /**
     * @param $generalService
     *
     * @return CalendarAbstractController
     */
    public function setGeneralService(GeneralService $generalService)
    {
        $this->generalService = $generalService;

        return $this;
    }

    /**
     * @return WorkpackageService
     */
    public function getWorkpackageService()
    {
        return $this->workpackageService;
    }

    /**
     * @param WorkpackageService $workpackageService
     *
     * @return CalendarAbstractController
     */
    public function setWorkpackageService(WorkpackageService $workpackageService)
    {
        $this->workpackageService = $workpackageService;

        return $this;
    }

    /**
     * @return ProjectService
     */
    public function getProjectService()
    {
        return $this->projectService;
    }

    /**
     * @param ProjectService $projectService
     *
     * @return CalendarAbstractController
     */
    public function setProjectService(ProjectService $projectService)
    {
        $this->projectService = $projectService;

        return $this;
    }

    /**
     * @return EmailService
     */
    public function getEmailService()
    {
        return $this->emailService;
    }

    /**
     * @param EmailService $emailService
     *
     * @return CalendarAbstractController
     */
    public function setEmailService(EmailService $emailService)
    {
        $this->emailService = $emailService;

        return $this;
    }


    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CalendarAbstractController
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * @return SelectionService
     */
    public function getSelectionService()
    {
        return $this->selectionService;
    }

    /**
     * @param SelectionService $selectionService
     *
     * @return CalendarAbstractController
     */
    public function setSelectionService(SelectionService $selectionService)
    {
        $this->selectionService = $selectionService;

        return $this;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return CalendarAbstractController
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions()
    {
        return $this->moduleOptions;
    }

    /**
     * @param ModuleOptions $moduleOptions
     *
     * @return CalendarAbstractController
     */
    public function setModuleOptions($moduleOptions)
    {
        $this->moduleOptions = $moduleOptions;

        return $this;
    }

    /**
     * @return TwigRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @param TwigRenderer $renderer
     *
     * @return CalendarAbstractController
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Proxy for the flash messenger helper to have the string translated earlier.
     *
     * @param $string
     *
     * @return string
     */
    protected function translate($string)
    {
        /*
         * @var Translate
         */
        $translate = $this->getViewHelperManager()->get('translate');

        return $translate($string);
    }

    /**
     * @return HelperPluginManager
     */
    public function getViewHelperManager(): HelperPluginManager
    {
        return $this->viewHelperManager;
    }

    /**
     * @param HelperPluginManager $viewHelperManager
     *
     * @return CalendarAbstractController
     */
    public function setViewHelperManager(HelperPluginManager $viewHelperManager): CalendarAbstractController
    {
        $this->viewHelperManager = $viewHelperManager;

        return $this;
    }
}
