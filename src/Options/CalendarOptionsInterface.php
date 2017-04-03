<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Calendar
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace Calendar\Options;

/**
 * Interface CalendarOptionsInterface.
 */
interface CalendarOptionsInterface
{
    /**
     * Sets whether the review invitations should be enabled on the homepage of the community.
     *
     * @param $communityCalendarContactEnabled
     *
     * @return CalendarOptionsInterface
     */
    public function setCommunityCalendarContactEnabled($communityCalendarContactEnabled);

    /**
     * Enable the calendar contacts.
     *
     * @return boolean
     */
    public function getCommunityCalendarContactEnabled();

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

    /**
     * @param  string $calendarUpcomingTemplate
     *
     * @return $this
     */
    public function setCalendarUpcomingTemplate($calendarUpcomingTemplate);

    /**
     * Return template to use for Upcoming Event rendering
     *
     * @return string
     */
    public function getCalendarUpcomingTemplate();

    /**
     * @param $calendarPastTemplate
     *
     * @return $this
     */
    public function setCalendarPastTemplate($calendarPastTemplate);

    /**
     * Return template to use for Past Event rendering
     *
     * @return string
     */
    public function getCalendarPastTemplate();
}
