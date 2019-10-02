<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Calendar
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Options;

/**
 * Interface CalendarOptionsInterface.
 */
interface CalendarOptionsInterface
{
    /**
     * @param $calendarContactTemplate
     *
     * @return CalendarOptionsInterface
     */
    public function setCalendarContactTemplate($calendarContactTemplate);

    /**
     * @return boolean
     */
    public function getCalendarContactTemplate();

    /**
     * @param $reviewCalendarTemplate
     *
     * @return CalendarOptionsInterface
     */
    public function setReviewCalendarTemplate($reviewCalendarTemplate);

    /**
     * @return boolean
     */
    public function getReviewCalendarTemplate();

    /**
     * Returns the default year
     *
     * @return int
     */
    public function getDefaultYear();

    /**
     * @param $defaultYear
     *
     * @return $this
     */
    public function setDefaultYear($defaultYear);
}
