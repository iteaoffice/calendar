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
use BjyAuthorize\Service\Authorize;
use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CalendarServiceFactory
 *
 * @package Calendar\Factory
 */
final class CalendarServiceFactory implements FactoryInterface
{
    /**
     * Create an instance of the requested class name.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return CalendarService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var CalendarService $calendarService */
        $calendarService = new $requestedName($options);
        $calendarService->setServiceLocator($container);

        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        $calendarService->setEntityManager($entityManager);

        /** @var Authorize $authorizeService */
        $authorizeService = $container->get(Authorize::class);
        $calendarService->setAuthorizeService($authorizeService);

        /** @var ContactService $contactService */
        $contactService = $container->get(ContactService::class);
        $calendarService->setContactService($contactService);

        /** @var AdminService $adminService */
        $adminService = $container->get(AdminService::class);
        $calendarService->setAdminService($adminService);

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);
        $calendarService->setModuleOptions($moduleOptions);

        return $calendarService;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string|null             $canonicalName
     * @param string|null             $requestedName
     *
     * @return CalendarService
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $canonicalName = null, $requestedName = null)
    {
        return $this($serviceLocator, $requestedName);
    }
}
