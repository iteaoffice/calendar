<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2015 ITEA Office (https://itea3.org]
 */
use Calendar\Acl;
use Calendar\Controller;
use Calendar\Factory;
use Calendar\Navigation;
use Calendar\Options;
use Calendar\Service;
use Calendar\View\Helper;

$config = [
    'controllers'     => [
        'invokables'         => [
            //Controller\CalendarController::class         ,
            //Controller\CalendarCommunityController::class,
            //Controller\CalendarManagerController::class  ,
            //Controller\CalendarDocumentController::class ,
        ],
        'abstract_factories' => [
            Controller\Factory\ControllerInvokableAbstractFactory::class,
        ]
    ],
    'service_manager' => [
        'factories'          => [
            Service\CalendarService::class => Factory\CalendarServiceFactory::class,
            Service\FormService::class     => Factory\FormServiceFactory::class,
            Options\ModuleOptions::class   => Factory\ModuleOptionsFactory::class,
            //Acl\Assertion\Calendar::class,
            //Acl\Assertion\Contact::class,
            //Acl\Assertion\Document::class,
        ],
        'invokables'         => [
            'calendar_calendar_form_filter' => 'Calendar\Form\FilterCreateObject',
        ],
        'abstract_factories' => [
            Acl\Factory\AssertionInvokableAbstractFactory::class
        ],
    ],
    'view_manager'    => [
        'template_map' => include __DIR__ . '/../template_map.php',
    ],
    'view_helpers'    => [
        'invokables' => [
            'calendarDocumentLink' => Helper\DocumentLink::class,
        ]
    ],
    'doctrine'        => [
        'driver'       => [
            'calendar_annotation_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => [__DIR__ . '/../src/Calendar/Entity/']
            ],
            'orm_default'                => [
                'drivers' => [
                    'Calendar\Entity' => 'calendar_annotation_driver',
                ]
            ]
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    'Gedmo\Timestampable\TimestampableListener',
                    'Gedmo\Sluggable\SluggableListener',
                ]
            ],
        ],
    ]
];
$configFiles = [
    __DIR__ . '/module.config.routes.php',
    __DIR__ . '/module.config.navigation.php',
    __DIR__ . '/module.config.authorize.php',
    __DIR__ . '/module.option.calendar.php',
];
foreach ($configFiles as $configFile) {
    $config = Zend\Stdlib\ArrayUtils::merge($config, include $configFile);
}
return $config;
