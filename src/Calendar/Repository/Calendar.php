<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Repository
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Calendar\Entity;
use Calendar\Service\CalendarService;

/**
 * @category    Calendar
 * @package     Repository
 */
class Calendar extends EntityRepository
{
    /**
     * @param $which
     *
     * @return Entity\Calendar[]
     */
    public function findCalendarItems($which)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c');
        $qb->from("Calendar\Entity\Calendar", 'c');

        switch ($which) {
            case CalendarService::WHICH_UPCOMING:
                $qb->andWhere('c.dateEnd > ?1');
                $qb->orderBy('c.dateFrom', 'ASC');
                $qb->setParameter(1, new \DateTime());
                break;
            case CalendarService::WHICH_PAST:
                $qb->andWhere('c.dateFrom < ?1');
                $qb->orderBy('c.dateEnd', 'DESC');
                $qb->setParameter(1, new \DateTime());
                break;
            case CalendarService::WHICH_UPDATED;
                $qb->orderBy('c.dateUpdated', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}