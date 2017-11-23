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
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
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
     * Proxy for the flash messenger helper to have the string translated earlier.
     *
     * @param $string
     *
     * @return string
     */
    protected function translate($string): string
    {
        /*
         * @var Translate
         */
        $translate = $this->getViewHelperManager()->get('translate');

        return $translate($string);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     * @return CalendarAbstractController
     */
    public function setEntityManager(EntityManager $entityManager): CalendarAbstractController
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * @return TwigRenderer
     */
    public function getRenderer(): TwigRenderer
    {
        return $this->renderer;
    }

    /**
     * @param TwigRenderer $renderer
     * @return CalendarAbstractController
     */
    public function setRenderer(TwigRenderer $renderer): CalendarAbstractController
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions(): ModuleOptions
    {
        return $this->moduleOptions;
    }

    /**
     * @param ModuleOptions $moduleOptions
     * @return CalendarAbstractController
     */
    public function setModuleOptions(ModuleOptions $moduleOptions): CalendarAbstractController
    {
        $this->moduleOptions = $moduleOptions;

        return $this;
    }

    /**
     * @return FormService
     */
    public function getFormService(): FormService
    {
        return $this->formService;
    }

    /**
     * @param FormService $formService
     * @return CalendarAbstractController
     */
    public function setFormService(FormService $formService): CalendarAbstractController
    {
        $this->formService = $formService;

        return $this;
    }

    /**
     * @return ContactService
     */
    public function getContactService(): ContactService
    {
        return $this->contactService;
    }

    /**
     * @param ContactService $contactService
     * @return CalendarAbstractController
     */
    public function setContactService(ContactService $contactService): CalendarAbstractController
    {
        $this->contactService = $contactService;

        return $this;
    }

    /**
     * @return ProjectService
     */
    public function getProjectService(): ProjectService
    {
        return $this->projectService;
    }

    /**
     * @param ProjectService $projectService
     * @return CalendarAbstractController
     */
    public function setProjectService(ProjectService $projectService): CalendarAbstractController
    {
        $this->projectService = $projectService;

        return $this;
    }

    /**
     * @return WorkpackageService
     */
    public function getWorkpackageService(): WorkpackageService
    {
        return $this->workpackageService;
    }

    /**
     * @param WorkpackageService $workpackageService
     * @return CalendarAbstractController
     */
    public function setWorkpackageService(WorkpackageService $workpackageService): CalendarAbstractController
    {
        $this->workpackageService = $workpackageService;

        return $this;
    }

    /**
     * @return CalendarService
     */
    public function getCalendarService(): CalendarService
    {
        return $this->calendarService;
    }

    /**
     * @param CalendarService $calendarService
     * @return CalendarAbstractController
     */
    public function setCalendarService(CalendarService $calendarService): CalendarAbstractController
    {
        $this->calendarService = $calendarService;

        return $this;
    }

    /**
     * @return GeneralService
     */
    public function getGeneralService(): GeneralService
    {
        return $this->generalService;
    }

    /**
     * @param GeneralService $generalService
     * @return CalendarAbstractController
     */
    public function setGeneralService(GeneralService $generalService): CalendarAbstractController
    {
        $this->generalService = $generalService;

        return $this;
    }

    /**
     * @return EmailService
     */
    public function getEmailService(): EmailService
    {
        return $this->emailService;
    }

    /**
     * @param EmailService $emailService
     * @return CalendarAbstractController
     */
    public function setEmailService(EmailService $emailService): CalendarAbstractController
    {
        $this->emailService = $emailService;

        return $this;
    }

    /**
     * @return SelectionService
     */
    public function getSelectionService(): SelectionService
    {
        return $this->selectionService;
    }

    /**
     * @param SelectionService $selectionService
     * @return CalendarAbstractController
     */
    public function setSelectionService(SelectionService $selectionService): CalendarAbstractController
    {
        $this->selectionService = $selectionService;

        return $this;
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
     * @return CalendarAbstractController
     */
    public function setViewHelperManager(HelperPluginManager $viewHelperManager): CalendarAbstractController
    {
        $this->viewHelperManager = $viewHelperManager;

        return $this;
    }
}
