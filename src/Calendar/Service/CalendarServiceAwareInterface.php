<?php
/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category  Calendar
 * @package   Service
 * @author    Johan van der Heide <info@japaveh.nl>
 * @copyright 2004-2014 Japaveh Webdesign
 * @license   http://solodb.net/license.txt proprietary
 * @link      http://solodb.net
 */
namespace Calendar\Service;

/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category  Calendar
 * @package   Service
 * @author    Johan van der Heide <info@japaveh.nl>
 * @copyright 2004-2014 Japaveh Webdesign
 * @license   http://solodb.net/license.txt proprietary
 * @link      http://solodb.net
 */
interface CalendarServiceAwareInterface
{
    /**
     * The calendar service
     *
     * @param CalendarService $calendarService
     */
    public function setCalendarService(CalendarService $calendarService);

    /**
     * Get calendar service
     *
     * @return CalendarService
     */
    public function getCalendarService();
}
