<?php

return [
    'modules'                 => [
        'DoctrineModule',
        'DoctrineORMModule',
        'BjyAuthorize',
        'ZfcUser',
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
