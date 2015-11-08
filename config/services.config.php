<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */
use Calendar\Entity;
use Calendar\Form;

return array(
    'factories' => [
        'calendar_calendar_form' => function ($sm) {
            return new Form\CreateObject($sm, new Entity\Calendar());
        },
    ],
);
