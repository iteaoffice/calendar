<?php

use BjyAuthorize\Guard\Route;
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
            Route::class => [
                ['route' => 'assets/calendar-type-color-css', 'roles' => []],
                [
                    'route' => 'community/calendar/overview',
                    'roles' => ['user'],
                ],
                [
                    'route'     => 'community/calendar/calendar',
                    'roles'     => [],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/review-calendar',
                    'roles'     => [],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/download-review-calendar',
                    'roles'     => [],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/select-attendees',
                    'roles'     => [],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/send-message',
                    'roles'     => [],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/download-binder',
                    'roles'     => [],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/presence-list',
                    'roles'     => [],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/signature-list',
                    'roles'     => [],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'json/calendar/update-status',
                    'roles'     => [],
                    'assertion' => ContactAssertion::class,
                ],
                [
                    'route' => 'community/calendar/contact',
                    'roles' => [],
                ],
                [
                    'route'     => 'community/calendar/document/document',
                    'roles'     => [],
                    'assertion' => DocumentAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/document/download',
                    'roles'     => [],
                    'assertion' => DocumentAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/document/edit',
                    'roles'     => [],
                    'assertion' => DocumentAssertion::class,
                ],
                ['route' => 'zfcadmin/calendar/overview', 'roles' => ['office'],],
                ['route' => 'zfcadmin/calendar/edit', 'roles' => ['office'],],
                ['route' => 'zfcadmin/calendar/calendar', 'roles' => ['office'],],
                ['route' => 'zfcadmin/calendar/new', 'roles' => ['office'],],
                ['route' => 'zfcadmin/calendar/add-contact', 'roles' => ['office'],],
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
                ['route' => 'zfcadmin/calendar/type/edit', 'roles' => ['office'],],
                ['route' => 'zfcadmin/calendar/type/view', 'roles' => ['office'],],
                ['route' => 'zfcadmin/calendar/type/new', 'roles' => ['office'],],
                ['route' => 'zfcadmin/calendar/type/list', 'roles' => ['office'],],
            ],
        ],
    ],
];
