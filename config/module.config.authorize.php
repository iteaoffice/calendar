<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
use Admin\Entity\Access;
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
                    'roles' => [strtolower(Access::ACCESS_USER)],
                ],
                [
                    'route'     => 'community/calendar/calendar',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/review-calendar',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/download-review-calendar',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/select-attendees',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/send-message',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/download-binder',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/presence-list',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/update-status',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => ContactAssertion::class,
                ],
                [
                    'route' => 'community/calendar/contact',
                    'roles' => [strtolower(Access::ACCESS_USER)],
                ],
                [
                    'route'     => 'community/calendar/document/document',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => DocumentAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/document/download',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => DocumentAssertion::class,
                ],
                [
                    'route'     => 'community/calendar/document/edit',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => DocumentAssertion::class,
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/overview',
                    'roles' => [Access::ACCESS_OFFICE],
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/edit',
                    'roles' => [Access::ACCESS_OFFICE],
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/calendar',
                    'roles' => [Access::ACCESS_OFFICE],
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/new',
                    'roles' => [Access::ACCESS_OFFICE],
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/select-attendees',
                    'roles' => [Access::ACCESS_OFFICE],
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/update-role',
                    'roles' => [Access::ACCESS_OFFICE],
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/get-roles',
                    'roles' => [Access::ACCESS_OFFICE],
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/document/document',
                    'roles' => [Access::ACCESS_OFFICE],
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/document/edit',
                    'roles' => [Access::ACCESS_OFFICE],
                ],
            ],
        ],
    ],
];
