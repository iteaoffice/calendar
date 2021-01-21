<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

return [
    'modules'                 => [
        'DoctrineModule',
        'DoctrineORMModule',
        'BjyAuthorize',
        'Admin',
        'Publication',
        'Content',
        'Deeplink',
        'Calendar',
        'News',
        'General',
        'Program',
        'Organisation',
        'Affiliation',
        'Invoice',
        'Event',
        'Press',
        'Mailing',
        'Project',
        'Contact',
    ],
    'module_listener_options' => [
        'config_glob_paths' => [
            __DIR__ . '/autoload/{,*.}{global,testing,local}.php',
        ],
        'module_paths'      => [
            './../module',
            './vendor',
        ],
    ],
    'service_manager'         => [
        'use_defaults' => true,
        'factories'    => [],
    ],
];
