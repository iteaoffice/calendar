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
}
