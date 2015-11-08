<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Calendar\Navigation\Factory;

use Calendar\Navigation\Service\CalendarNavigationService;
use Calendar\Service\CalendarService;
use Project\Service\ProjectService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 */
class CalendarNavigationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CalendarNavigationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $calendarNavigationService = new CalendarNavigationService();
        $calendarNavigationService->setTranslator($serviceLocator->get('viewhelpermanager')->get('translate'));
        $calendarNavigationService->setCalendarService($serviceLocator->get(CalendarService::class));
        $calendarNavigationService->setProjectService($serviceLocator->get(ProjectService::class));
        $application = $serviceLocator->get('application');
        $calendarNavigationService->setRouteMatch($application->getMvcEvent()->getRouteMatch());
        $calendarNavigationService->setRouter($application->getMvcEvent()->getRouter());
        $calendarNavigationService->setNavigation($serviceLocator->get('navigation'));

        return $calendarNavigationService;
    }
}
