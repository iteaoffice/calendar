<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category  Publication
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2016 ITEA Office (http://itea3.org)
 */

namespace Calendar\Controller\Factory;

use Calendar\Controller\CalendarAbstractController;
use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use Contact\Service\ContactService;
use Contact\Service\SelectionService;
use Doctrine\ORM\EntityManager;
use Event\Service\FormService;
use General\Service\EmailService;
use General\Service\GeneralService;
use Project\Service\ProjectService;
use Project\Service\WorkpackageService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcTwig\View\TwigRenderer;

/**
 * Class ControllerInvokableAbstractFactory
 *
 * @package Calendar\Controller\Factory
 */
class ControllerInvokableAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface|ControllerManager $serviceLocator
     * @param                                           $name
     * @param                                           $requestedName
     *
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return (class_exists($requestedName)
            && in_array(CalendarAbstractController::class, class_parents($requestedName)));
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface|ControllerManager $serviceLocator
     * @param string                                    $name
     * @param string                                    $requestedName
     *
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        try {

            /** @var CalendarAbstractController $controller */
            $controller = new $requestedName();
            $controller->setServiceLocator($serviceLocator);

            $serviceManager = $serviceLocator->getServiceLocator();

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

            return $controller;
        } catch (\Exception $e) {
            var_dump($e);
            die();
        }
    }
}
