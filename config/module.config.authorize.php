<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2014 ITEA Office (http://itea3.org]
 */
return [
    'bjyauthorize' => [
        // resource providers provide a list of resources that will be tracked
        // in the ACL. like roles, they can be hierarchical
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'calendar' => [],
            ],
        ],
        /* rules can be specified here with the format:
         * [roles (array] , resource, [privilege (array|string], assertion]]
         * assertions will be loaded using the service manager and must implement
         * Zend\Acl\Assertion\AssertionInterface.
         * *if you use assertions, define them using the service manager!*
         */
        'rule_providers'     => [
            'BjyAuthorize\Provider\Rule\Config' => [
                'allow' => [
                    // allow guests and users (and admins, through inheritance]
                    // the "wear" privilege on the resource "pants"d
                    [['public'], 'calendar', ['listings', 'view']],
                    [['office'], 'calendar', ['edit', 'new', 'delete']]
                ],
                // Don't mix allow/deny rules if you are using role inheritance.
                // There are some weird bugs.
                'deny'  => [ // ...
                ],
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
                ['route' => 'community/calendar/overview', 'roles' => ['office']],
                [
                    'route'     => 'community/calendar/calendar',
                    'roles'     => ['office'],
                    'assertion' => 'calendar_acl_assertion_calendar'
                ],
                ['route' => 'community/calendar/review-calendar', 'roles' => ['office']],
                ['route' => 'community/calendar/document/document', 'roles' => ['office']],
                ['route' => 'community/calendar/document/download', 'roles' => ['office']],
                ['route' => 'zfcadmin/calendar-manager/overview', 'roles' => ['office']],
                ['route' => 'zfcadmin/calendar-manager/edit', 'roles' => ['office']],
                ['route' => 'zfcadmin/calendar-manager/calendar', 'roles' => ['office']],
                ['route' => 'zfcadmin/calendar-manager/new', 'roles' => ['office']],
                ['route' => 'zfcadmin/calendar-manager/document/document', 'roles' => ['office']],
                ['route' => 'zfcadmin/calendar-manager/document/edit', 'roles' => ['office']],
            ],
        ],
    ],
];
