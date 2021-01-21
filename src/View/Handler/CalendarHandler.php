<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\View\Handler;

use Calendar\Search\Service\CalendarSearchService;
use Calendar\Service\CalendarService;
use Content\Entity\Content;
use Search\Form\SearchResult;
use Search\Paginator\Adapter\SolariumPaginator;
use Solarium\QueryType\Select\Query\Query as SolariumQuery;
use Laminas\Authentication\AuthenticationService;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\Application;
use Laminas\Paginator\Paginator;
use Laminas\View\HelperPluginManager;
use General\View\Handler\AbstractHandler;
use ZfcTwig\View\TwigRenderer;

use function array_filter;
use function count;
use function http_build_query;
use function in_array;
use function sprintf;

/**
 * Class CalendarHandler
 *
 * @package Calendar\View\Handler
 */
final class CalendarHandler extends AbstractHandler
{
    public const LIMIT = 10;

    private CalendarService $calendarService;
    private CalendarSearchService $calendarSearchService;

    public function __construct(
        Application $application,
        HelperPluginManager $helperPluginManager,
        TwigRenderer $renderer,
        AuthenticationService $authenticationService,
        TranslatorInterface $translator,
        CalendarService $calendarService,
        CalendarSearchService $calendarSearchService
    ) {
        parent::__construct(
            $application,
            $helperPluginManager,
            $renderer,
            $authenticationService,
            $translator
        );

        $this->calendarService = $calendarService;
        $this->calendarSearchService = $calendarSearchService;
    }

    public function __invoke(Content $content): ?string
    {
        switch ($content->getHandler()->getHandler()) {
            case 'calendar':
            case 'calendar_past':
            case 'calendar_upcoming':
                $this->getHeadTitle()->append($this->translate('txt-upcoming-calendar'));

                return $this->parseCalendar();
            default:
                return sprintf(
                    'No handler available for <code>%s</code> in class <code>%s</code>',
                    $content->getHandler()->getHandler(),
                    __CLASS__
                );
        }
    }

    public function parseCalendar(): string
    {
        $page = $this->routeMatch->getParam('page', 1);

        $form = new SearchResult();
        $data = array_merge(
            [
                'order'     => '',
                'direction' => '',
                'query'     => '',
                'facet'     => [],
            ],
            $this->request->getQuery()->toArray()
        );
        $hasTerm = ! in_array($data['query'], ['*', ''], true) || count($data['facet']) !== 0;

        $searchFields = ['calendar_search', 'description_search', 'highlight_description_search', 'location_search'];

        if ($this->request->isGet()) {
            $this->calendarSearchService->setSearch(
                $data['query'],
                $searchFields,
                $data['order'],
                $data['direction'],
                false,
                $hasTerm
            );
            if (isset($data['facet'])) {
                foreach ($data['facet'] as $facetField => $values) {
                    $quotedValues = [];

                    foreach ($values as $value) {
                        $quotedValues[] = \sprintf('"%s"', $value);
                    }

                    $this->calendarSearchService->addFilterQuery(
                        $facetField,
                        implode(' ' . SolariumQuery::QUERY_OPERATOR_OR . ' ', $quotedValues)
                    );
                }
            }

            $form->addSearchResults(
                $this->calendarSearchService->getQuery()->getFacetSet(),
                $this->calendarSearchService->getResultSet()->getFacetSet(),
                ['year']
            );
            $form->setData($data);
        }

        $paginator = new Paginator(
            new SolariumPaginator(
                $this->calendarSearchService->getSolrClient(),
                $this->calendarSearchService->getQuery()
            )
        );
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? 1000 : 12);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        // Remove order and direction from the GET params to prevent duplication
        $filteredData = array_filter(
            $data,
            static function ($key) {
                return ! in_array($key, ['order', 'direction'], true);
            },
            ARRAY_FILTER_USE_KEY
        );

        return $this->renderer->render(
            'cms/calendar/list',
            [
                'form'              => $form,
                'order'             => $data['order'],
                'direction'         => $data['direction'],
                'query'             => $data['query'],
                'badges'            => $form->getBadges(),
                'arguments'         => http_build_query($filteredData),
                'route'             => $this->routeMatch->getMatchedRouteName(),
                'params'            => $this->routeMatch->getParams(),
                'paginator'         => $paginator,
                'page'              => $page,
                'hasTerm'           => $hasTerm,
                'calendarService'   => $this->calendarService,
                'upcomingCalendar'  => $this->calendarSearchService->findUpcomingCalendar(50),
                'highlightCalendar' => $this->calendarSearchService->findHighlightCalendar(),
            ]
        );
    }
}
