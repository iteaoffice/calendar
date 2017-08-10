<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Service;

use Admin\Service\AdminService;
use BjyAuthorize\Service\Authorize;
use Calendar\Entity;
use Calendar\Entity\EntityAbstract;
use Calendar\Options\ModuleOptions;
use Contact\Service\ContactService;
use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ServiceAbstract.
 */
abstract class ServiceAbstract implements ServiceInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    /**
     * @var AuthenticationService;
     */
    protected $authenticationService;
    /**
     * @var AdminService;
     */
    protected $adminService;
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
    /**
     * @var ContactService
     */
    protected $contactService;
    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;
    /**
     * @var Authorize
     */
    protected $authorizeService;

    /**
     * @param      $entity
     * @param bool $toArray
     *
     * @return array
     */
    public function findAll($entity, $toArray = false)
    {
        return $this->getEntityManager()->getRepository($entity)->findAll();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     *
     * @return ServiceAbstract
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * Find 1 entity based on the id.
     *
     * @param string $entity
     * @param        $id
     *
     * @return null|Entity\Calendar|Entity\ContactStatus
     */
    public function findEntityById($entity, $id)
    {
        return $this->getEntityManager()->getRepository($entity)->find($id);
    }

    /**
     * @param Entity\EntityAbstract $entity
     *
     * @return Entity\EntityAbstract
     */
    public function newEntity(Entity\EntityAbstract $entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity;
    }

    /**
     * @param Entity\EntityAbstract $entity
     *
     * @return Entity\EntityAbstract
     */
    public function updateEntity(Entity\EntityAbstract $entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $this->getAdminService()->flushPermitsByEntityAndId($entity->get('underscore_entity_name'), $entity->getId());

        return $entity;
    }

    /**
     * @return AdminService
     */
    public function getAdminService()
    {
        return $this->adminService;
    }

    /**
     * @param AdminService $adminService
     *
     * @return ServiceAbstract
     */
    public function setAdminService($adminService)
    {
        $this->adminService = $adminService;

        return $this;
    }

    /**
     * @param Entity\EntityAbstract $entity
     *
     * @return bool
     */
    public function removeEntity(Entity\EntityAbstract $entity)
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @param EntityAbstract $entity
     * @param                $assertion
     */
    public function addResource(EntityAbstract $entity, $assertion)
    {
        /*
         * @var AssertionAbstract
         */
        $assertion = $this->getServiceLocator()->get($assertion);
        if (!$this->getAuthorizeService()->getAcl()->hasResource($entity)) {
            $this->getAuthorizeService()->getAcl()->addResource($entity);
            $this->getAuthorizeService()->getAcl()->allow([], $entity, [], $assertion);
        }
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface|ContainerInterface $serviceLocator
     *
     * @return ServiceAbstract
     */
    public function setServiceLocator($serviceLocator): ServiceAbstract
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * @return Authorize
     */
    public function getAuthorizeService(): Authorize
    {
        return $this->authorizeService;
    }

    /**
     * @param Authorize $authorizeService
     *
     * @return ServiceAbstract
     */
    public function setAuthorizeService($authorizeService): ServiceAbstract
    {
        $this->authorizeService = $authorizeService;

        return $this;
    }

    /**
     * @return AuthenticationService
     */
    public function getAuthenticationService(): AuthenticationService
    {
        return $this->authenticationService;
    }

    /**
     * @param AuthenticationService $authenticationService
     *
     * @return ServiceAbstract
     */
    public function setAuthenticationService($authenticationService): ServiceAbstract
    {
        $this->authenticationService = $authenticationService;

        return $this;
    }

    /**
     * @return ContactService
     */
    public function getContactService(): ContactService
    {
        return $this->contactService;
    }

    /**
     * @param ContactService $contactService
     *
     * @return ServiceAbstract
     */
    public function setContactService($contactService): ServiceAbstract
    {
        $this->contactService = $contactService;

        return $this;
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions(): ModuleOptions
    {
        return $this->moduleOptions;
    }

    /**
     * @param ModuleOptions $moduleOptions
     *
     * @return ServiceAbstract
     */
    public function setModuleOptions($moduleOptions): ServiceAbstract
    {
        $this->moduleOptions = $moduleOptions;

        return $this;
    }
}
