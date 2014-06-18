<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Application
 * @package     Navigation
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Navigation\Service;

use Calendar\Service\CalendarService;
use Project\Service\ProjectService;
use Zend\I18n\View\Helper\Translate;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Navigation\Navigation;

/**
 * Factory for the Community admin navigation
 *
 * @package    Application
 * @subpackage Navigation\Service
 */
class CalendarNavigationService
{
    /**
     * @var RouteMatch
     */
    protected $routeMatch;
    /**
     * @var Translate
     */
    protected $translator;
    /**
     * @var CalendarService;
     */
    protected $calendarService;
    /**
     * @var ProjectService;
     */
    protected $projectService;
    /**
     * @var Navigation
     */
    protected $navigation;
    /**
     * @var TreeRouteStack
     */
    protected $router;

    /**
     * Add the dedicated pages to the navigation
     */
    public function update()
    {
        if (!is_null($this->getRouteMatch()) &&
            strtolower($this->getRouteMatch()->getParam('namespace')) === 'calendar'
        ) {
            if (strpos($this->getRouteMatch()->getMatchedRouteName(), 'community') !== false) {
                $this->updateCommunityNavigation();
            }
            if (strpos($this->getRouteMatch()->getMatchedRouteName(), 'zfcadmin') !== false) {
                $this->updateAdminNavigation();
            }
            if (!is_null($this->getRouteMatch()->getParam('id'))) {
                $this->getCalendarService()->setCalendarId($this->getRouteMatch()->getParam('id'));
            }
        }
    }

    /**
     * @return RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }

    /**
     * @param RouteMatch $routeMatch
     *
     * @return CalendarNavigationService
     */
    public function setRouteMatch($routeMatch)
    {
        $this->routeMatch = $routeMatch;

        return $this;
    }

    /**
     * Update the navigation for the community pages
     *
     * @return bool
     */
    public function updateCommunityNavigation()
    {
        $communityCalendar = $this->getNavigation()->findOneBy('route', 'community/calendar');
        if ($this->getCalendarService()->isEmpty()) {
            return false;
        }
        if ($this->getRouteMatch()->getMatchedRouteName() === 'community/calendar/calendar') {
            if (!is_null($this->getCalendarService()->getCalendar()->getProjectCalendar())) {
                $this->projectService->setProject(
                    $this->getCalendarService()->getCalendar()->getProjectCalendar()->getProject()
                );
                $communityCalendar->addPage(
                    array(
                        'label' => $this->translate("txt-review-calendar"),
                        'route' => 'community/calendar/review-calendar'
                    )
                );
                $pages['calendar']['pages']['calendar']['pages']['project'] = array(
                    'label'  => $this->projectService->parseFullname(),
                    'route'  => 'community/project/project',
                    'params' => array(
                        'docRef' => $this->projectService->getProject()->getDocRef()
                    )
                );
                $pages['calendar']['pages']['calendar']['pages']['project']['pages']['calendar'] = array(
                    'label'  => sprintf(
                        $this->translate("txt-calendar-item-%s-at-%s"),
                        $this->getCalendarService()->getCalendar()->getCalendar(),
                        $this->getCalendarService()->getCalendar()->getLocation()
                    ),
                    'route'  => 'community/calendar/calendar',
                    'active' => true,
                    'params' => array(
                        'id' => $this->getCalendarService()->getCalendar()->getId()
                    )
                );
            }
            if (is_null($this->getCalendarService()->getCalendar()->getProjectCalendar())) {
                $pages['calendar']['pages']['calendar'] = array(
                    'label' => $this->translate("txt-calendar"),
                    'route' => 'community/calendar/overview',
                );
                $pages['calendar']['pages']['calendar']['pages']['item'] = array(
                    'label'  => sprintf(
                        $this->translate("txt-calendar-item-%s-at-%s"),
                        $this->getCalendarService()->getCalendar()->getCalendar(),
                        $this->getCalendarService()->getCalendar()->getLocation()
                    ),
                    'route'  => 'community/calendar/calendar',
                    'active' => true,
                    'params' => array(
                        'id' => $this->getCalendarService()->getCalendar()->getId()
                    )
                );
            }
        }

        return true;
    }

    /**
     * @return Navigation
     */
    public function getNavigation()
    {
        return $this->navigation;
    }

    /**
     * @param Navigation $navigation
     *
     * @return CalendarNavigationService
     */
    public function setNavigation($navigation)
    {
        $this->navigation = $navigation;

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
     * @param CalendarService $calendarService
     */
    public function setCalendarService($calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function translate($string)
    {
        return $this->getTranslator()->__invoke($string);
    }

    /**
     * @return Translate
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param Translate $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     *
     */
    public function updateAdminNavigation()
    {
        $calendarManager = $this->getNavigation()->findOneBy('route', 'zfcadmin/calendar-manager');
        if ($this->getCalendarService()->isEmpty()) {
            return false;
        }
        switch ($this->getRouteMatch()->getMatchedRouteName()) {
            case 'zfcadmin/calendar-manager/calendar':
                if (!is_null($this->getCalendarService()->getCalendar()->getProjectCalendar())) {
                    $this->getProjectService()->setProject(
                        $this->getCalendarService()->getCalendar()->getProjectCalendar()->getProject()
                    );
                    $calendarManager->addPage(
                        array(
                            'label'  => $this->projectService->parseFullname(),
                            'route'  => 'community/project/project',
                            'params' => array(
                                'docRef' => $this->projectService->getProject()->getDocRef()
                            ),
                            'router' => $this->getRouter(),
                            'pages'  => array(
                                'project_calendar' => array(
                                    'label'  => sprintf(
                                        $this->translate("txt-calendar-item-%s-at-%s"),
                                        $this->getCalendarService()->getCalendar()->getCalendar(),
                                        $this->getCalendarService()->getCalendar()->getLocation()
                                    ),
                                    'route'  => 'zfcadmin/calendar-manager/calendar',
                                    'active' => true,
                                    'router' => $this->getRouter(),
                                    'params' => array(
                                        'id' => $this->getCalendarService()->getCalendar()->getId()
                                    )
                                )
                            )
                        )
                    );
                } else {
                    $calendarManager->addPage(
                        array(
                            'label'  => sprintf(
                                $this->translate("txt-calendar-item-%s-at-%s"),
                                $this->getCalendarService()->getCalendar()->getCalendar(),
                                $this->getCalendarService()->getCalendar()->getLocation()
                            ),
                            'route'  => 'zfcadmin/calendar-manager/calendar',
                            'router' => $this->getRouter(),
                            'active' => true,
                            'params' => array(
                                'id' => $this->getCalendarService()->getCalendar()->getId()
                            )
                        )
                    );
                }
                break;
            case 'zfcadmin/calendar-manager/edit':
                if (!is_null($this->getCalendarService()->getCalendar()->getProjectCalendar())) {
                    $this->getProjectService()->setProject(
                        $this->getCalendarService()->getCalendar()->getProjectCalendar()->getProject()
                    );
                    $calendarManager->addPage(
                        array(
                            'label'  => $this->projectService->parseFullname(),
                            'route'  => 'community/project/project',
                            'router' => $this->getRouter(),
                            'params' => array(
                                'docRef' => $this->projectService->getProject()->getDocRef()
                            ),
                            'pages'  => array(
                                'project_calendar' => array(
                                    'label'  => sprintf(
                                        $this->translate("txt-calendar-item-%s-at-%s"),
                                        $this->getCalendarService()->getCalendar()->getCalendar(),
                                        $this->getCalendarService()->getCalendar()->getLocation()
                                    ),
                                    'route'  => 'zfcadmin/calendar-manager/calendar',
                                    'router' => $this->getRouter(),
                                    'params' => array(
                                        'id' => $this->getCalendarService()->getCalendar()->getId()
                                    ),
                                    'pages'  => array(
                                        'edit_project_calendar' => array(
                                            'label'  => sprintf(
                                                $this->translate("txt-edit-calendar-item-%s-at-%s"),
                                                $this->getCalendarService()->getCalendar()->getCalendar(),
                                                $this->getCalendarService()->getCalendar()->getLocation()
                                            ),
                                            'route'  => 'zfcadmin/calendar-manager/edit',
                                            'active' => true,
                                            'router' => $this->getRouter(),
                                            'params' => array(
                                                'id' => $this->getCalendarService()->getCalendar()->getId()
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    );
                } else {
                    $calendarManager->addPage(
                        array(
                            'label'  => sprintf(
                                $this->translate("txt-calendar-item-%s-at-%s"),
                                $this->getCalendarService()->getCalendar()->getCalendar(),
                                $this->getCalendarService()->getCalendar()->getLocation()
                            ),
                            'route'  => 'zfcadmin/calendar-manager/calendar',
                            'router' => $this->getRouter(),
                            'params' => array(
                                'id' => $this->getCalendarService()->getCalendar()->getId()
                            ),
                            'pages'  => array(
                                'edit_calendar_item' =>
                                    array(
                                        'label'  => sprintf(
                                            $this->translate("txt-edit-calendar-item-%s-at-%s"),
                                            $this->getCalendarService()->getCalendar()->getCalendar(),
                                            $this->getCalendarService()->getCalendar()->getLocation()
                                        ),
                                        'route'  => 'zfcadmin/calendar-manager/edit',
                                        'active' => true,
                                        'router' => $this->getRouter(),
                                        'params' => array(
                                            'id' => $this->getCalendarService()->getCalendar()->getId()
                                        )
                                    )
                            )
                        )
                    );
                }
                break;
        }

        return true;
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
     */
    public function setProjectService($projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * @return TreeRouteStack
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param TreeRouteStack $router
     *
     * @return CalendarNavigationService
     */
    public function setRouter($router)
    {
        $this->router = $router;

        return $this;
    }
}
