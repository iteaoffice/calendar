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
            'assets'    => array(
                'type'          => 'Literal',
                'priority'      => 999,
                'options'       => array(
                    'route' => '/assets/' . DEBRANOVA_HOST,
//                    'defaults' => array(
//                        'controller' => 'calendar-index',
//                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'calendar-type-color-css' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => "/css/calendar-type-color.css",
                            'defaults' => array(
                                //Explicitly add the controller here as the assets are collected
                                'controller' => 'calendar-index',
                                'action'     => 'calendar-type-color-css',
                            ),
                        ),
                    ),
                )
            ),
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
            'zfcadmin'  => array(
                'child_routes' => array(
                    'calendar-manager' => array(
                        'type'          => 'Segment',
                        'priority'      => 1000,
                        'options'       => array(
                            'route'    => '/calendar',
                            'defaults' => array(
                                'controller' => 'calendar-manager',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes'  => array(
                            'overview' => array(
                                'type'     => 'Segment',
                                'priority' => 1000,
                                'options'  => array(
                                    'route'    => '/overview[/:which][/page-:page].html',
                                    'defaults' => array(
                                        'action' => 'overview',
                                    ),
                                ),
                            ),
                            'calendar' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/view/[:id].html',
                                    'defaults' => array(
                                        'action'    => 'calendar',
                                        'privilege' => 'view',
                                    ),
                                ),
                            ),
                            'new'      => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/new.html',
                                    'defaults' => array(
                                        'action' => 'new',
                                    ),
                                ),
                            ),
                            'edit'     => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/edit/[:id].html',
                                    'defaults' => array(
                                        'action' => 'edit',
                                    ),
                                ),
                            ),
                            'document' => array(
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
                                    'edit'     => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/edit/[:id].html',
                                            'defaults' => array(
                                                'action' => 'edit',
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
