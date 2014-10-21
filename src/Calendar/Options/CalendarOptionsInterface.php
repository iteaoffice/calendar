<?php
/**
 * ARTEMIS-IA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Options
 * @author      Andre Hebben <andre.hebben@artemis-ia.eu>
 * @copyright   Copyright (c) 2007-2014 ARTEMIS-IA Office (http://artemis-ia.eu)
 */
namespace Calendar\Options;

/**
 * Interface CalendarOptionsInterface
 * @package Calendar\Options
 */
interface CalendarOptionsInterface
{
    /**
     * Sets wether the review invitations should be enabled on the homepage of the community
     * @param $communityCalendarContactEnabled
     * @return boolean
     */
    public function setCommunityCalendarContactEnabled($communityCalendarContactEnabled);

    /**
     * Enable the calendar contacts
     * @return boolean
     */
    public function getCommunityCalendarContactEnabled();
}
