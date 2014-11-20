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
    'community_calendar_contact_enabled' => true,
    'calendar_contact_template'          => __DIR__ . '/../../../../styles/itea/template/pdf/nda-template.pdf',
    'review_calendar_template'           => __DIR__ . '/../../../../styles/itea/template/pdf/review-calendar-template.pdf',
];
/**
 * You do not need to edit below this line
 */
return [
    'calendar_option' => $options,
];
