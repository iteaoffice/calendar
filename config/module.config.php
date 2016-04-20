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
use Calendar\Options;
use Calendar\Service;
use Calendar\View;

$config = [
    'controllers'        => [
        'factories' => [
            Controller\CalendarCommunityController::class => Controller\Factory\ControllerFactory::class,
            Controller\CalendarController::class          => Controller\Factory\ControllerFactory::class,
            Controller\CalendarDocumentController::class  => Controller\Factory\ControllerFactory::class,
            Controller\CalendarManagerController::class   => Controller\Factory\ControllerFactory::class,
        ]
    ],
    'controller_plugins' => [
        'aliases'   => [
            'renderCalendarContactList' => Controller\Plugin\RenderCalendarContactList::class,
            'renderReviewCalendar'      => Controller\Plugin\RenderReviewCalendar::class,
        ],
        'factories' => [
            Controller\Plugin\RenderCalendarContactList::class => Controller\Factory\PluginFactory::class,
            Controller\Plugin\RenderReviewCalendar::class      => Controller\Factory\PluginFactory::class,
        ]
    ],
    'service_manager'    => [
        'factories' => [
            Service\CalendarService::class => Factory\CalendarServiceFactory::class,
            Service\FormService::class     => Factory\FormServiceFactory::class,
            Options\ModuleOptions::class   => Factory\ModuleOptionsFactory::class,
            Acl\Assertion\Calendar::class  => Acl\Factory\AssertionFactory::class,
            Acl\Assertion\Contact::class   => Acl\Factory\AssertionFactory::class,
            Acl\Assertion\Document::class  => Acl\Factory\AssertionFactory::class,
        ],
    ],
    'view_manager'       => [
        'template_map' => include __DIR__ . '/../template_map.php',
    ],
    'view_helpers'       => [
        'aliases'   => [
            'calendarDocumentLink' => View\Helper\DocumentLink::class,
            'calendarLink'         => View\Helper\CalendarLink::class,
            'calendarHandler'      => View\Helper\CalendarHandler::class,
        ],
        'factories' => [
            View\Helper\DocumentLink::class    => View\Factory\ViewHelperFactory::class,
            View\Helper\CalendarLink::class    => View\Factory\ViewHelperFactory::class,
            View\Helper\CalendarHandler::class => View\Factory\ViewHelperFactory::class,
        ]
    ],
    'doctrine'           => [
        'driver'       => [
            'calendar_annotation_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => [__DIR__ . '/../src/Entity/']
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
