<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
$config = array(
    'controllers'     => array(
        'invokables' => array(
            'calendar-index'     => 'Calendar\Controller\CalendarController',
            'calendar-community' => 'Calendar\Controller\CalendarCommunityController',
            'calendar-manager'   => 'Calendar\Controller\CalendarManagerController',
            'calendar-document'  => 'Calendar\Controller\CalendarDocumentController',
        ),
    ),
    'view_manager'    => array(
        'template_map' => include __DIR__ . '/../template_map.php',
    ),
    'service_manager' => array(
        'invokables' => array(
            'calendar_calendar_service'     => 'Calendar\Service\CalendarService',
            'calendar_form_service'         => 'Calendar\Service\FormService',
            'calendar_calendar_form_filter' => 'Calendar\Form\FilterCreateObject',

        )
    ),
    'doctrine'        => array(
        'driver'       => array(
            'calendar_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => array(
                    __DIR__ . '/../src/Calendar/Entity/'
                )
            ),
            'orm_default'                => array(
                'drivers' => array(
                    'Calendar\Entity' => 'calendar_annotation_driver',
                )
            )
        ),
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                    'Gedmo\Timestampable\TimestampableListener',
                    'Gedmo\Sluggable\SluggableListener',
                )
            ),
        ),
    )
);

$configFiles = array(
    __DIR__ . '/module.config.routes.php',
    __DIR__ . '/module.config.navigation.php',
    __DIR__ . '/module.config.authorize.php',
);

foreach ($configFiles as $configFile) {
    $config = Zend\Stdlib\ArrayUtils::merge($config, include $configFile);
}

return $config;
