<?php

/**
 * ARTEMIS-IA Office copyright message placeholder.
 *
 * @category    Calendar
 *
 * @author      Andre Hebben <andre.hebben@artemis-ia.eu>
 * @copyright   Copyright (c) 2007-2014 ARTEMIS-IA Office (http://artemis-ia.eu)
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
     * @return bool
     */
    public function getCommunityCalendarContactEnabled();

    /**
     * @param $calendarContactTemplate
     *
     * @return CalendarOptionsInterface
     */
    public function setCalendarContactTemplate($calendarContactTemplate);

    /**
     * @return bool
     */
    public function getCalendarContactTemplate();

    /**
     * @param $reviewCalendarTemplate
     *
     * @return CalendarOptionsInterface
     */
    public function setReviewCalendarTemplate($reviewCalendarTemplate);

    /**
     * @return bool
     */
    public function getReviewCalendarTemplate();

    /**
     * Returns the default year
     * @return int
     */
    public function getDefaultYear();

    /**
     * @param $defaultYear
     * @return $this
     */
    public function setDefaultYear($defaultYear);

    /**
     * @param  string $calendarUpcomingTemplate
     * @return $this
     */
    public function setCalendarUpcomingTemplate($calendarUpcomingTemplate);

    /**
     * Return template to use for Upcoming Event rendering
     * @return string
     */
    public function getCalendarUpcomingTemplate();

    /**
     * @param $calendarPastTemplate
     * @return $this
     */
    public function setCalendarPastTemplate($calendarPastTemplate);

    /**
     * Return template to use for Past Event rendering
     * @return string
     */
    public function getCalendarPastTemplate();
}
