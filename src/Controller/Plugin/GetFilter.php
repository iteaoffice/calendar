<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\Controller\Plugin;

use Doctrine\Common\Collections\Criteria;
use Interop\Container\ContainerInterface;
use Laminas\Http\Request;
use Laminas\Json\Json;
use Laminas\Mvc\Application;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

use function base64_decode;
use function base64_encode;
use function http_build_query;
use function urldecode;

/**
 * Class GetFilter
 *
 * @package Calendar\Controller\Plugin
 */
final class GetFilter extends AbstractPlugin
{
    private ContainerInterface $container;
    private array $filter = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(): GetFilter
    {
        $filter = [];
        /** @var Application $application */
        $application = $this->container->get('application');
        $encodedFilter = urldecode((string)$application->getMvcEvent()->getRouteMatch()->getParam('encodedFilter'));
        /** @var Request $request */
        $request = $application->getMvcEvent()->getRequest();

        $filter = [];
        if (! empty($base64decodedFilter = base64_decode($encodedFilter))) {
            $filter = (array)Json::decode($base64decodedFilter);
        }

        $order = $request->getQuery('order');
        $direction = $request->getQuery('direction');

        // If the form is submitted, refresh the URL
        if ($request->isGet() && ($request->getQuery('submit') !== null)) {
            $query = $request->getQuery()->toArray();
            if (isset($query['filter'])) {
                $filter = $query['filter'];
            }
        }

        // Add a default order and direction if not known in the filter
        if (! isset($filter['order'])) {
            $filter['order'] = '';
            $filter['direction'] = Criteria::ASC;
        }

        // Overrule the order if set in the query
        if (null !== $order) {
            $filter['order'] = $order;
        }

        // Overrule the direction if set in the query
        if (null !== $direction) {
            $filter['direction'] = $direction;
        }

        $this->filter = $filter;

        return $this;
    }

    public function getFilter(): array
    {
        return $this->filter;
    }

    public function parseFilteredSortQuery(array $removeParams = []): string
    {
        $filterCopy = $this->filter;
        unset($filterCopy['order'], $filterCopy['direction']);
        foreach ($removeParams as $param) {
            unset($filterCopy[$param]);
        }

        return http_build_query(['filter' => $filterCopy, 'submit' => 'true']);
    }

    public function getOrder(): string
    {
        return $this->filter['order'];
    }

    public function getDirection(): string
    {
        return $this->filter['direction'];
    }

    public function getHash(): string
    {
        return base64_encode(Json::encode($this->filter));
    }
}
