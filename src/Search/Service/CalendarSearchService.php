<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Calendar
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
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
class CalendarSearchService extends AbstractSearchService
{
    public const SOLR_CONNECTION = 'calendar_calendar';

    public function setAdminSearch(
        string $searchTerm,
        array $searchFields = [],
        string $order = '',
        string $direction = Query::SORT_ASC,
        bool $upcoming = true,
        bool $past = false
    ): SearchServiceInterface {
        $this->setQuery($this->getSolrClient()->createSelect());

        // Enable highlighting
        if ($searchTerm && ($searchTerm !== '*')) {
            $highlighting = $this->getQuery()->getHighlighting();
            $highlighting->setFields($searchFields);
            $highlighting->setSimplePrefix('<mark>');
            $highlighting->setSimplePostfix('</mark>');
        }

        $query = static::parseQuery($searchTerm, $searchFields);


        if ($past) {
            $query .= ' AND date_end:[* TO NOW] ';
        } elseif ($upcoming) {
            $query .= ' AND date_end:[NOW TO *] ';
        }

        $this->getQuery()->setQuery($query);

        $hasTerm = ! \in_array($searchTerm, ['*', ''], true);

        switch ($order) {
            case 'name':
                $this->getQuery()->addSort('name_sort', $direction);
                break;
            case 'type':
                $this->getQuery()->addSort('type_sort', $direction);
                break;
            case 'date':
                $this->getQuery()->addSort('date_from', $direction);
                break;
            case 'highlight':
                $this->getQuery()->addSort('highlight', $direction);
                break;
            case 'on-homepage':
                $this->getQuery()->addSort('on_homepage', $direction);
                break;
            case 'own-event':
                $this->getQuery()->addSort('own_event', $direction);
                break;
            case 'internal':
                $this->getQuery()->addSort('internal', $direction);
                break;
            default:
                if ($past) {
                    $this->getQuery()->addSort('date_from', Query::SORT_DESC);
                } else {
                    $this->getQuery()->addSort('date_from', Query::SORT_ASC);
                }

                break;
        }


        if ($hasTerm) {
            $this->getQuery()->addSort('score', Query::SORT_DESC);
        }

        $facetSet = $this->getQuery()->getFacetSet();
        $facetSet->createFacetField('year')->setField('year')->setSort('year')->setMinCount(1)->setExcludes(['year']);
        $facetSet->createFacetField('type')->setField('type')->setSort('type')->setMinCount(1)
            ->setExcludes(['type']);
        $facetSet->createFacetField('highlight')->setField('highlight_text')->setSort('highlight')->setMinCount(1)
            ->setExcludes(['highlight']);
        $facetSet->createFacetField('own_event')->setField('own_event_text')->setSort('own_event_text')->setMinCount(1)
            ->setExcludes(['own_event_text']);
        $facetSet->createFacetField('final')->setField('final_text')->setSort('final_text')->setMinCount(1)
            ->setExcludes(['final_text']);
        $facetSet->createFacetField('office_is_present')->setField('is_present_text')->setSort('is_present_text')
            ->setMinCount(1)->setExcludes(['is_present_text']);
        $facetSet->createFacetField('on_frontpage')->setField('on_homepage_text')->setSort('on_homepage')->setMinCount(
            1
        )->setExcludes(['on_homepage_text']);
        $facetSet->createFacetField('birthday')->setField('is_birthday_text')->setSort('is_birthday_text')->setMinCount(
            1
        )
            ->setExcludes(['is_birthday_text']);
        $facetSet->createFacetField('review')->setField('is_review_text')->setSort('is_review_text')->setMinCount(1)
            ->setExcludes(['is_review_text']);
        $facetSet->createFacetField('project_event')->setField('is_project_text')->setSort('is_project_text')
            ->setMinCount(1)
            ->setExcludes(['is_project_text']);
        $facetSet->createFacetField('project')->setField('project_name')->setSort('project_name')->setMinCount(1)
            ->setExcludes(['project_name']);

        return $this;
    }

    public function setCommunitySearch(
        string $searchTerm,
        array $searchFields = [],
        string $order = '',
        string $direction = Query::SORT_ASC,
        bool $upcoming = true,
        bool $past = false,
        array $hiddenItems = [],
        bool $isOffice = false
    ): SearchServiceInterface {
        $this->setQuery($this->getSolrClient()->createSelect());

        // Enable highlighting
        if ($searchTerm && ($searchTerm !== '*')) {
            $highlighting = $this->getQuery()->getHighlighting();
            $highlighting->setFields($searchFields);
            $highlighting->setSimplePrefix('<mark>');
            $highlighting->setSimplePostfix('</mark>');
        }

        $query = static::parseQuery($searchTerm, $searchFields);

        $query .= ' AND final:true AND is_birthday:false ';

        if ($past) {
            $query .= ' AND date_end:[* TO NOW] ';
        } elseif ($upcoming) {
            $query .= ' AND date_end:[NOW TO *] ';
        }

        //Apply a filer when the user is not office staff
        if (! $isOffice) {
            $query .= ' AND (is_call:true OR calendar_id:(0 ' . \implode(' ', $hiddenItems) . '))';
        }

        $this->getQuery()->setQuery($query);

        $hasTerm = ! \in_array($searchTerm, ['*', ''], true);

        switch ($order) {
            case 'name':
                $this->getQuery()->addSort('name_sort', $direction);
                break;
            case 'type':
                $this->getQuery()->addSort('type_sort', $direction);
                break;
            case 'date':
                $this->getQuery()->addSort('date_from', $direction);
                break;
            case 'highlight':
                $this->getQuery()->addSort('highlight', $direction);
                break;
            case 'on-homepage':
                $this->getQuery()->addSort('on_homepage', $direction);
                break;
            case 'own-event':
                $this->getQuery()->addSort('own_event', $direction);
                break;
            case 'internal':
                $this->getQuery()->addSort('internal', $direction);
                break;
            default:
                if ($past) {
                    $this->getQuery()->addSort('date_from', Query::SORT_DESC);
                } else {
                    $this->getQuery()->addSort('date_from', Query::SORT_ASC);
                }

                break;
        }


        if ($hasTerm) {
            $this->getQuery()->addSort('score', Query::SORT_DESC);
        }


        $facetSet = $this->getQuery()->getFacetSet();
        $facetSet->createFacetField('year')->setField('year')->setSort('year')->setMinCount(1)->setExcludes(['year']);
        $facetSet->createFacetField('type')->setField('type')->setSort('type')->setMinCount(1)
            ->setExcludes(['type']);
        $facetSet->createFacetField('is_review')->setField('is_review_text')->setSort('is_review_text')->setMinCount(1)
            ->setExcludes(['is_review_text']);
        $facetSet->createFacetField('project')->setField('project_name')->setSort('project_name')->setMinCount(1)
            ->setExcludes(['project_name']);

        return $this;
    }

    public function setSearch(
        string $searchTerm,
        array $searchFields = [],
        string $order = '',
        string $direction = Query::SORT_ASC,
        bool $upcoming = false,
        bool $hasTerm = false
    ): SearchServiceInterface {
        $this->setQuery($this->getSolrClient()->createSelect());

        $query = '(on_homepage:true) AND (final:true) AND ';

        if ($upcoming) {
            $query .= ' date_end:[NOW TO *] AND ';
        }

        if (! $hasTerm) {
            $query .= ' date_end:[* TO NOW] AND ';
        }

        $query .= static::parseQuery($searchTerm, $searchFields);

        $this->getQuery()->setQuery($query);
        $this->getQuery()->addSort('date_from', Query::SORT_DESC);

        if ($hasTerm) {
            $this->getQuery()->addSort('score', Query::SORT_DESC);
        }


        $facetSet = $this->getQuery()->getFacetSet();
        $facetSet->createFacetField('year')->setField('year')->setSort('year')->setMinCount(1)->setExcludes(['year']);

        return $this;
    }

    public function findUpcomingCalendar(int $limit = 5): ResultInterface
    {
        $this->setQuery($this->getSolrClient()->createSelect());

        $this->query->setQuery('(on_homepage:true) AND (final:true) AND date_end:[NOW TO *]')
            ->addSort('date_from', Query::SORT_ASC)
            ->setRows($limit);


        return $this->getSolrClient()->execute($this->query);
    }

    public function findHighlightCalendar(): ResultInterface
    {
        $this->setQuery($this->getSolrClient()->createSelect());

        $this->query->setQuery('on_homepage:true AND (final:true) AND highlight:true')
            ->addSort('date_from', Query::SORT_DESC);


        return $this->getSolrClient()->execute($this->query);
    }
}
