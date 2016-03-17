<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2015 ITEA Office (https://itea3.org]
 */
use Admin\Entity\Access;
use Calendar\Acl\Assertion\Calendar as CalendarAssertion;
use Calendar\Acl\Assertion\Contact as ContactAssertion;
use Calendar\Acl\Assertion\Document as DocumentAssertion;

return [
    'bjyauthorize' => [
        // resource providers provide a list of resources that will be tracked
        // in the ACL. like roles, they can be hierarchical
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'calendar' => [],
            ],
        ],
        /* Currently, only controller and route guards exist
         */
        'guards'             => [
            /* If this guard is specified here (i.e. it is enabled], it will block
             * access to all routes unless they are specified here.
             */
            'BjyAuthorize\Guard\Route' => [
                ['route' => 'assets/calendar-type-color-css', 'roles' => []],
                [
                    'route' => 'community/calendar/overview',
                    'roles' => [strtolower(Access::ACCESS_USER)]
                ],
                [
                    'route'     => 'community/calendar/calendar',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class
                ],
                [
                    'route'     => 'community/calendar/review-calendar',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class
                ],
                [
                    'route'     => 'community/calendar/download-review-calendar',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class
                ],
                [
                    'route'     => 'community/calendar/select-attendees',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class
                ],
                [
                    'route'     => 'community/calendar/send-message',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class
                ],
                [
                    'route'     => 'community/calendar/download-binder',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class
                ],
                [
                    'route'     => 'community/calendar/presence-list',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class
                ],
                [
                    'route'     => 'community/calendar/update-status',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => ContactAssertion::class
                ],
                [
                    'route'     => 'community/calendar/contact',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => CalendarAssertion::class
                ],
                [
                    'route'     => 'community/calendar/document/document',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => DocumentAssertion::class
                ],
                [
                    'route'     => 'community/calendar/document/download',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => DocumentAssertion::class
                ],
                [
                    'route'     => 'community/calendar/document/edit',
                    'roles'     => [strtolower(Access::ACCESS_USER)],
                    'assertion' => DocumentAssertion::class
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/overview',
                    'roles' => [strtolower(Access::ACCESS_OFFICE)]
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/edit',
                    'roles' => [strtolower(Access::ACCESS_OFFICE)]
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/calendar',
                    'roles' => [strtolower(Access::ACCESS_OFFICE)]
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/new',
                    'roles' => [strtolower(Access::ACCESS_OFFICE)]
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/select-attendees',
                    'roles' => [strtolower(Access::ACCESS_OFFICE)]
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/update-role',
                    'roles' => [strtolower(Access::ACCESS_OFFICE)]
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/get-roles',
                    'roles' => [strtolower(Access::ACCESS_OFFICE)]
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/document/document',
                    'roles' => [strtolower(Access::ACCESS_OFFICE)]
                ],
                [
                    'route' => 'zfcadmin/calendar-manager/document/edit',
                    'roles' => [strtolower(Access::ACCESS_OFFICE)]
                ],
            ],
        ],
    ],
];
