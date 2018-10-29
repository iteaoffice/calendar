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

use Application\Service\AssertionService;
use Calendar\Options\ModuleOptions;
use Calendar\Search\Service\CalendarSearchService;
use Calendar\Service\CalendarService;
use Calendar\Service\FormService;
use Contact\Service\ContactService;
use Contact\Service\SelectionContactService;
use Doctrine\ORM\EntityManager;
use General\Service\EmailService;
use General\Service\GeneralService;
use Project\Service\ActionService;
use Project\Service\ProjectService;
use Project\Service\WorkpackageService;
use Zend\Authentication\AuthenticationService;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use ZfcTwig\View\TwigRenderer;

return [
    ConfigAbstractFactory::class => [
        //Controllers
        Controller\CalendarController::class               => [
            CalendarService::class,
            TwigRenderer::class
        ],
        Controller\CommunityController::class              => [
            CalendarService::class,
            GeneralService::class,
            ContactService::class,
            ProjectService::class,
            WorkpackageService::class,
            ActionService::class,
            AssertionService::class,
            EmailService::class,
            TranslatorInterface::class,
            EntityManager::class
        ],
        Controller\DocumentController::class               => [
            CalendarService::class,
            GeneralService::class,
            EntityManager::class,
            TranslatorInterface::class
        ],
        Controller\JsonController::class                   => [
            CalendarService::class
        ],
        Controller\ManagerController::class                => [
            CalendarService::class,
            FormService::class,
            ProjectService::class,
            ActionService::class,
            ContactService::class,
            GeneralService::class,
            AssertionService::class,
            EntityManager::class,
            TranslatorInterface::class
        ],
        // Controller plugins
        Controller\Plugin\RenderCalendarContactList::class => [
            TwigRenderer::class,
            ModuleOptions::class,
            CalendarService::class
        ],
        Controller\Plugin\RenderReviewCalendar::class      => [
            TwigRenderer::class,
            ModuleOptions::class
        ],
        Service\CalendarService::class                     => [
            EntityManager::class,
            SelectionContactService::class,
            CalendarSearchService::class,
            ContactService::class
        ],
        Search\Service\CalendarSearchService::class        => [
            'Config'
        ],
        View\Handler\CalendarHandler::class                => [
            'Application',
            'ViewHelperManager',
            TwigRenderer::class,
            TranslatorInterface::class,
            Service\CalendarService::class,
            CalendarSearchService::class
        ],
    ]
];