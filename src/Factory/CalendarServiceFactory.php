<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Calendar
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/main for the canonical source repository
 */
declare(strict_types=1);

namespace Calendar\Factory;

use Admin\Service\AdminService;
use BjyAuthorize\Service\Authorize;
use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use Contact\Service\ContactService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

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
     * @param string $requestedName
     * @param null|array $options
     *
     * @return CalendarService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CalendarService
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
}
