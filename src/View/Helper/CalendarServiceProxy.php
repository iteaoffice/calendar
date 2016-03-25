<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category   Calendar
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Calendar\View\Helper;

use Calendar\Entity\Calendar;
use Calendar\Service\CalendarService;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\HelperPluginManager;

/**
 * Class CalendarServiceProxy.
 */
class CalendarServiceProxy extends AbstractHelper
{
    /**
     * @var HelperPluginManager
     */
    protected $serviceLocator;

    /**
     * @param Calendar $calendar
     *
     * @return CalendarService
     */
    public function __invoke(Calendar $calendar)
    {
        $calendarService = $this->serviceLocator->getServiceLocator()->get(CalendarService::class);

        return $calendarService->setCalendar($calendar);
    }

    /**
     * Get the service locator.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Set the service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AbstractHelper
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }
}
