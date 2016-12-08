<?php
/**
 * Calendar Options
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
$options = [
    /**
     * Indicate here if a project has versions
     */
    'default_year'                       => date('Y'),
    'community_calendar_contact_enabled' => true,
    'calendar_contact_template'          => __DIR__ . '/../../../../styles/itea/template/pdf/itea-template.pdf',
    'review_calendar_template'           => __DIR__
                                            . '/../../../../styles/itea/template/pdf/review-calendar-template.pdf',
    'calendar_past_template'             => 'calendar/partial/list/calendar-past',
    'calendar_upcoming_template'         => 'calendar/partial/list/calendar-upcoming',
];
/**
 * You do not need to edit below this line
 */
return [
    'calendar_option' => $options,
];
