<?php

/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     View
 * @subpackage  Helper
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Calendar\Entity;
use Calendar\Service\CalendarService;

/**
 * Create a link to an calendar
 *
 * @category    Calendar
 * @package     View
 * @subpackage  Helper
 */
class CalendarLink extends AbstractHelper
{
    /**
     * @param Entity\Calendar $calendar
     * @param string          $action
     * @param string          $show
     * @param string          $which
     * @param null            $alternativeShow
     * @param null            $year
     *
     * @return string
     * @throws \Exception
     */
    public function __invoke(
        Entity\Calendar $calendar = null,
        $action = 'view',
        $show = 'name',
        $which = CalendarService::WHICH_UPCOMING,
        $alternativeShow = null,
        $year = null
    ) {
        $translate = $this->view->plugin('translate');
        $url       = $this->view->plugin('url');
        $serverUrl = $this->view->plugin('serverUrl');

        $params = array(
            'entity' => 'calendar'
        );

        switch ($action) {
            case 'new':
                $router   = 'zfcadmin/calendar-manager/new';
                $text     = sprintf($translate("txt-new-area"));
                $calendar = new Entity\Calendar();
                break;
            case 'edit':
                $router = 'zfcadmin/calendar-manager/edit';
                $text   = sprintf($translate("txt-edit-calendar-%s"), $calendar);
                break;
            case 'list':

                /**
                 * Push the docRef in the params array
                 */
                $router         = 'route-content_entity_node';
                $params['year'] = $year;
                /**
                 * @todo: hardcoded docRef here. Can we avoid this? Maybe by finding a node having the new overview as handler
                 */
                switch ($which) {
                    case CalendarService::WHICH_UPCOMING:
                        $params['docRef'] = 'upcoming-events';
                        $text             = sprintf($translate("txt-upcoming-events"));
                        break;
                    case CalendarService::WHICH_PAST:
                        $params['docRef'] = 'past-events';
                        $text             = sprintf($translate("txt-past-events"));
                        break;
                }
                $calendar = new Entity\Calendar();
                break;
            case 'overview':
                $router   = 'community/calendar/overview';
                $text     = sprintf($translate("txt-view-calendar-%s"), $calendar);
                $calendar = new Entity\Calendar();
                break;
            case 'overview-admin':
                $router   = 'zfcadmin/calendar-manager/overview';
                $text     = sprintf($translate("txt-view-calendar-%s"), $calendar);
                $calendar = new Entity\Calendar();
                break;
            case 'view':
                $router             = 'route-' . $calendar->get("underscore_full_entity_name");
                $params['calendar'] = $calendar->getId();
                $params['docRef']   = $calendar->getDocRef();
                $text               = sprintf($translate("txt-view-calendar-item-%s"), $calendar->getCalendar());
                break;
            case 'view-community':
                $router = 'community/calendar/calendar';
                $text   = sprintf($translate("txt-view-calendar-%s"), $calendar);
                break;
            case 'view-admin':
                $router = 'zfcadmin/calendar-manager/calendar';
                $text   = sprintf($translate("txt-view-calendar-%s"), $calendar);
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $action, __CLASS__));
        }

        $params['id']    = $calendar->getId();
        $params['which'] = $which;

        $classes     = array();
        $linkContent = array();

        switch ($show) {
            case 'icon':
                if ($action === 'edit') {
                    $linkContent[] = '<i class="icon-pencil"></i>';
                } elseif ($action === 'delete') {
                    $linkContent[] = '<i class="icon-remove"></i>';
                } else {
                    $linkContent[] = '<i class="icon-info-sign"></i>';
                }
                break;
            case 'button':
                $linkContent[] = '<i class="icon-pencil icon-white"></i> ' . $text;
                $classes[]     = "btn btn-primary";
                break;
            case 'name':
                $linkContent[] = $calendar->getCalendar();
                break;
            case 'text-which-tab':
                $linkContent[] = ucfirst($which);
                break;
            case 'alternativeShow':

                $linkContent[] = $alternativeShow;
                break;
            default:
                $linkContent[] = $calendar;
                break;
        }

        $uri = '<a href="%s" title="%s" class="%s">%s</a>';

        return sprintf(
            $uri,
            $serverUrl->__invoke() . $url($router, $params),
            $text,
            implode($classes),
            implode($linkContent)
        );
    }
}
