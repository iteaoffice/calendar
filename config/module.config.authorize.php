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
    'bjyauthorize' => array(
        // resource providers provide a list of resources that will be tracked
        // in the ACL. like roles, they can be hierarchical
        'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                'calendar' => array(),
            ),
        ),
        /* rules can be specified here with the format:
         * array(roles (array) , resource, [privilege (array|string), assertion])
         * assertions will be loaded using the service manager and must implement
         * Zend\Acl\Assertion\AssertionInterface.
         * *if you use assertions, define them using the service manager!*
         */
        'rule_providers'     => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    // allow guests and users (and admins, through inheritance)
                    // the "wear" privilege on the resource "pants"d
                    array(array('public'), 'calendar', array('listings', 'view')),
                    array(array('office'), 'calendar', array('edit', 'new', 'delete'))
                ),
                // Don't mix allow/deny rules if you are using role inheritance.
                // There are some weird bugs.
                'deny'  => array( // ...
                ),
            ),
        ),
        /* Currently, only controller and route guards exist
         */
        'guards'             => array(
            /* If this guard is specified here (i.e. it is enabled), it will block
             * access to all routes unless they are specified here.
             */
            'BjyAuthorize\Guard\Route' => array(
                array('route' => 'assets/calendar-type-color-css', 'roles' => array()),
                array('route' => 'community/calendar/overview', 'roles' => array('office')),
                array(
                    'route'     => 'community/calendar/calendar',
                    'roles'     => array('office'),
                    'assertion' =>
                        'calendar_acl_assertion_calendar'
                ),
                array('route' => 'community/calendar/review-calendar', 'roles' => array('office')),
                array('route' => 'community/calendar/document/document', 'roles' => array('office')),
                array('route' => 'community/calendar/document/download', 'roles' => array('office')),
                array('route' => 'zfcadmin/calendar-manager/overview', 'roles' => array('office')),
                array('route' => 'zfcadmin/calendar-manager/edit', 'roles' => array('office')),
                array('route' => 'zfcadmin/calendar-manager/calendar', 'roles' => array('office')),
                array('route' => 'zfcadmin/calendar-manager/new', 'roles' => array('office')),
                array('route' => 'zfcadmin/calendar-manager/document/document', 'roles' => array('office')),
                array('route' => 'zfcadmin/calendar-manager/document/edit', 'roles' => array('office')),
            ),
        ),
    ),
);
