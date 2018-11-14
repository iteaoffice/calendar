<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
return [
    'navigation' => [
        'community2' => [
            // And finally, here is where we define our page hierarchy
            'calendar' => [
                'label' => _("txt-community-calendar"),
                'order' => 50,
                'route' => 'community/calendar/overview',
                'pages' => [
                    'community-calendar' => [
                        'label' => _("txt-community-calendar"),
                        'route' => 'community/calendar/overview',
                        'pages' => [
                            'view-calendar' => [
                                'route'   => 'community/calendar/calendar',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => Calendar\Entity\Calendar::class,
                                    ],
                                    'invokables' => [
                                        Calendar\Navigation\Invokable\CalendarLabel::class,
                                    ],
                                ],
                                'pages'   => [
                                    'send-message'     => [
                                        'label'   => _("txt-nav-calendar-send-messages"),
                                        'route'   => 'community/calendar/send-message',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Calendar\Entity\Calendar::class,
                                            ],
                                        ],
                                    ],
                                    'select-attendees' => [
                                        'label'   => _("txt-nav-calendar-select-attendees"),
                                        'route'   => 'community/calendar/select-attendees',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Calendar\Entity\Calendar::class,
                                            ],
                                        ],
                                    ],
                                    'document'         => [
                                        'route'   => 'community/calendar/document/document',
                                        'visible' => false,
                                        'params'  => [
                                            'entities'   => [
                                                'id' => Calendar\Entity\Document::class,
                                            ],
                                            'invokables' => [
                                                Calendar\Navigation\Invokable\DocumentLabel::class,
                                            ],
                                        ],
                                        'pages'   => [
                                            'edit-document' => [
                                                'label'   => _("txt-edit-calendar-document"),
                                                'route'   => 'community/calendar/document/edit',
                                                'visible' => false,
                                                'params'  => [
                                                    'entities' => [
                                                        'id' => Calendar\Entity\Document::class,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'review-calendar'    => [
                        'label' => _("txt-review-calendar"),
                        'route' => 'community/calendar/review-calendar',
                    ],
                    'contact'            => [
                        'label' => _("txt-review-invitations"),
                        'route' => 'community/calendar/contact',
                    ],
                ],
            ],
        ],
        'admin'     => [
            // And finally, here is where we define our page hierarchy
            'calendar' => [
                'label' => _("txt-calendar-admin"),
                'order' => 60,
                'route' => 'zfcadmin/calendar',
                'pages' => [
                    'calendar'          => [
                        'label' => _("txt-calendar"),
                        'route' => 'zfcadmin/calendar/overview',
                        'pages' => [
                            'view-calendar' => [
                                'route'   => 'zfcadmin/calendar/calendar',
                                'visible' => false,
                                'params'  => [
                                    'entities'   => [
                                        'id' => Calendar\Entity\Calendar::class,
                                    ],
                                    'invokables' => [
                                        Calendar\Navigation\Invokable\CalendarLabel::class,
                                    ],
                                ],
                                'pages'   => [
                                    'edit-calendar'    => [
                                        'label'   => _("txt-edit-calendar"),
                                        'route'   => 'zfcadmin/calendar/edit',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Calendar\Entity\Calendar::class,
                                            ],
                                        ],
                                    ],
                                    'select-attendees' => [
                                        'label'   => _("txt-nav-calendar-select-attendees"),
                                        'route'   => 'zfcadmin/calendar/select-attendees',
                                        'visible' => false,
                                        'params'  => [
                                            'entities' => [
                                                'id' => Calendar\Entity\Calendar::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'new-calendar-item' => [
                        'label' => _("txt-add-calendar-item"),
                        'route' => 'zfcadmin/calendar/new',
                    ],
                ],
            ],
        ],
    ],
];


