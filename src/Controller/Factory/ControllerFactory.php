<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */
namespace Calendar\Controller\Factory;

use Calendar\Controller\CalendarAbstractController;
use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use Calendar\Service\FormService;
use Contact\Service\ContactService;
use Contact\Service\SelectionService;
use Doctrine\ORM\EntityManager;
use General\Service\EmailService;
use General\Service\GeneralService;
use Interop\Container\ContainerInterface;
use Project\Service\ProjectService;
use Project\Service\WorkpackageService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;

/**
 * Class ControllerFactory
 *
 * @package Project\Controller\Factory
 */
final class ControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface|ControllerManager $container
     * @param string                               $requestedName
     * @param array|null                           $options
     *
     * @return CalendarAbstractController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var CalendarAbstractController $controller */
        $controller     = new $requestedName($options);
        $serviceManager = $container;

        /** @var FormService $formService */
        $formService = $serviceManager->get(FormService::class);
        $controller->setFormService($formService);

        /** @var EntityManager $entityManager */
        $entityManager = $serviceManager->get(EntityManager::class);
        $controller->setEntityManager($entityManager);

        /** @var TwigRenderer $renderer */
        $renderer = $serviceManager->get('ZfcTwigRenderer');
        $controller->setRenderer($renderer);

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceManager->get(ModuleOptions::class);
        $controller->setModuleOptions($moduleOptions);

        /** @var ContactService $contactService */
        $contactService = $serviceManager->get(ContactService::class);
        $controller->setContactService($contactService);

        /** @var ProjectService $projectService */
        $projectService = $serviceManager->get(ProjectService::class);
        $controller->setProjectService($projectService);

        /** @var WorkPackageService $workpackageService */
        $workpackageService = $serviceManager->get(WorkpackageService::class);
        $controller->setWorkpackageService($workpackageService);

        /** @var CalendarService $calendarService */
        $calendarService = $serviceManager->get(CalendarService::class);
        $controller->setCalendarService($calendarService);

        /** @var GeneralService $generalService */
        $generalService = $serviceManager->get(GeneralService::class);
        $controller->setGeneralService($generalService);

        /** @var EmailService $emailService */
        $emailService = $serviceManager->get(EmailService::class);
        $controller->setEmailService($emailService);

        /** @var SelectionService $selectionService */
        $selectionService = $serviceManager->get(SelectionService::class);
        $controller->setSelectionService($selectionService);

        /** @var HelperPluginManager $viewHelperManager */
        $viewHelperManager = $container->get('ViewHelperManager');
        $controller->setViewHelperManager($viewHelperManager);

        return $controller;
    }
}
