<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
return array(
    'navigation' => [
        'community' => [
            // And finally, here is where we define our page hierarchy
            'calendar' => [
                'label' => _("txt-calendar"),
                'route' => 'community/calendar/overview',
                'pages' => [
                    'calendars'        => [
                        'label' => _("txt-calendar"),
                        'route' => 'community/calendar/overview',
                    ],
                    'review-calendar'  => [
                        'label' => _("txt-review-calendar"),
                        'route' => 'community/calendar/review-calendar',
                    ],
                    'contact' => [
                        'label' => _("txt-calendar-invitations"),
                        'route' => 'community/calendar/contact',
                    ],
                ],
            ],
        ],
        'admin'     => [
            // And finally, here is where we define our page hierarchy
            'calendar' => [
                'label'    => _("txt-calendar-admin"),
                'route'    => 'zfcadmin/calendar-manager',
                'resource' => 'zfcadmin',
                'pages'    => [
                    'calendar'          => [
                        'label' => _("txt-calendar"),
                        'route' => 'zfcadmin/calendar-manager/overview',
                    ],
                    'new-calendar-item' => [
                        'label' => _("txt-add-calendar-item"),
                        'route' => 'zfcadmin/calendar-manager/new',
                    ],
                ],
            ],
        ],
    ],
);
