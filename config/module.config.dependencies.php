<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

declare(strict_types=1);

namespace Calendar;

use Calendar\Controller\Plugin;
use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use ZfcTwig\View\TwigRenderer;

return [
    ConfigAbstractFactory::class => [
        // Controller plugins
        Plugin\RenderCalendarContactList::class => [
            TwigRenderer::class,
            ModuleOptions::class,
            CalendarService::class
        ],
        Plugin\RenderReviewCalendar::class      => [
            TwigRenderer::class,
            ModuleOptions::class
        ],
    ]
];