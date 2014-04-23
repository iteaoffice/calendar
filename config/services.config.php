<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

use Calendar\Form;
use Calendar\Entity;

return array(
    'factories' => array(
        'calendar_acl_assertion_calendar' => function ($sm) {
            return new \Calendar\Acl\Assertion\Calendar($sm);
        },
        'calendar_calendar_form'          => function ($sm) {
            return new Form\CreateObject($sm, new Entity\Calendar());
        },
    ),
);
