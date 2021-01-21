<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

return [
    'service_manager' => [
    ],
    'contact-config'  =>
        [
            // cache options have to be compatible with Laminas\Cache\StorageFactory::factory
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
