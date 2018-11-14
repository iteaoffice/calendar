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

namespace Calendar\Service;

use Admin\Entity\Access;
use Admin\Entity\Permit;
use Admin\Repository\Permit\Role;
use Calendar\Entity;
use Contact\Entity\Contact;
use Contact\Service\SelectionContactService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AbstractService
 *
 * @package Project\Service
 */
abstract class AbstractService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var SelectionContactService
     */
    protected $selectionContactService;

    public function __construct(EntityManager $entityManager, SelectionContactService $selectionContactService = null)
    {
        $this->entityManager = $entityManager;
        $this->selectionContactService = $selectionContactService;
    }

    public function findFilteredByContact(string $entity, $filter, Contact $contact): QueryBuilder
    {
        //The 'filter' should always be there to support the repositories
        if (!\array_key_exists('filter', $filter)) {
            $filter['filter'] = [];
        }

        $qb = $this->findFiltered($entity, $filter);

        return $this->limitQueryBuilderByPermissions($qb, $contact, $entity);
    }

    public function findFiltered(string $entity, array $filter): QueryBuilder
    {
        return $this->entityManager->getRepository($entity)->findFiltered(
            $filter,
            AbstractQuery::HYDRATE_SIMPLEOBJECT
        );
    }

    protected function limitQueryBuilderByPermissions(
        QueryBuilder $qb,
        Contact $contact,
        string $entity,
        string $permit = 'list'
    ): QueryBuilder {

        //Create an entity from the name
        /** @var Entity\AbstractEntity $entity */
        $entity = new $entity();

        switch ($permit) {
            case 'edit':
                $limitQueryBuilder = $this->parseWherePermit($entity, 'edit', $contact);
                break;
            case 'list':
            default:
                $limitQueryBuilder = $this->parseWherePermit($entity, 'list', $contact);
                break;
        }


        /*
         * Limit the projects based on the rights
         */
        if (null !== $limitQueryBuilder) {
            $qb->andWhere(
                $qb->expr()
                    ->in(strtolower($entity->get('underscore_entity_name')), $limitQueryBuilder->getDQL())
            );
        } else {
            $qb->andWhere(
                $qb->expr()->isNull(
                    strtolower($entity->get('underscore_entity_name'))
                    . '.id'
                )
            );
        }

        return $qb;
    }

    public function parseWherePermit(Entity\AbstractEntity $entity, string $roleName, Contact $contact): ?QueryBuilder
    {
        $permitEntity = $this->findPermitEntityByEntity($entity);

        if (null === $permitEntity) {
            throw new \InvalidArgumentException(sprintf("Entity '%s' cannot be found as permit", $entity));
        }

        //Try to find the corresponding role
        $role = $this->entityManager->getRepository(Permit\Role::class)->findOneBy(
            [
                'entity' => $permitEntity,
                'role'   => $roleName,
            ]
        );


        if (null === $role) {
            //We have no roles found, so return a query which gives always zeros
            //We will simply return NULL
            print sprintf("Role '%s' on entity '%s' could not be found", $roleName, $entity);

            return null;
        }

        //@todo; fix this when no role is found (equals to NULL for example)
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('permit_contact.keyId');
        $qb->from(Permit\Contact::class, 'permit_contact');
        $qb->andWhere('permit_contact.contact = ' . $contact->getId());
        $qb->andWhere('permit_contact.role = ' . $role->getId());

        return $qb;
    }

    public function findPermitEntityByEntity(Entity\AbstractEntity $entity): ?Permit\Entity
    {
        return $this->entityManager->getRepository(Permit\Entity::class)
            ->findOneBy(['underscoreFullEntityName' => $entity->get('underscore_entity_name')]);
    }

    public function findAll(string $entity): array
    {
        return $this->entityManager->getRepository($entity)->findAll();
    }

    public function find(string $entity, int $id): ?Entity\AbstractEntity
    {
        return $this->entityManager->getRepository($entity)->find($id);
    }

    public function findByName(string $entity, string $column, string $name): ?Entity\AbstractEntity
    {
        return $this->entityManager->getRepository($entity)->findOneBy([$column => $name]);
    }

    public function save(Entity\AbstractEntity $entity): Entity\AbstractEntity
    {
        if (!$this->entityManager->contains($entity)) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();

        $this->flushPermitsByEntityAndId($entity, (int)$entity->getId());

        return $entity;
    }

    public function flushPermitsByEntityAndId(Entity\AbstractEntity $entity, int $id): void
    {
        $permitEntity = $this->findPermitEntityByEntity($entity);
        /**
         * Do not do anything when the permit cannot be found
         */
        if (null === $permitEntity) {
            return;
        }

        $repository = $this->entityManager->getRepository(Permit\Entity::class);
        $repository->flushPermitsByEntityAndId($permitEntity, $id);

        $this->flushAccessPermitsByEntityAndId($permitEntity, $id);
    }

    private function flushAccessPermitsByEntityAndId(Permit\Entity $permitEntity, int $id): void
    {
        /**
         * Add the role based on the role_selections
         */
        foreach ($permitEntity->getRole() as $role) {
            foreach ($role->getAccess() as $accessRole) {
                $this->flushPermitsPerRoleByAccessRoleAndId($role, $accessRole, $id);
            }
        }
    }

    private function flushPermitsPerRoleByAccessRoleAndId(Permit\Role $role, Access $access, $id): void
    {
        /** @var Role $repository */
        $repository = $this->entityManager->getRepository(Permit\Role::class);

        /*
         * Go over te contacts in the selection
         */
        foreach ($access->getContact() as $contact) {
            if (null === $contact->getDateEnd()) {
                $repository->insertPermitsForRoleByContactAndId($role, $contact, $id);
            }
        }

        /*
         * Go over the selections in having the access role
         */
        foreach ($access->getSelection() as $selection) {
            foreach ($this->selectionContactService->findContactsInSelection($selection) as $contact) {
                $repository->insertPermitsForRoleByContactAndId($role, $contact, $id);
            }
        }
    }

    public function delete(Entity\AbstractEntity $abstractEntity): void
    {
        $this->entityManager->remove($abstractEntity);
        $this->entityManager->flush();
    }

    public function refresh(Entity\AbstractEntity $abstractEntity): void
    {
        $this->entityManager->refresh($abstractEntity);
    }
}
