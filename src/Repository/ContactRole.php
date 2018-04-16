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
class ContactRole extends EntityRepository
{
    /**
     * @param array $filter
     *
     * @return QueryBuilder
     */
    public function findFiltered(array $filter): QueryBuilder
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('calendar_entity_contact_role');
        $queryBuilder->from(Entity\ContactRole::class, 'calendar_entity_contact_role');

        $direction = 'DESC';
        if (isset($filter['direction'])
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
