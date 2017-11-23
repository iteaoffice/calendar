<?php

/**
 * ITEA Office all rights reserved
 *
 * @category    Calendar
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\View\Helper;

use Calendar\Acl\Assertion\Calendar as CalendarAssertion;
use Calendar\Entity\Calendar;
use Calendar\Service\CalendarService;
use Project\Entity\Project;

/**
 * Create a link to an calendar.
 *
 * @category    Calendar
 */
class CalendarLink extends LinkAbstract
{
    /**
     * @param Calendar|null $calendar
     * @param string $action
     * @param string $show
     * @param string $which
     * @param null $alternativeShow
     * @param null $year
     * @param Project|null $project
     * @param null $classes
     * @return string
     */
    public function __invoke(
        Calendar $calendar = null,
        $action = 'view',
        $show = 'name',
        $which = CalendarService::WHICH_UPCOMING,
        $alternativeShow = null,
        $year = null,
        Project $project = null,
        $classes = null
    ): string {
        $this->classes = [];

        $this->setCalendar($calendar);
        $this->setAction($action);
        $this->setShow($show);
        $this->setWhich($which);
        $this->setYear($year);
        $this->setProject($project);
        $this->setAlternativeShow($alternativeShow);

        $this->addClasses($classes);

        // Set the non-standard options needed to give an other link value
        $this->setShowOptions([
            'alternativeShow' => $this->getAlternativeShow(),
            'text-which-tab'  => ucfirst((string) $this->getWhich()),
            'name'            => $this->getCalendar()->getCalendar(),
        ]);

        /*
         * Check the access to the object
         */
        if (!$this->hasAccess($this->getCalendar(), CalendarAssertion::class, $this->getAction())) {
            return '';
        }

        $this->addRouterParam('id', $this->getCalendar()->getId());
        $this->addRouterParam('calendar', $this->getCalendar()->getId());
        $this->addRouterParam('docRef', $this->getCalendar()->getDocRef());
        $this->addRouterParam('project', $this->getProject()->getId());
        $this->addRouterParam('which', $this->getWhich());
        $this->addRouterParam('year', $this->getYear());

        return $this->createLink();
    }

    /**
     * Parse te action and fill the correct parameters.
     */
    public function parseAction(): void
    {
        switch ($this->getAction()) {
            case 'edit':
                $this->setRouter('zfcadmin/calendar-manager/edit');
                $this->setText(sprintf($this->translate("txt-edit-calendar-%s"), $this->getCalendar()));
                break;
            case 'list':
                /*
                 * Push the docRef in the params array
                 */
                $this->setRouter('route-content_entity_node');
                switch ($this->getWhich()) {
                    case CalendarService::WHICH_UPCOMING:
                        $this->addRouterParam('docRef', 'upcoming-events');
                        $this->setText($this->translate("txt-upcoming-events"));
                        break;
                    case CalendarService::WHICH_ON_HOMEPAGE:
                        $this->addRouterParam('docRef', 'events');
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
            case 'download-binder':
                $this->setRouter('community/calendar/download-binder');
                $this->setText($this->translate("txt-download-binder"));
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
                $this->setRouter('route-' . $this->getCalendar()->get("underscore_entity_name"));
                $this->addRouterParam('calendar', $this->getCalendar()->getId());
                $this->addRouterParam('docRef', $this->getCalendar()->getDocRef());
                $this->setText(
                    sprintf(
                        $this->translate("txt-view-calendar-item-%s"),
                        $this->getCalendar()->getCalendar()
                    )
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
            case 'edit-attendees-admin':
                $this->setRouter('zfcadmin/calendar-manager/select-attendees');
                $this->setText(sprintf($this->translate("txt-select-attendees-for-calendar-%s"), $this->getCalendar()));
                break;
            case 'new':
                $this->setRouter('zfcadmin/calendar-manager/new');
                if (\is_null($this->getProject())) {
                    $this->setText(sprintf($this->translate("txt-add-calendar-item")));
                } else {
                    $this->setText(sprintf($this->translate("txt-review-meeting-for-%s"), $this->getProject()));
                }

                break;
            default:
                throw new \Exception(sprintf("%s is an incorrect action for %s", $this->getAction(), __CLASS__));
        }
    }
}
