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
use Calendar\Service\CalendarService;
use Calendar\Entity\Calendar;

/**
 * Class CalendarServiceProxy
 *
 * @package Contact\View\Helper
 */
class CalendarServiceProxy extends AbstractHelper
{
    /**
     * @var CalendarService
     */
    protected $calendarService;

    /**
     * @param HelperPluginManager $helperPluginManager
     */
    public function __construct(HelperPluginManager $helperPluginManager)
    {
        $this->calendarService = clone $helperPluginManager->getServiceLocator()->get('calendar_calendar_service');
    }

    /**
     * @param Calendar $calendar
     *
     * @return CalendarService
     */
    public function __invoke(Calendar $calendar)
    {
        return $this->calendarService->setCalendar($calendar);
    }
}
