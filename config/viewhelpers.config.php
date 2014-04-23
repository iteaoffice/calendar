<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Application
 * @package     Config
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar;

use Calendar\View\Helper;

return array(
    'factories'  => array(
        'calendarLink'         => function ($sm) {
            return new Helper\CalendarLink($sm);
        },
        'calendarHandler'      => function ($sm) {
            return new Helper\CalendarHandler($sm);
        },
        'calendarServiceProxy' => function ($sm) {
            return new Helper\CalendarServiceProxy($sm);
        },
    ),
    'invokables' => array(
        'calendarDocumentLink'   => 'Calendar\View\Helper\DocumentLink',
        'calendarPaginationLink' => 'Calendar\View\Helper\PaginationLink'
    )
);
