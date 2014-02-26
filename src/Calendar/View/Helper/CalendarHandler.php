<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     View
 * @subpackage  Helper
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Calendar\View\Helper;

use Zend\View\HelperPluginManager;
use Zend\View\Helper\AbstractHelper;


use ZfcTwig\View\TwigRenderer;
use Calendar\Service\CalendarService;
use Calendar\Entity\Calendar;

use Content\Entity\Handler;

/**
 * Class CountryHandler
 * @package Country\View\Helper
 */
class CalendarHandler extends AbstractHelper
{
    /**
     * @var CalendarService
     */
    protected $calendarService;
    /**
     * @var Handler
     */
    protected $handler;
    /**
     * @var Calendar;
     */
    protected $calendar;
    /**
     * @var int
     */
    protected $limit = 5;
    /**
     * @var TwigRenderer;
     */
    protected $zfcTwigRenderer;

    /**
     * @param HelperPluginManager $helperPluginManager
     */
    public function __construct(HelperPluginManager $helperPluginManager)
    {
        $this->calendarService = $helperPluginManager->getServiceLocator()->get('calendar_calendar_service');
        $this->calendarService = $helperPluginManager->getServiceLocator()->get('calendar_calendar_service');
        $this->routeMatch      = $helperPluginManager->getServiceLocator()
            ->get('application')
            ->getMvcEvent()
            ->getRouteMatch();
        /**
         * Load the TwigRenderer directly form the plugin manager to avoid a fallback to the standard PhpRenderer
         */
        $this->zfcTwigRenderer = $helperPluginManager->getServiceLocator()->get('ZfcTwigRenderer');
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render()
    {

        $translate = $this->getView()->plugin('translate');

        switch ($this->getHandler()->getHandler()) {

            case 'calendar_item':

                return $this->parseCalendarItem();
                break;

            case 'calendar':

                return $this->parseCalendar($this->getLimit());
                break;

            case 'calendar_small':


                return $this->parseCalendarSmall($this->getLimit());
                break;

            default:
                return sprintf("No handler available for <code>%s</code> in class <code>%s</code>",
                    $this->getHandler()->getHandler(),
                    __CLASS__);
        }
    }

    /**
     * Create a list of all countries which are active (have projects)
     *
     * @return string
     */
    public function parseCalendarSmall()
    {
        $calendarItems = $this->calendarService
            ->findCalendarItems(CalendarService::WHICH_UPCOMING)
            ->setMaxResults($this->getLimit())
            ->getResult();

        return $this->zfcTwigRenderer->render('calendar/partial/list/calendar-small',
            array('calendarItems' => $calendarItems));
    }

    /**
     * Create a list of all countries which are active (have projects)
     *
     * @return string
     */
    public function parseCalendar()
    {
        $calendarItems = $this->calendarService
            ->findCalendarItems(CalendarService::WHICH_UPCOMING)
            ->setMaxResults($this->getLimit())
            ->getResult();

        return $this->zfcTwigRenderer->render('calendar/partial/list/calendar',
            array('calendarItems' => $calendarItems));
    }

    /**
     * Show the details of 1 calendar item
     *
     * @return string
     */
    public function parseCalendarItem()
    {
        return $this->zfcTwigRenderer->render('calendar/partial/entity/calendar',
            array('calendar' => $this->getCalendar()));
    }


    /**
     * @param \Content\Entity\Handler $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return \Content\Entity\Handler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param $id
     *
     * @return Calendar
     */
    public function setCalendarId($id)
    {
        $this->setCalendar($this->calendarService->findEntityById('Calendar', $id));

        return $this->getCalendar();
    }

    /**
     * @param \Calendar\Entity\Calendar $calendar
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * @return \Calendar\Entity\Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
}
