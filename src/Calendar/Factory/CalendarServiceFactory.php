<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Calendar
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/main for the canonical source repository
 */
namespace Calendar\Factory;

use Admin\Service\AdminService;
use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CalendarServiceFactory
 *
 * @package Calendar\Factory
 */
class CalendarServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CalendarService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $calendarService = new CalendarService();
        $calendarService->setServiceLocator($serviceLocator);

        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);
        $calendarService->setEntityManager($entityManager);

        /** @var  $authorizeService */
        //$authorizeService = $serviceLocator->get(Authorize::class);
        //$calendarService->setAuthorizeService($authorizeService);

        /** @var ContactService $contactService */
        $contactService = $serviceLocator->get(ContactService::class);
        $calendarService->setContactService($contactService);

        /** @var AdminService $adminService */
        $adminService = $serviceLocator->get(AdminService::class);
        $calendarService->setAdminService($adminService);

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get(ModuleOptions::class);
        $calendarService->setModuleOptions($moduleOptions);

        return $calendarService;
    }
}
