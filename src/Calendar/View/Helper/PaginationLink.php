<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category   Content
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2014 ITEA Office
 * @license    http://debranova.org/license.txt proprietary
 *
 * @link       http://debranova.org
 */

namespace Calendar\View\Helper;

use Zend\View\Helper\Url;

/**
 * Create a link to an document.
 *
 * @category   Affiliation
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2014 ITEA Office
 * @license    http://debranova.org/license.txt proprietary
 *
 * @link       http://debranova.org
 */
class PaginationLink extends LinkAbstract
{
    /**
     * @param $page
     * @param $show
     *
     * @return string
     */
    public function __invoke($page, $show)
    {
        $router = $this->getRouteMatch()->getMatchedRouteName();

        $params = array_merge(
            $this->getRouteMatch()->getParams(),
            [
                'page' => $page,
            ]
        );

        /*
         * @var Url
         */
        $url = $this->serviceLocator->get('url');

        $uri = '<a href="%s" title="%s">%s</a>';

        return sprintf(
            $uri,
            $url($router, $params),
            sprintf($this->translate("txt-go-to-page-%s"), $show),
            $show
        );
    }
}
