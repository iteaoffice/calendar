<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

use Calendar\Entity;
use Calendar\Form;

return array(
    'factories'  => array(
        'calendar_navigation_service'     => 'Calendar\Navigation\Factory\CalendarNavigationServiceFactory',
        'calendar_acl_assertion_calendar' => function ($sm) {
            return new \Calendar\Acl\Assertion\Calendar($sm);
        },
        'calendar_calendar_form'          => function ($sm) {
            return new Form\CreateObject($sm, new Entity\Calendar());
        },
    ),
    'invokables' => array(
        'calendar_calendar_service'     => 'Calendar\Service\CalendarService',
        'calendar_form_service'         => 'Calendar\Service\FormService',
        'calendar_calendar_form_filter' => 'Calendar\Form\FilterCreateObject',
    )
);
