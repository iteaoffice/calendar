<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
return array(
    'router' => array(
        'routes' => array(
            'community' => array(
                'child_routes' => array(
                    'calendar' => array(
                        'type'          => 'Segment',
                        'priority'      => 1000,
                        'options'       => array(
                            'route'    => '/calendar',
                            'defaults' => array(
                                'controller' => 'calendar-community',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes'  => array(
                            'overview'        => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/overview[/:which][/page-:page].html',
                                    'defaults' => array(
                                        'action' => 'overview',
                                    ),
                                ),
                            ),
                            'calendar'        => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/view/[:id].html',
                                    'defaults' => array(
                                        'action'    => 'calendar',
                                        'privilege' => 'view',
                                    ),
                                ),
                            ),
                            'review-calendar' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/review-calendar.html',
                                    'defaults' => array(
                                        'action' => 'review-calendar',
                                    ),
                                ),
                            ),
                            'document'        => array(
                                'type'          => 'Segment',
                                'options'       => array(
                                    'route'    => '/document',
                                    'defaults' => array(
                                        'controller' => 'calendar-document',
                                        'action'     => 'document',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(
                                    'document' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/[:id].html',
                                            'defaults' => array(
                                                'action' => 'document',
                                            ),
                                        ),
                                    ),
                                    'download' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/download/[:id]/[:filename].[:ext]',
                                            'defaults' => array(
                                                'action' => 'download',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        )
    )
);
