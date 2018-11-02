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
 * @link        https://github.com/iteaoffice/calendar for the canonical source repository
 */

declare(strict_types=1);

namespace Calendar\Search\Service;

use Search\Service\AbstractSearchService;
use Search\Service\SearchServiceInterface;
use Solarium\Core\Query\Result\ResultInterface;
use Solarium\QueryType\Select\Query\Query;

/**
 * Class CalendarSearchService
 *
 * @package Calendar\Search\Service
 */
final class CalendarSearchService extends AbstractSearchService
{
    public const SOLR_CONNECTION = 'calendar_calendar';

    public function setSearch(
        string $searchTerm,
        array $searchFields = [],
        string $order = '',
        string $direction = Query::SORT_ASC,
        bool $upcoming = false
    ): SearchServiceInterface {
        $this->setQuery($this->getSolrClient()->createSelect());

        $query = '(on_homepage:true) AND ';

        if ($upcoming) {
            $query .= ' date_from:[NOW TO *] AND ';
        }

        $query .= static::parseQuery($searchTerm, $searchFields);


        $this->getQuery()->setQuery($query);

        $hasTerm = !\in_array($searchTerm, ['*', ''], true);
        $hasSort = ($order !== '');

        if ($hasSort) {
            switch ($order) {
                default:
                    $this->getQuery()->addSort('date_from', Query::SORT_DESC);
                    break;
            }
        }

        if ($hasTerm) {
            $this->getQuery()->addSort('score', Query::SORT_DESC);
        } else {
            $this->getQuery()->addSort('date_from', Query::SORT_DESC);
        }

        $facetSet = $this->getQuery()->getFacetSet();
        $facetSet->createFacetField('year')->setField('year')->setSort('year')->setMinCount(1)->setExcludes(['year']);

        return $this;
    }

    public function findUpcomingCalendar(int $limit = 5): ResultInterface
    {
        $this->setQuery($this->getSolrClient()->createSelect());

        $this->query->setQuery('(on_homepage:true) AND date_from:[NOW TO *]')
            ->addSort('date_from', Query::SORT_DESC)
            ->setRows($limit);


        return $this->getSolrClient()->execute($this->query);
    }
}
