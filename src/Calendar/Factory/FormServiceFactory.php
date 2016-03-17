<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    General
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/main for the canonical source repository
 */
namespace Calendar\Factory;

use Calendar\Service\FormService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class FormServiceFactory
 *
 * @package General\Factory
 */
class FormServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return FormService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $formService = new FormService();
        $formService->setServiceLocator($serviceLocator);
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);
        $formService->setEntityManager($entityManager);

        return $formService;
    }
}
