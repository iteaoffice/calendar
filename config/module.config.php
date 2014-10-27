<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2014 ITEA Office (http://itea3.org]
 */
use Calendar\Acl\Assertion\Calendar as CalendarAssertion;
use Calendar\Acl\Assertion\Contact as ContactAssertion;
use Calendar\Acl\Assertion\Document as DocumentAssertion;
use Calendar\Controller\ControllerInitializer;
use Calendar\Service\CalendarService;
use Calendar\Service\FormService;
use Calendar\Service\ServiceInitializer;

$config = [
    'controllers'     => [
        'initializers' => [
            ControllerInitializer::class
        ],
        'invokables'   => [
            'calendar-index'     => 'Calendar\Controller\CalendarController',
            'calendar-community' => 'Calendar\Controller\CalendarCommunityController',
            'calendar-manager'   => 'Calendar\Controller\CalendarManagerController',
            'calendar-document'  => 'Calendar\Controller\CalendarDocumentController',
        ],
    ],
    'service_manager' => [
        'initializers' => [
            ServiceInitializer::class
        ],
        'factories'    => [
            'calendar_navigation_service' => 'Calendar\Navigation\Factory\CalendarNavigationServiceFactory',
        ],
        'invokables'   => [
            CalendarAssertion::class        => CalendarAssertion::class,
            ContactAssertion::class         => ContactAssertion::class,
            DocumentAssertion::class        => DocumentAssertion::class,
            CalendarService::class          => CalendarService::class,
            FormService::class              => FormService::class,
            'calendar_calendar_form_filter' => 'Calendar\Form\FilterCreateObject',
        ]
    ],
    'view_manager'    => [
        'template_map' => include __DIR__ . '/../template_map.php', ], 'view_helpers' => [ 'invokables' => [ 'calendarDocumentLink' => 'Calendar\View\Helper\DocumentLink', 'calendarPaginationLink' => 'Calendar\View\Helper\PaginationLink' ] ], 'doctrine' => [ 'driver' => [ 'calendar_annotation_driver' => [ 'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver', 'paths' => [ __DIR__ . '/../src/Calendar/Entity/' ] ], 'orm_default' => [ 'drivers' => [ 'Calendar\Entity' => 'calendar_annotation_driver', ] ] ], 'eventmanager' => [ 'orm_default' => [ 'subscribers' => [ 'Gedmo\Timestampable\TimestampableListener', 'Gedmo\Sluggable\SluggableListener', ] ], ], ] ];
$configFiles = [
    __DIR__ . '/module.config.routes.php',
    __DIR__ . '/module.config.navigation.php',
    __DIR__ . '/module.config.authorize.php',
];
foreach ($configFiles as $configFile) {
    $config = Zend\Stdlib\ArrayUtils::merge($config, include $configFile);
}
return $config;
