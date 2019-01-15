<?php
/**
 * ITEA Office all rights reserved
 *
 * @category   News
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2017 ITEA Office (http://itea3.org)
 */
declare(strict_types=1);

namespace Calendar\View\Handler;

use Calendar\Entity\Calendar;
use Calendar\Search\Service\CalendarSearchService;
use Calendar\Service\CalendarService;
use Calendar\View\Helper\CalendarLink;
use Content\Entity\Content;
use Search\Form\SearchResult;
use Search\Paginator\Adapter\SolariumPaginator;
use Solarium\QueryType\Select\Query\Query as SolariumQuery;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\Application;
use Zend\Paginator\Paginator;
use Zend\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;

/**
 * Class CalendarHandler
 *
 * @package Calendar\View\Handler
 */
class CalendarHandler extends AbstractHandler
{
    public const LIMIT = 10;
    /**
     * @var CalendarService
     */
    private $calendarService;
    /**
     * @var CalendarSearchService
     */
    private $calendarSearchService;

    public function __construct(
        Application $application,
        HelperPluginManager $helperPluginManager,
        TwigRenderer $renderer,
        TranslatorInterface $translator,
        CalendarService $calendarService,
        CalendarSearchService $calendarSearchService
    ) {
        parent::__construct(
            $application,
            $helperPluginManager,
            $renderer,
            $translator
        );

        $this->calendarService = $calendarService;
        $this->calendarSearchService = $calendarSearchService;
    }

    public function __invoke(Content $content): ?string
    {
        $params = $this->extractContentParam($content);

        switch ($content->getHandler()->getHandler()) {
            case 'calendar':
            case 'calendar_past':
            case 'calendar_upcoming':
                $this->getHeadTitle()->append($this->translate('txt-upcoming-calendar'));

                return $this->parseCalendar();

            case 'calendar_small':
                return $this->parseCalendarSmall($params['limit']);
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
        $hasTerm = !\in_array($data['query'], ['*', ''], true) || \count($data['facet']) !== 0;

        $searchFields = ['calendar_search', 'description_search', 'highlight_description_search', 'location_search'];

        if ($this->request->isGet()) {
            $this->calendarSearchService->setSearch($data['query'], $searchFields, $data['order'], $data['direction'], false, $hasTerm);
            if (isset($data['facet'])) {
                foreach ($data['facet'] as $facetField => $values) {
                    $quotedValues = [];

                    foreach ($values as $value) {
                        $quotedValues[] = $value;
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
            function ($key) {
                return !\in_array($key, ['order', 'direction'], true);
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
                'arguments'         => http_build_query($filteredData),
                'paginator'         => $paginator,
                'page'              => $page,
                'hasTerm'           => $hasTerm,
                'calendarService'   => $this->calendarService,
                'upcomingCalendar'  => $this->calendarSearchService->findUpcomingCalendar(50),
                'highlightCalendar' => $this->calendarSearchService->findHighlightCalendar(),
            ]
        );
    }


    public function parseCalendarSmall(int $limit = self::LIMIT): string
    {
        return $this->renderer->render(
            'cms/calendar/calendar-small',
            [
                'calendarItems'   => $this->calendarSearchService->findUpcomingCalendar($limit),
                'calendarService' => $this->calendarService
            ]
        );
    }
}
