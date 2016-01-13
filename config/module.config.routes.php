<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c] 2004-2015 ITEA Office (https://itea3.org]
 */
namespace Calendar;

use Calendar\Controller;

return [
    'router' => [
        'routes' => [
            'assets'    => [
                'type'          => 'Literal',
                'priority'      => 999,
                'options'       => [
                    'route' => '/assets/' . (defined("DEBRANOVA_HOST")
                            ? DEBRANOVA_HOST : 'test'),
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'calendar-type-color-css' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => "/css/calendar-type-color.css",
                            'defaults' => [
                                //Explicitly add the controller here as the assets are collected
                                'controller' => Controller\CalendarController::class,
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
                                'controller' => Controller\CalendarCommunityController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'overview'                 => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/overview[/which-:which][/page-:page].html',
                                    'defaults' => [
                                        'action' => 'overview',
                                    ],
                                ],
                            ],
                            'calendar'                 => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/view/[:id].html',
                                    'defaults' => [
                                        'action'    => 'calendar',
                                        'privilege' => 'view-community',
                                    ],
                                ],
                            ],
                            'select-attendees'         => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/select-attendees/[:id].html',
                                    'defaults' => [
                                        'action'    => 'select-attendees',
                                        'privilege' => 'select-attendees',
                                    ],
                                ],
                            ],
                            'send-message'             => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/send-message/[:id].html',
                                    'defaults' => [
                                        'action'    => 'send-message',
                                        'privilege' => 'send-message',
                                    ],
                                ],
                            ],
                            'presence-list'            => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/presence-list/[:id].pdf',
                                    'defaults' => [
                                        'action'    => 'presence-list',
                                        'privilege' => 'presence-list',
                                    ],
                                ],
                            ],
                            'review-calendar'          => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/review-calendar.html',
                                    'defaults' => [
                                        'action'    => 'review-calendar',
                                        'privilege' => 'review-calendar',
                                    ],
                                ],
                            ],
                            'download-review-calendar' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/download/review-calendar.pdf',
                                    'defaults' => [
                                        'action'    => 'download-review-calendar',
                                        'privilege' => 'review-calendar',
                                    ],
                                ],
                            ],
                            'download-binder'         => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/download-binder/[:id].html',
                                    'defaults' => [
                                        'action'    => 'download-binder',
                                        'privilege' => 'download-binder',
                                    ],
                                ],
                            ],
                            'contact'                  => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/contact.html',
                                    'defaults' => [
                                        'action'    => 'contact',
                                        'privilege' => 'contact',
                                    ],
                                ],
                            ],
                            'update-status'            => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/update-status.html',
                                    'defaults' => [
                                        'action'    => 'update-status',
                                        'privilege' => 'update-status',
                                    ],
                                ],
                            ],
                            'document'                 => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/document',
                                    'defaults' => [
                                        'controller' => Controller\CalendarDocumentController::class,
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
                                                'action'    => 'document',
                                                'privilege' => 'document-community',
                                            ],
                                        ],
                                    ],
                                    'edit'     => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/edit/[:id].html',
                                            'defaults' => [
                                                'action'    => 'edit',
                                                'privilege' => 'edit-community',
                                            ],
                                        ],
                                    ],
                                    'download' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/download/[:id]/[:filename]',
                                            'defaults' => [
                                                'action'    => 'download',
                                                'privilege' => 'download',
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
                                'controller' => Controller\CalendarManagerController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'overview'         => [
                                'type'     => 'Segment',
                                'priority' => 1000,
                                'options'  => [
                                    'route'    => '/overview[/:which][/page-:page].html',
                                    'defaults' => [
                                        'action' => 'overview',
                                    ],
                                ],
                            ],
                            'calendar'         => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/view/[:id].html',
                                    'defaults' => [
                                        'action'    => 'calendar',
                                        'privilege' => 'view',
                                    ],
                                ],
                            ],
                            'new'              => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/new[/project-:project].html',
                                    'defaults' => [
                                        'action' => 'new',
                                    ],
                                ],
                            ],
                            'edit'             => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/edit/[:id].html',
                                    'defaults' => [
                                        'action' => 'edit',
                                    ],
                                ],
                            ],
                            'select-attendees' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/select-attendees/[:id].html',
                                    'defaults' => [
                                        'action' => 'select-attendees',
                                    ],
                                ],
                            ],
                            'update-role'      => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/update-role.json',
                                    'defaults' => [
                                        'action' => 'update-role',
                                    ],
                                ],
                            ],
                            'get-roles'        => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/get-roles.json',
                                    'defaults' => [
                                        'action' => 'get-roles',
                                    ],
                                ],
                            ],
                            'document'         => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/document',
                                    'defaults' => [
                                        'controller' => Controller\CalendarDocumentController::class,
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
