<?php

use Calendar\Acl;
use Calendar\Controller;
use Calendar\Factory;
use Calendar\InputFilter;
use Calendar\Navigation;
use Calendar\Options;
use Calendar\Search;
use Calendar\Service;
use Calendar\View;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\Timestampable\TimestampableListener;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\Stdlib;

/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

$config = [
    'controllers'        => [
        'factories' => [
            Controller\CommunityController::class => ConfigAbstractFactory::class,
            Controller\CalendarController::class  => ConfigAbstractFactory::class,
            Controller\TypeController::class      => ConfigAbstractFactory::class,
            Controller\DocumentController::class  => ConfigAbstractFactory::class,
            Controller\JsonController::class      => ConfigAbstractFactory::class,
            Controller\ManagerController::class   => ConfigAbstractFactory::class,
        ],
    ],
    'controller_plugins' => [
        'aliases'   => [
            'renderCalendarContactList' => Controller\Plugin\RenderCalendarContactList::class,
            'renderReviewCalendar'      => Controller\Plugin\RenderReviewCalendar::class,
            'getFilter'                 => Controller\Plugin\GetFilter::class,
        ],
        'factories' => [
            Controller\Plugin\RenderCalendarContactList::class => ConfigAbstractFactory::class,
            Controller\Plugin\RenderReviewCalendar::class      => ConfigAbstractFactory::class,
            Controller\Plugin\GetFilter::class                 => Factory\InvokableFactory::class,
        ],
    ],
    'service_manager'    => [
        'factories'  => [
            Service\CalendarService::class              => ConfigAbstractFactory::class,
            Service\FormService::class                  => Factory\FormServiceFactory::class,
            Options\ModuleOptions::class                => Factory\ModuleOptionsFactory::class,
            Acl\Assertion\Calendar::class               => Factory\InvokableFactory::class,
            Acl\Assertion\Contact::class                => Factory\InvokableFactory::class,
            Acl\Assertion\Document::class               => Factory\InvokableFactory::class,
            Search\Service\CalendarSearchService::class => ConfigAbstractFactory::class,
            Navigation\Invokable\CalendarLabel::class   => Factory\InvokableFactory::class,
            Navigation\Invokable\DocumentLabel::class   => Factory\InvokableFactory::class,
        ],
        'invokables' => [
            InputFilter\CalendarFilter::class => InputFilter\CalendarFilter::class,
            InputFilter\DocumentFilter::class => InputFilter\DocumentFilter::class,
        ]
    ],
    'view_manager'       => [
        'template_map' => include __DIR__ . '/../template_map.php',
    ],
    'view_helpers'       => [
        'aliases'   => [
            'calendarDocumentLink' => View\Helper\DocumentLink::class,
            'calendarTypeLink'     => View\Helper\TypeLink::class,
            'calendarLink'         => View\Helper\CalendarLink::class,
        ],
        'factories' => [
            View\Helper\DocumentLink::class     => View\Factory\ViewHelperFactory::class,
            View\Helper\CalendarLink::class     => View\Factory\ViewHelperFactory::class,
            View\Helper\TypeLink::class         => View\Factory\ViewHelperFactory::class,
            View\Handler\CalendarHandler::class => ConfigAbstractFactory::class,
        ],
    ],
    'doctrine'           => [
        'driver'       => [
            'calendar_annotation_driver' => [
                'class' => AnnotationDriver::class,
                'paths' => [__DIR__ . '/../src/Entity/'],
            ],
            'orm_default'                => [
                'drivers' => [
                    'Calendar\Entity' => 'calendar_annotation_driver',
                ],
            ],
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    TimestampableListener::class,
                    SluggableListener::class,
                ],
            ],
        ],
    ],
];
foreach (Stdlib\Glob::glob(__DIR__ . '/module.config.{,*}.php', Stdlib\Glob::GLOB_BRACE) as $file) {
    $config = Stdlib\ArrayUtils::merge($config, include $file);
}

return $config;
