<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Controller
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Controller;

use Calendar\Service\CalendarService;
use Calendar\Service\CalendarServiceAwareInterface;
use Calendar\Service\FormService;
use Calendar\Service\FormServiceAwareInterface;
use Contact\Service\ContactService;
use Contact\Service\ContactServiceAwareInterface;
use General\Service\GeneralService;
use General\Service\GeneralServiceAwareInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 */
abstract class CalendarAbstractController extends AbstractActionController implements
    FormServiceAwareInterface,
    ServiceLocatorAwareInterface,
    ContactServiceAwareInterface,
    CalendarServiceAwareInterface,
    GeneralServiceAwareInterface
{
    /**
     * @var FormService
     */
    protected $formService;
    /**
     * @var ContactService
     */
    protected $contactService;
    /**
     * @var CalendarService;
     */
    protected $calendarService;
    /**
     * @var GeneralService
     */
    protected $generalService;
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

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
}
