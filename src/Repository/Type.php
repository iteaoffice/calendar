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
 * Class ContactStatus
 *
 * @package Calendar\Repository
 */
final class Type extends EntityRepository
{
    public function findFiltered(array $filter): QueryBuilder
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('calendar_entity_type');
        $queryBuilder->from(Entity\Type::class, 'calendar_entity_type');

        $direction = 'DESC';
        if (
            isset($filter['direction'])
            && \in_array(strtoupper($filter['direction']), ['ASC', 'DESC'], true)
        ) {
            $direction = strtoupper($filter['direction']);
        }

        switch ($filter['order']) {
            case 'id':
                $queryBuilder->addOrderBy('calendar_entity_type.id', $direction);
                break;
            case 'type':
                $queryBuilder->addOrderBy('calendar_entity_type.type', $direction);
                break;
            default:
                $queryBuilder->addOrderBy('calendar_entity_type.type', Criteria::ASC);
        }

        return $queryBuilder;
    }
}
