<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Service
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Navigation\Factory;

use Calendar\Navigation\Service\CalendarNavigationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * NodeService
 *
 * this is a wrapper for node entity related services
 *
 */
class CalendarNavigationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return array|mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $calendarNavigationService = new CalendarNavigationService();
        $calendarNavigationService->setTranslator($serviceLocator->get('viewhelpermanager')->get('translate'));
        $calendarNavigationService->setCalendarService($serviceLocator->get('calendar_calendar_service'));
        $calendarNavigationService->setProjectService($serviceLocator->get('project_project_service'));

        $application = $serviceLocator->get('application');

        $calendarNavigationService->setRouteMatch($application->getMvcEvent()->getRouteMatch());
        $calendarNavigationService->setRouter($application->getMvcEvent()->getRouter());
        $calendarNavigationService->setNavigation($serviceLocator->get('navigation'));

        return $calendarNavigationService;
    }
}
