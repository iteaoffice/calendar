<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\Repository;

use Calendar\Entity;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ContactRole
 *
 * @package Calendar\Repository
 */
final class ContactRole extends EntityRepository
{
    public function findFiltered(array $filter): QueryBuilder
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('calendar_entity_contact_role');
        $queryBuilder->from(Entity\ContactRole::class, 'calendar_entity_contact_role');

        $direction = 'DESC';
        if (
            isset($filter['direction'])
            && \in_array(strtoupper($filter['direction']), ['ASC', 'DESC'], true)
        ) {
            $direction = strtoupper($filter['direction']);
        }

        switch ($filter['order']) {
            case 'id':
                $queryBuilder->addOrderBy('calendar_entity_contact_role.id', $direction);
                break;
            case 'role':
                $queryBuilder->addOrderBy('calendar_entity_contact_role.role', $direction);
                break;
            default:
                $queryBuilder->addOrderBy('calendar_entity_contact_role.role', Criteria::ASC);
        }

        return $queryBuilder;
    }
}
