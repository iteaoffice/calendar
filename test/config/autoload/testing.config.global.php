<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Project
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
return [
    'service_manager' => [
    ],
    'contact-config'  =>
        [
            // cache options have to be compatible with Zend\Cache\StorageFactory::factory
            'cache_options' => [
                'adapter' => [
                    'name' => 'memory',
                ],
                'plugins' => [
                    'serializer',
                ],
            ],
            'cache_key'     => 'contact-cache-' . (defined("ITEAOFFICE_HOST") ? ITEAOFFICE_HOST : 'test'),
        ],
];
