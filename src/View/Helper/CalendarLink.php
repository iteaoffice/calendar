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
use Contact\Entity\Contact;
use General\ValueObject\Link\Link;
use General\View\Helper\AbstractLink;
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
        Project $project = null,
        Contact $contact = null
    ): string {
        $calendar ??= new Calendar();

        if (!$this->hasAccess($calendar, Assertion\Calendar::class, $action)) {
            return '';
        }

        $routeParams = [];
        $showOptions = [];
        if (!$calendar->isEmpty()) {
            $routeParams['id'] = $calendar->getId();
            $routeParams['docRef'] = $calendar->getDocRef();
            $showOptions['name'] = $calendar->getCalendar();
        }

        $routeParams['which'] = $which;

        if (null !== $project) {
            $routeParams['project'] = $project->getId();
        }
        if (null !== $contact) {
            $routeParams['contactId'] = $contact->getId();
        }

        switch ($action) {
            case 'edit':
                $linkParams = [
                    'icon' => 'fa-pencil-square-o',
                    'route' => 'zfcadmin/calendar/edit',
                    'text' => $showOptions[$show]
                        ?? sprintf($this->translator->translate('txt-edit-calendar-%s'), $calendar)
                ];
                break;
            case 'overview':
                $linkParams = [
                    'icon' => 'fa-calendar',
                    'route' => 'community/calendar/overview',
                    'text' => $showOptions[$show]
                        ?? sprintf($this->translator->translate('txt-%s-events'), ucfirst($which))
                ];
                break;
            case 'contact':
                $linkParams = [
                    'icon' => 'fa-calendar',
                    'route' => 'community/calendar/contact',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-view-review-invitations')
                ];
                break;
            case 'review-calendar':
                $linkParams = [
                    'icon' => 'fa-calendar',
                    'route' => 'community/calendar/review-calendar',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-view-review-calendar')
                ];
                break;
            case 'download-review-calendar':
                $linkParams = [
                    'icon' => 'fa-download',
                    'route' => 'community/calendar/download-review-calendar',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-download-review-calendar')
                ];
                break;
            case 'select-attendees':
                $linkParams = [
                    'icon' => 'fa-user-plus',
                    'route' => 'community/calendar/select-attendees',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-select-attendees-from-project')
                ];
                break;
            case 'send-message':
                $linkParams = [
                    'icon' => 'fa-envelope-o',
                    'route' => 'community/calendar/send-message',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-send-message-to-attendees')
                ];
                break;
            case 'download-binder':
                $linkParams = [
                    'icon' => 'fa-file-archive-o',
                    'route' => 'community/calendar/download-binder',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-download-binder')
                ];
                break;
            case 'presence-list':
                $linkParams = [
                    'icon' => 'fa-download',
                    'route' => 'community/calendar/presence-list',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-download-presence-list')
                ];
                break;
            case 'signature-list':
                $linkParams = [
                    'icon' => 'fa-download',
                    'route' => 'community/calendar/signature-list',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-download-signature-list')
                ];
                break;
            case 'overview-admin':
                $linkParams = [
                    'icon' => 'fa-calendar',
                    'route' => 'zfcadmin/calendar/overview',
                    'text' => $showOptions[$show]
                        ?? sprintf($this->translator->translate('txt-%s-events'), ucfirst($which))
                ];
                break;
            case 'view-community':
                $linkParams = [
                    'icon' => 'fa-calendar',
                    'route' => 'community/calendar/calendar',
                    'text' => $showOptions[$show]
                        ?? sprintf($this->translator->translate('txt-view-calendar-%s'), $calendar)
                ];
                break;
            case 'view-admin':
                $linkParams = [
                    'icon' => 'fa-calendar',
                    'route' => 'zfcadmin/calendar/calendar',
                    'text' => $showOptions[$show]
                        ?? sprintf($this->translator->translate('txt-view-calendar-%s'), $calendar)
                ];
                break;
            case 'edit-attendees-admin':
                $linkParams = [
                    'icon' => 'fa-user-plus',
                    'route' => 'zfcadmin/calendar/select-attendees',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-select-attendees')
                ];
                break;
            case 'add-contact':
                $linkParams = [
                    'icon' => 'fa-user-plus',
                    'route' => 'zfcadmin/calendar/add-contact',
                    'text' => $showOptions[$show]
                        ?? $this->translator->translate('txt-add-contact-to-calendar')
                ];
                break;
            case 'new':
                $text = $this->translator->translate('txt-add-calendar-item');

                if (null !== $project) {
                    $text = sprintf($this->translator->translate('txt-review-meeting-for-%s'), $project);
                }

                $linkParams = [
                    'icon' => 'fa-calendar-plus-o',
                    'route' => 'zfcadmin/calendar/new',
                    'text' => $showOptions[$show] ?? $text
                ];

                break;
        }

        $linkParams['action'] = $action;
        $linkParams['show'] = $show;
        $linkParams['routeParams'] = $routeParams;

        return $this->parse(Link::fromArray($linkParams));
    }
}
