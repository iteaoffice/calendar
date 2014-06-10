<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2014 ITEA Office (http://itea3.org]
 */
namespace Calendar;

return [
    'router' => [
        'routes' => [
            'assets'    => [
                'type'          => 'Literal',
                'priority'      => 999,
                'options'       => [
                    'route' => '/assets/' . DEBRANOVA_HOST,
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'calendar-type-color-css' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => "/css/calendar-type-color.css",
                            'defaults' => [
                                //Explicitly add the controller here as the assets are collected
                                'controller' => 'calendar-index',
                                'action'     => 'calendar-type-color-css',
                            ],
                        ],
                    ],
                ]
            ],
            'community' => [
                'child_routes' => [
                    'calendar' => [
                        'type'          => 'Segment',
                        'priority'      => 1000,
                        'options'       => [
                            'route'    => '/calendar',
                            'defaults' => [
                                'namespace'  => __NAMESPACE__,
                                'controller' => 'calendar-community',
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'overview'        => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/overview[/:which][/page-:page].html',
                                    'defaults' => [
                                        'action' => 'overview',
                                    ],
                                ],
                            ],
                            'calendar'        => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/view/[:id].html',
                                    'defaults' => [
                                        'action'    => 'calendar',
                                        'privilege' => 'view',
                                    ],
                                ],
                            ],
                            'review-calendar' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/review-calendar.html',
                                    'defaults' => [
                                        'action' => 'review-calendar',
                                    ],
                                ],
                            ],
                            'document'        => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/document',
                                    'defaults' => [
                                        'controller' => 'calendar-document',
                                        'action'     => 'document',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes'  => [
                                    'document' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/[:id].html',
                                            'defaults' => [
                                                'action' => 'document',
                                            ],
                                        ],
                                    ],
                                    'download' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/download/[:id]/[:filename].[:ext]',
                                            'defaults' => [
                                                'action' => 'download',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'zfcadmin'  => [
                'child_routes' => [
                    'calendar-manager' => [
                        'type'          => 'Segment',
                        'priority'      => 1000,
                        'options'       => [
                            'route'    => '/calendar',
                            'defaults' => [
                                'namespace'  => __NAMESPACE__,
                                'controller' => 'calendar-manager',
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'overview' => [
                                'type'     => 'Segment',
                                'priority' => 1000,
                                'options'  => [
                                    'route'    => '/overview[/:which][/page-:page].html',
                                    'defaults' => [
                                        'action' => 'overview',
                                    ],
                                ],
                            ],
                            'calendar' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/view/[:id].html',
                                    'defaults' => [
                                        'action'    => 'calendar',
                                        'privilege' => 'view',
                                    ],
                                ],
                            ],
                            'new'      => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/new.html',
                                    'defaults' => [
                                        'action' => 'new',
                                    ],
                                ],
                            ],
                            'edit'     => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/edit/[:id].html',
                                    'defaults' => [
                                        'action' => 'edit',
                                    ],
                                ],
                            ],
                            'document' => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/document',
                                    'defaults' => [
                                        'controller' => 'calendar-document',
                                        'action'     => 'document',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes'  => [
                                    'document' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/[:id].html',
                                            'defaults' => [
                                                'action' => 'document',
                                            ]
                                        ]
                                    ],
                                    'edit'     => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/edit/[:id].html',
                                            'defaults' => [
                                                'action' => 'edit',
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
