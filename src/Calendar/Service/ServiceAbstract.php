<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (https://itea3.org)
 */

namespace Calendar\Service;

use Admin\Service\AdminService;
use BjyAuthorize\Service\Authorize;
use Calendar\Entity;
use Calendar\Entity\EntityAbstract;
use Calendar\Options\ModuleOptions;
use Contact\Service\ContactService;
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
     * @var Entity\Calendar
     */
    protected $calendar;

    /**
     * @param      $entity
     * @param bool $toArray
     *
     * @return array
     */
    public function findAll($entity, $toArray = false)
    {
        return $this->getEntityManager()->getRepository($this->getFullEntityName($entity))->findAll();
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
        return $this->getEntityManager()->getRepository($this->getFullEntityName($entity))->find($id);
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

        $this->getAdminService()
            ->flushPermitsByEntityAndId($entity->get('underscore_full_entity_name'), $entity->getId());

        return $entity;
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
     * Build dynamically a entity based on the full entity name.
     *
     * @param $entity
     *
     * @return mixed
     */
    public function getEntity($entity)
    {
        $entity = $this->getFullEntityName($entity);

        return new $entity();
    }

    /**
     * Create a full path to the entity for Doctrine.
     *
     * @param $entity
     *
     * @return string
     */
    public function getFullEntityName($entity)
    {
        /*
         * Convert a - to a camelCased situation
         */
        if (strpos($entity, '-') !== false) {
            $entity = explode('-', $entity);
            $entity = $entity[0] . ucfirst($entity[1]);
        }

        return ucfirst(implode('', array_slice(explode('\\', __NAMESPACE__), 0, 1))) . '\\' . 'Entity' . '\\'
        . ucfirst($entity);
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
     * @return AuthenticationService
     */
    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }

    /**
     * @param AuthenticationService $authenticationService
     *
     * @return ServiceAbstract
     */
    public function setAuthenticationService($authenticationService)
    {
        $this->authenticationService = $authenticationService;

        return $this;
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
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ServiceAbstract
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * @return ContactService
     */
    public function getContactService()
    {
        return $this->contactService;
    }

    /**
     * @param ContactService $contactService
     *
     * @return ServiceAbstract
     */
    public function setContactService($contactService)
    {
        $this->contactService = $contactService;

        return $this;
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions()
    {
        return $this->moduleOptions;
    }

    /**
     * @param ModuleOptions $moduleOptions
     *
     * @return ServiceAbstract
     */
    public function setModuleOptions($moduleOptions)
    {
        $this->moduleOptions = $moduleOptions;

        return $this;
    }

    /**
     * @return Entity\Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @param Entity\Calendar $calendar
     *
     * @return ServiceAbstract
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * @return Authorize
     */
    public function getAuthorizeService()
    {
        return $this->authorizeService;
    }

    /**
     * @param Authorize $authorizeService
     *
     * @return ServiceAbstract
     */
    public function setAuthorizeService($authorizeService)
    {
        $this->authorizeService = $authorizeService;

        return $this;
    }
}
