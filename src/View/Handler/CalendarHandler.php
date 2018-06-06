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
use Calendar\Service\CalendarService;
use Calendar\View\Helper\CalendarLink;
use Contact\Entity\Contact;
use Content\Entity\Content;
use Content\Navigation\Service\UpdateNavigationService;
use Zend\Authentication\AuthenticationService;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\Application;
use Zend\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;

/**
 * Class CalendarHandler
 *
 * @package Calendar\View\Handler
 */
class CalendarHandler extends AbstractHandler
{
    /**
     * Limit
     */
    public const LIMIT = 10;

    /**
     * @var CalendarService
     */
    protected $calendarService;

    /**
     * BlogHandler constructor.
     *
     * @param Application             $application
     * @param HelperPluginManager     $helperPluginManager
     * @param TwigRenderer            $renderer
     * @param AuthenticationService   $authenticationService
     * @param UpdateNavigationService $updateNavigationService
     * @param CalendarService         $calendarService
     * @param TranslatorInterface     $translator
     */
    public function __construct(
        Application $application,
        HelperPluginManager $helperPluginManager,
        TwigRenderer $renderer,
        AuthenticationService $authenticationService,
        UpdateNavigationService $updateNavigationService,
        CalendarService $calendarService,
        TranslatorInterface $translator
    ) {
        parent::__construct(
            $application,
            $helperPluginManager,
            $renderer,
            $authenticationService,
            $updateNavigationService,
            $translator
        );

        $this->calendarService = $calendarService;
    }

    /**
     * @param Content $content
     *
     * @return null|string
     * @throws \Exception
     */
    public function __invoke(Content $content): ?string
    {
        $params = $this->extractContentParam($content);

        $calendar = $this->getCalendarByParams($params);

        switch ($content->getHandler()->getHandler()) {
            case 'calendar_item':
                if (null === $calendar) {
                    $this->response->setStatusCode(404);

                    return 'The selected calendar item cannot be found';
                }

                $this->getHeadTitle()->append($this->translate('txt-calendar-item'));
                $this->getHeadTitle()->append($calendar->getCalendar());

                $this->getHeadMeta()->setProperty('og:type', $this->translate("txt-calendar"));
                $this->getHeadMeta()->setProperty('og:title', $calendar->getCalendar());
                $this->getHeadMeta()->setProperty('og:url', $this->getCalendarLink()($calendar, 'view', 'social'));

                return $this->parseCalendarItem($calendar);
            case 'calendar':
                $this->getHeadTitle()->append($this->translate('txt-calendar'));

                return $this->parseCalendar(
                    $params['year'],
                    $params['limit'],
                    $this->authenticationService->getIdentity()
                );
            case 'calendar_upcoming':
                $this->getHeadTitle()->append($this->translate('txt-calendar'));

                return $this->parseUpcomingCalendar(
                    $params['limit'],
                    $this->authenticationService->getIdentity()
                );
            case 'calendar_past':
                $this->getHeadTitle()->append($this->translate('txt-calendar'));

                return $this->parseCalendar(
                    $params['year'],
                    $params['limit'],
                    $this->authenticationService->getIdentity()
                );
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

    /**
     * @param array $params
     *
     * @return Calendar|null
     */
    private function getCalendarByParams(array $params): ?Calendar
    {
        $calendar = null;
        if (null !== $params['id']) {
            $calendar = $this->calendarService->findCalendarById((int)$params['id']);
        }

        if (null !== $params['docRef']) {
            $calendar = $this->calendarService->findCalendarByDocRef($params['docRef']);
        }

        return $calendar;
    }

    /**
     * @return CalendarLink
     */
    public function getCalendarLink(): CalendarLink
    {
        return $this->helperPluginManager->get(CalendarLink::class);
    }

    /**
     * @param Calendar $calendar
     *
     * @return string
     */
    public function parseCalendarItem(Calendar $calendar): string
    {
        return $this->renderer->render(
            'cms/calendar/calendar-item',
            [
                'calendar' => $calendar,
            ]
        );
    }

    /**
     * @param int     $year
     * @param int     $limit
     * @param Contact $contact
     * @param string  $which
     *
     * @return null|string
     */
    public function parseCalendar(
        ?int $year,
        ?int $limit = self::LIMIT,
        Contact $contact = null,
        string $which = CalendarService::WHICH_FINAL
    ): string {
        if (null === $year) {
            $which = CalendarService::WHICH_UPCOMING;
        }

        $calendarItems = $this->calendarService->findCalendarItems(
            $which,
            $contact,
            $year
        )
            ->setMaxResults($limit)->getResult();

        $yearSpan = $this->calendarService->findMinAndMaxYear();
        $routeParams = $this->routeMatch->getParams();

        return $this->renderer->render(
            'cms/calendar/calendar-past',
            [
                'calendarItems'    => $calendarItems,
                'which'            => $which,
                'years'            => \range($yearSpan->minYear, $yearSpan->maxYear),
                'matchedRouteName' => $this->routeMatch->getMatchedRouteName(),
                'routeParams'      => $routeParams,
                'selectedYear'     => $this->routeMatch->getParam('year')
            ]
        );
    }

    /**
     * @param int|null     $limit
     * @param Contact|null $contact
     * @param string       $which
     *
     * @return null|string
     */
    public function parseUpcomingCalendar(
        ?int $limit = self::LIMIT,
        Contact $contact = null,
        string $which = CalendarService::WHICH_UPCOMING
    ): string {
        $calendarItems = $this->calendarService->findCalendarItems(
            $which,
            $contact,
            null
        )
            ->setMaxResults($limit)->getResult();

        $yearSpan = $this->calendarService->findMinAndMaxYear();
        $routeParams = $this->routeMatch->getParams();

        return $this->renderer->render(
            'cms/calendar/calendar-upcoming',
            [
                'calendarItems'    => $calendarItems,
                'which'            => $which,
                'years'            => \range($yearSpan->minYear, $yearSpan->maxYear),
                'matchedRouteName' => $this->routeMatch->getMatchedRouteName(),
                'routeParams'      => $routeParams,
                'selectedYear'     => $this->routeMatch->getParam('year')
            ]
        );
    }

    /**
     * @param int $limit
     *
     * @return string
     */
    public function parseCalendarSmall(int $limit = self::LIMIT): string
    {
        $calendarItems = $this->calendarService->findCalendarItems(CalendarService::WHICH_ON_HOMEPAGE)
            ->setMaxResults($limit)->getResult();

        return $this->renderer->render(
            'cms/calendar/calendar-small',
            [
                'calendarItems'   => $calendarItems,
                'calendarService' => $this->calendarService
            ]
        );
    }
}
