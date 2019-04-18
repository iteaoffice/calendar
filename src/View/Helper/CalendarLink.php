<?php

/**
 * Jield copyright message placeholder.
 *
 * @category    Admin
 *
 * @author      Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2004-2015 Jield (http://jield.nl)
 */

declare(strict_types=1);

namespace Calendar\View\Helper;

use Calendar\Acl\Assertion;
use Calendar\Entity\Calendar;
use Project\Entity\Project;
use function ucfirst;

/**
 * Class CalendarLink
 *
 * @package Calendar\View\Helper
 */
final class CalendarLink extends AbstractLink
{
    public function __invoke(
        Calendar $calendar = null,
        string $action = 'view',
        string $show = 'name',
        ?string $which = 'all',
        Project $project = null
    ): string {
        $this->reset();

        if (!$this->hasAccess($calendar ?? new Calendar(), Assertion\Calendar::class, $action)) {
            return '';
        }

        $this->extractLinkContentFromEntity($calendar, ['calendar']);
        $this->extractRouterParams($calendar, ['id', 'docRef']);
        $this->addRouteParam('which', $which);

        if (null !== $calendar) {
            $this->addShowOption('name', $calendar->getCalendar());
        }

        if (null !== $project) {
            $this->addRouteParam('project', $project->getId());
        }

        $this->parseAction($action, $which, $calendar, $project);

        return $this->createLink($show);
    }

    private function parseAction(string $action, ?string $which, ?Calendar $calendar, ?Project $project): void
    {
        $this->action = $action;

        switch ($action) {
            case 'edit':
                $this->setRouter('zfcadmin/calendar/edit');
                $this->setText(sprintf($this->translate('txt-edit-calendar-%s'), $calendar));
                break;
            case 'overview':
                $this->setRouter('community/calendar/overview');
                $this->setText(sprintf($this->translate('txt-%s-events'), ucfirst($which)));
                break;
            case 'contact':
                $this->setRouter('community/calendar/contact');
                $this->setText($this->translate('txt-view-review-invitations'));
                break;
            case 'review-calendar':
                $this->setRouter('community/calendar/review-calendar');
                $this->setText($this->translate('txt-view-review-calendar'));
                break;
            case 'download-review-calendar':
                $this->setRouter('community/calendar/download-review-calendar');
                $this->setText($this->translate('txt-view-download-review-calendar'));
                break;
            case 'select-attendees':
                $this->setRouter('community/calendar/select-attendees');
                $this->setText($this->translate('txt-select-attendees-from-project'));
                break;
            case 'send-message':
                $this->setRouter('community/calendar/send-message');
                $this->setText($this->translate('txt-send-message-to-attendees'));
                break;
            case 'download-binder':
                $this->setRouter('community/calendar/download-binder');
                $this->setText($this->translate('txt-download-binder'));
                break;
            case 'presence-list':
                $this->setRouter('community/calendar/presence-list');
                $this->setText($this->translate('txt-download-presence-list'));
                break;
            case 'signature-list':
                $this->setRouter('community/calendar/signature-list');
                $this->setText($this->translate('txt-download-signature-list'));
                break;
            case 'overview-admin':
                $this->setRouter('zfcadmin/calendar/overview');
                $this->setText(sprintf($this->translate('txt-%s-events'), ucfirst($which)));
                break;
            case 'view-community':
                $this->setRouter('community/calendar/calendar');
                $this->setText(sprintf($this->translate('txt-view-calendar-%s'), $calendar));
                break;
            case 'view-admin':
                $this->setRouter('zfcadmin/calendar/calendar');
                $this->setText(sprintf($this->translate('txt-view-calendar-%s'), $calendar));
                break;
            case 'edit-attendees-admin':
                $this->setRouter('zfcadmin/calendar/select-attendees');
                $this->setText(sprintf($this->translate('txt-select-attendees-for-calendar-%s'), $calendar));
                break;
            case 'new':
                $this->setRouter('zfcadmin/calendar/new');
                $this->setText(sprintf($this->translate('txt-add-calendar-item')));
                if (null !== $project) {
                    $this->setText(sprintf($this->translate('txt-review-meeting-for-%s'), $project));
                }

                break;
        }
    }
}
