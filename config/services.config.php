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
    'factories' => array(
        'calendar_calendar_form' => function ($sm) {
            return new Form\CreateObject($sm, new Entity\Calendar());
        },
    ),
);
