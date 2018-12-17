<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

use Calendar\Acl\Assertion\Calendar as CalendarAssertion;
use Calendar\Acl\Assertion\Contact as ContactAssertion;
use Calendar\Acl\Assertion\Document as DocumentAssertion;

return [
    'bjyauthorize' => [
        /* Currently, only controller and route guards exist
         */
        'guards' => [
            /* If this guard is specified here (i.e. it is enabled], it will block
             * access to all routes unless they are specified here.
             */
            'BjyAuthorize\Guard\Route' => [
                ['route' => 'assets/calendar-type-color-css', 'roles' => []],
                [
                    'route' => 'community/calendar/overview',
                    'roles' => ['user'],
                ],
                [
                    'route'     => 'community/calendar/calendar',
                    'roles'     => ['user'],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/review-calendar',
                    'roles'     => ['user'],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/download-review-calendar',
                    'roles'     => ['user'],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/select-attendees',
                    'roles'     => ['user'],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/send-message',
                    'roles'     => ['user'],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/download-binder',
                    'roles'     => ['user'],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/presence-list',
                    'roles'     => ['user'],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/signature-list',
                    'roles'     => ['user'],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'json/calendar/update-status',
                    'roles'     => ['user'],
                    'assertion' => ContactAssertion::class,
                ],
                [
                    'route' => 'community/calendar/contact',
                    'roles' => ['user'],
                ],
                [
                    'route'     => 'community/calendar/document/document',
                    'roles'     => ['user'],
                    'assertion' => DocumentAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/document/download',
                    'roles'     => ['user'],
                    'assertion' => DocumentAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/document/edit',
                    'roles'     => ['user'],
                    'assertion' => DocumentAssertion::class,
                ],
                [
                    'route' => 'zfcadmin/calendar/overview',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/calendar/edit',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/calendar/calendar',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/calendar/new',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/calendar/select-attendees',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/calendar/json/update-role',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/calendar/json/get-roles',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/calendar/document/document',
                    'roles' => ['office'],
                ],
                [
                    'route' => 'zfcadmin/calendar/document/edit',
                    'roles' => ['office'],
                ],
            ],
        ],
    ],
];
