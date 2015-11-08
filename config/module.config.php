<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2015 ITEA Office (https://itea3.org]
 */
use Calendar\Acl\Assertion;
use Calendar\Controller;
use Calendar\Service;
use Calendar\View\Helper;

$config = [
    'controllers'     => [
        'initializers' => [
            Controller\ControllerInitializer::class
        ],
        'invokables'   => [
            Controller\CalendarController::class          => Controller\CalendarController::class,
            Controller\CalendarCommunityController::class => Controller\CalendarCommunityController::class,
            Controller\CalendarManagerController::class   => Controller\CalendarManagerController::class,
            Controller\CalendarDocumentController::class  => Controller\CalendarDocumentController::class,
        ],
    ],
    'service_manager' => [
        'initializers' => [
            Service\ServiceInitializer::class
        ],
        'factories'    => [
            'calendar_module_options'     => 'Calendar\Factory\OptionServiceFactory',
            'calendar_navigation_service' => 'Calendar\Navigation\Factory\CalendarNavigationServiceFactory',
        ],
        'invokables'   => [
            Assertion\Calendar::class       => Assertion\Calendar::class,
            Assertion\Contact::class        => Assertion\Contact::class,
            Assertion\Document::class       => Assertion\Document::class,
            Service\CalendarService::class  => Service\CalendarService::class,
            Service\FormService::class      => Service\FormService::class,
            'calendar_calendar_form_filter' => 'Calendar\Form\FilterCreateObject',
        ]
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
