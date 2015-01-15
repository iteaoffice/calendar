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

use Calendar\Acl\Assertion\Calendar as CalendarAssertion;
use Calendar\Entity\Calendar;
use Calendar\Service\CalendarService;

/**
 * Create a link to an calendar
 *
 * @category    Calendar
 * @package     View
 * @subpackage  Helper
 */
class CalendarLink extends LinkAbstract
{
    /**
     * @var Calendar
     */
    protected $calendar;
    /**
     * @var int
     */
    protected $year;
    /**
     * @var
     */
    protected $which;

    /**
     * @param Calendar $calendar
     * @param string   $action
     * @param string   $show
     * @param string   $which
     * @param null     $alternativeShow
     * @param null     $year
     *
     * @return string
     * @throws \Exception
     */
    public function __invoke(
        Calendar $calendar = null,
        $action = 'view',
        $show = 'name',
        $which = CalendarService::WHICH_UPCOMING,
        $alternativeShow = null,
        $year = null
    ) {
        $this->setCalendar($calendar);
        $this->setAction($action);
        $this->setShow($show);
        $this->setWhich($which);
        $this->setYear($year);
        $this->setAlternativeShow($alternativeShow);
        /**LiLik
         * Set the non-standard options needed to give an other link value
         */
        $this->setShowOptions(
            [
                'alternativeShow' => $this->getAlternativeShow(),
                'text-which-tab'  => ucfirst($this->getWhich()),
                'name'            => $this->getCalendar()->getCalendar(),
            ]
        );

        /**
         * Check the access to the object
         */
        if (!$this->hasAccess(
            $this->getCalendar(),
            CalendarAssertion::class,
            $this->getAction()
        )
        ) {
            return '';
        }

        $this->addRouterParam('entity', 'calendar');
        $this->addRouterParam('id', $this->getCalendar()->getId());
        $this->addRouterParam('calendar', $this->getCalendar()->getId());
        $this->addRouterParam('docRef', $this->getCalendar()->getDocRef());
        $this->addRouterParam('which', $this->getWhich());
        $this->addRouterParam('year', $this->getYear());

        return $this->createLink();
    }

    /**
     * Parse te action and fill the correct parameters
     */
    public function parseAction()
    {
        switch ($this->getAction()) {
            case 'edit':
                $this->setRouter('zfcadmin/calendar-manager/edit');
                $this->setText(sprintf($this->translate("txt-edit-calendar-%s"), $this->getCalendar()));
                break;
            case 'list':
                /**
                 * Push the docRef in the params array
                 */
                $this->setRouter('route-content_entity_node');
                switch ($this->getWhich()) {
                    case CalendarService::WHICH_UPCOMING:
                        $this->addRouterParam('docRef', 'upcoming-events');
                        $this->setText($this->translate("txt-upcoming-events"));
                        break;
                    case CalendarService::WHICH_PAST:
                        $this->addRouterParam('docRef', 'past-events');
                        $this->setText($this->translate("txt-past-events"));
                        break;
                }
                break;
            case 'overview':
                $this->setRouter('community/calendar/overview');
                $this->setText($this->translate("txt-view-full-calendar"));
                break;
            case 'contact':
                $this->setRouter('community/calendar/contact');
                $this->setText($this->translate("txt-view-review-invitations"));
                break;
            case 'review-calendar':
                $this->setRouter('community/calendar/review-calendar');
                $this->setText($this->translate("txt-view-review-calendar"));
                break;
            case 'download-review-calendar':
                $this->setRouter('community/calendar/download-review-calendar');
                $this->setText($this->translate("txt-view-download-review-calendar"));
                break;
            case 'select-attendees':
                $this->setRouter('community/calendar/select-attendees');
                $this->setText($this->translate("txt-select-attendees-from-project"));
                break;
            case 'send-message':
                $this->setRouter('community/calendar/send-message');
                $this->setText($this->translate("txt-send-message-to-attendees"));
                break;
            case 'presence-list':
                $this->setRouter('community/calendar/presence-list');
                $this->setText($this->translate("txt-download-presence-list"));
                break;
            case 'overview-admin':
                $this->setRouter('zfcadmin/calendar-manager/overview');
                $this->setText(sprintf($this->translate("txt-view-calendar-%s"), $this->getCalendar()));
                break;
            case 'view':
                $this->setRouter('route-'.$this->getCalendar()->get("underscore_full_entity_name"));
                $this->addRouterParam('calendar', $this->getCalendar()->getId());
                $this->addRouterParam('docRef', $this->getCalendar()->getDocRef());
                $this->setText(
                    sprintf($this->translate("txt-view-calendar-item-%s"), $this->getCalendar()->getCalendar())
                );
                break;
            case 'view-community':
                $this->setRouter('community/calendar/calendar');
                $this->setText(sprintf($this->translate("txt-view-calendar-%s"), $this->getCalendar()));
                break;
            case 'view-admin':
                $this->setRouter('zfcadmin/calendar-manager/calendar');
                $this->setText(sprintf($this->translate("txt-view-calendar-%s"), $this->getCalendar()));
                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
    }

    /**
     * @return Calendar
     */
    public function getCalendar()
    {
        if (is_null($this->calendar)) {
            $this->calendar = new Calendar();
        }

        return $this->calendar;
    }

    /**
     * @param Calendar $calendar
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return mixed
     */
    public function getWhich()
    {
        return $this->which;
    }

    /**
     * @param mixed $which
     */
    public function setWhich($which)
    {
        $this->which = $which;
    }
}
