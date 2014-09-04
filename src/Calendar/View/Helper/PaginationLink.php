<?php

/**
 * ITEA Office copyright message placeholder
 *
 * @category   Content
 * @package    View
 * @subpackage Helper
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Create a link to an article
 *
 * @category   Content
 * @package    View
 * @subpackage Helper
 */
class PaginationLink extends AbstractHelper
{
    /**
     * @param $entity
     * @param $page
     * @param $show
     *
     * @return string
     */
    public function __invoke($entity, $page, $show)
    {
        $router = 'community/calendar/overview';
        $translate = $this->view->plugin('translate');
        $url       = $this->view->plugin('url');
        $params = array(
            'entity' => $entity,
            'page'   => $page
        );
        $uri = '<a href="%s" title="%s">%s</a>';

        return sprintf(
            $uri,
            $url($router, $params),
            sprintf($translate("txt-go-to-page-%s"), $show),
            $show
        );
    }
}
