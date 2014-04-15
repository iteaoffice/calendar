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
    'navigation' => array(
        'community' => array(
            // And finally, here is where we define our page hierarchy
            'calendar' => array(
                'label' => _("txt-calendar"),
                'route' => 'community/calendar',
                'pages' => array(
                    'calendars'       => array(
                        'label' => _("txt-calendar"),
                        'route' => 'community/calendar/overview',
                    ),
                    'review-calendar' => array(
                        'label' => _("txt-review-calendar"),
                        'route' => 'community/calendar/review-calendar',
                    ),
                ),
            ),
        ),
        'admin'     => array(
            // And finally, here is where we define our page hierarchy
            'calendar' => array(
                'label'    => _("txt-calendar-admin"),
                'route'    => 'zfcadmin/calendar-manager',
                'resource' => 'zfcadmin',
                'pages'    => array(
                    'calendar'          => array(
                        'label' => _("txt-calendar"),
                        'route' => 'zfcadmin/calendar-manager/overview',
                    ),
                    'new-calendar-item' => array(
                        'label' => _("txt-add-calendar-item"),
                        'route' => 'zfcadmin/calendar-manager/new',
                    ),
                ),
            ),
        ),
    ),
);
