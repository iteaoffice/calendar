<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Project
 * @package     Navigation
 * @subpackage  Service
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Navigation\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\Mvc\Router\Http\RouteMatch;

use Calendar\Service\CalendarService;
use Project\Service\ProjectService;

/**
 * Factory for the Project admin navigation
 *
 * @package    Calendar
 * @subpackage Navigation\Service
 */
class CalendarNavigationFactory extends DefaultNavigationFactory
{
    /**
     * @var RouteMatch
     */
    protected $routeMatch;
    /**
     * @var CalendarService;
     */
    protected $calendarService;
    /**
     * @var ProjectService;
     */
    protected $projectService;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array                   $pages
     *
     * @return array
     */
    public function getExtraPages(ServiceLocatorInterface $serviceLocator, array $pages)
    {
        $application           = $serviceLocator->get('Application');
        $this->routeMatch      = $application->getMvcEvent()->getRouteMatch();
        $router                = $application->getMvcEvent()->getRouter();
        $this->calendarService = $serviceLocator->get('calendar_calendar_service');
        $this->projectService  = $serviceLocator->get('project_project_service');

        if ($this->routeMatch->getMatchedRouteName() === 'community/calendar/calendar') {

            $this->calendarService->setCalendarId($this->routeMatch->getParam('id'));

            if (is_null($this->calendarService->getCalendar()->getId())) {
                return false;
            }

            if (!is_null($this->calendarService->getCalendar()->getProjectCalendar())) {
                $this->projectService->setProject($this->calendarService->getCalendar()->getProjectCalendar()->getProject());

                $pages['project']['pages']['projects']['pages']['project'] = array(
                    'label'      => $this->projectService->parseFullname(),
                    'route'      => 'community/project/project',
                    'routeMatch' => $this->routeMatch,
                    'router'     => $router,
                    'params'     => array(
                        'docRef' => $this->projectService->getProject()->getDocRef()
                    )
                );

                $pages['project']['pages']['projects']['pages']['project']['pages']['calendar'] = array(
                    'label'      => sprintf(_("txt-calendar-item-%s-at-%s"),
                        $this->calendarService->getCalendar()->getCalendar(),
                        $this->calendarService->getCalendar()->getLocation()),
                    'route'      => 'community/calendar/calendar',
                    'routeMatch' => $this->routeMatch,
                    'active'     => true,
                    'router'     => $router,
                    'params'     => array(
                        'id' => $this->calendarService->getCalendar()->getId()
                    )
                );
            }

            if (is_null($this->calendarService->getCalendar()->getProjectCalendar())) {

                $pages['calendar']['pages']['calendar'] = array(
                    'label'      => sprintf(_("txt-calendar-item-%s-at-%s"),
                        $this->calendarService->getCalendar()->getCalendar(),
                        $this->calendarService->getCalendar()->getLocation()),
                    'route'      => 'community/calendar/calendar',
                    'routeMatch' => $this->routeMatch,
                    'active'     => true,
                    'router'     => $router,
                    'params'     => array(
                        'id' => $this->calendarService->getCalendar()->getId()
                    )
                );
            }
        }

        if ($this->routeMatch->getMatchedRouteName() === 'zfcadmin/calendar-manager/calendar') {

            $this->calendarService->setCalendarId($this->routeMatch->getParam('id'));

            if (is_null($this->calendarService->getCalendar()->getId())) {
                return false;
            }

            if (!is_null($this->calendarService->getCalendar()->getProjectCalendar())) {
                $this->projectService->setProject($this->calendarService->getCalendar()->getProjectCalendar()->getProject());

                $pages['project']['pages']['projects']['pages']['project'] = array(
                    'label'      => $this->projectService->parseFullname(),
                    'route'      => 'community/project/project',
                    'routeMatch' => $this->routeMatch,
                    'router'     => $router,
                    'params'     => array(
                        'docRef' => $this->projectService->getProject()->getDocRef()
                    )
                );

                $pages['project']['pages']['projects']['pages']['project']['pages']['calendar'] = array(
                    'label'      => sprintf(_("txt-calendar-item-%s-at-%s"),
                        $this->calendarService->getCalendar()->getCalendar(),
                        $this->calendarService->getCalendar()->getLocation()),
                    'route'      => 'zfcadmin/calendar-manager/calendar',
                    'routeMatch' => $this->routeMatch,
                    'active'     => true,
                    'router'     => $router,
                    'params'     => array(
                        'id' => $this->calendarService->getCalendar()->getId()
                    )
                );
            }

            if (is_null($this->calendarService->getCalendar()->getProjectCalendar())) {

                $pages['calendar']['pages']['calendar'] = array(
                    'label'      => sprintf(_("txt-calendar-item-%s-at-%s"),
                        $this->calendarService->getCalendar()->getCalendar(),
                        $this->calendarService->getCalendar()->getLocation()),
                    'route'      => 'zfcadmin/calendar-manager/calendar',
                    'routeMatch' => $this->routeMatch,
                    'active'     => true,
                    'router'     => $router,
                    'params'     => array(
                        'id' => $this->calendarService->getCalendar()->getId()
                    )
                );
            }
        }

        return $pages;
    }
}
