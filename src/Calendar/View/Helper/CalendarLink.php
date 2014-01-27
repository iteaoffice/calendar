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
     * @param \Calendar\Entity\Calendar $subArea
     * @param                         $action
     * @param                         $show
     *
     * @return string
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function __invoke(Entity\Calendar $subArea = null, $action = 'view', $show = 'name')
    {
        $translate = $this->view->plugin('translate');
        $url       = $this->view->plugin('url');
        $serverUrl = $this->view->plugin('serverUrl');
        $isAllowed = $this->view->plugin('isAllowed');

        if (!$isAllowed('calendar', $action)) {
            if ($action === 'view' && $show === 'name') {
                return $subArea;
            }

            return '';
        }

        switch ($action) {
            case 'new':
                $router  = 'zfcadmin/calendar-manager/new';
                $text    = sprintf($translate("txt-new-area"));
                $subArea = new Entity\Calendar();
                break;
            case 'edit':
                $router = 'zfcadmin/calendar-manager/edit';
                $text   = sprintf($translate("txt-edit-calendar-%s"), $subArea);
                break;
            case 'view':
                $router = 'calendar/calendar';
                $text   = sprintf($translate("txt-view-calendar-%s"), $subArea);
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $action, __CLASS__));
        }

        if (is_null($subArea)) {
            throw new \RuntimeException(
                sprintf(
                    "Area needs to be an instance of %s, %s given in %s",
                    "Calendar\Entity\Calendar",
                    get_class($subArea),
                    __CLASS__
                )
            );
        }

        $params = array(
            'id'     => $subArea->getId(),
            'entity' => 'calendar'
        );

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
                $linkContent[] = $subArea->getName();
                break;
            default:
                $linkContent[] = $subArea;
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
