<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category   Calendar
 * @package    View
 * @subpackage Helper
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
declare(strict_types=1);

namespace Calendar\View\Helper;

use Calendar\Entity\Calendar;
use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use Content\Entity\Content;
use Content\Entity\Param;
use ZfcTwig\View\TwigRenderer;

/**
 * Class CalendarHandler
 *
 * @package Calendar\View\Helper
 */
class CalendarHandler extends AbstractViewHelper
{
    /**
     * @var int
     */
    protected $year;
    /**
     * @var int
     */
    protected $type;
    /**
     * @var Calendar
     */
    protected $calendar;
    /**
     * @var int
     */
    protected $limit = 25;

    /**
     * @param Content $content
     *
     * @return string
     */
    public function __invoke(Content $content)
    {
        $this->extractContentParam($content);

        switch ($content->getHandler()->getHandler()) {
            case 'calendar_item':
                if (\is_null($this->getCalendar())) {
                    $this->getServiceManager()->get('response')->setStatusCode(404);

                    return 'The selected calendar item cannot be found';
                }

                $this->getHelperPluginManager()->get('headtitle')->append($this->translate("txt-calendar"));
                $this->getHelperPluginManager()->get('headtitle')->append((string)$this->getCalendar()->getCalendar());
                $this->getHelperPluginManager()->get('headmeta')
                    ->setProperty('og:type', $this->translate("txt-calendar"));
                $this->getHelperPluginManager()->get('headmeta')
                    ->setProperty('og:title', $this->getCalendar()->getCalendar());
                $this->getHelperPluginManager()->get('headmeta')
                    ->setProperty('og:description', $this->getCalendar()->getDescription());
                /**
                 * @var $calendarLink CalendarLink
                 */
                $calendarLink = $this->getHelperPluginManager()->get('calendarLink');
                $this->getHelperPluginManager()->get('headmeta')
                    ->setProperty('og:url', $calendarLink($this->getCalendar(), 'view', 'social'));

                return $this->parseCalendarItem($this->getCalendar());
            case 'calendar':
                $this->getHelperPluginManager()->get('headtitle')->append($this->translate("txt-calendar"));

                return $this->parseCalendar($this->getYear(), $this->getLimit());
            case 'calendar_upcoming':
                $this->getHelperPluginManager()->get('headtitle')->append($this->translate("txt-calendar"));

                return $this->parseUpcomingCalendar();
            case 'calendar_past':
                $this->getHelperPluginManager()->get('headtitle')->append($this->translate("txt-past-events"));

                return $this->parsePastCalendar($this->getYear(), $this->getType(), $this->getLimit());
            case 'calendar_small':
                return $this->parseCalendarSmall($this->getLimit());
            case 'calendar_year_selector':
                return $this->parseYearSelector($this->getYear());
            default:
                return sprintf(
                    "No handler available for <code>%s</code> in class <code>%s</code>",
                    $content->getHandler()->getHandler(),
                    __CLASS__
                );
        }
    }

    /**
     * @param Content $content
     */
    public function extractContentParam(Content $content)
    {
        /**
         * Go over the handler params and try to see if it is hardcoded or just set via the route
         */
        foreach ($content->getHandler()->getParam() as $parameter) {
            switch ($parameter->getParam()) {
                case 'docRef':
                    $this->setCalendarByDocRef($this->findParamValueFromContent($content, $parameter));
                    break;
                case 'limit':
                    $this->setLimit($this->findParamValueFromContent($content, $parameter));
                    break;
                case 'type':
                    $this->setType($this->findParamValueFromContent($content, $parameter));
                    break;
                case 'year':
                    $this->setYear($this->findParamValueFromContent($content, $parameter));
                    break;
            }
        }
    }

    /**
     * Set the newsService based on the DocRef
     *
     * @param $docRef
     *
     * @return Calendar
     */
    public function setCalendarByDocRef($docRef)
    {
        $this->setCalendar($this->getCalendarService()->findCalendarByDocRef($docRef));
    }

    /**
     * @return CalendarService
     */
    public function getCalendarService()
    {
        return $this->getServiceManager()->get(CalendarService::class);
    }

    /**
     * @param Content $content
     * @param Param $param
     *
     * @return null|string
     */
    private function findParamValueFromContent(Content $content, Param $param)
    {

        //Try first to see if the param can be found from the route (rule 1)
        if (!\is_null($this->getRouteMatch()->getParam($param->getParam()))) {
            return $this->getRouteMatch()->getParam($param->getParam());
        }

        //If it cannot be found, try to find it from the docref (rule 2)
        foreach ($content->getContentParam() as $contentParam) {
            if ($contentParam->getParameter() === $param && !empty($contentParam->getParameterId())) {
                return $contentParam->getParameterId();
            }
        }

        //If not found, take rule 3
        return null;
    }

    /**
     * @return Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @param Calendar $calendar
     *
     * @return CalendarHandler
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * Show the details of 1 calendar item
     *
     * @param Calendar $calendar
     *
     * @return string
     */
    public function parseCalendarItem(Calendar $calendar): string
    {
        return $this->getRenderer()->render('calendar/partial/entity/calendar', ['calendar' => $calendar]);
    }

    /**
     * @return TwigRenderer
     */
    public function getRenderer(): TwigRenderer
    {
        return $this->getServiceManager()->get('ZfcTwigRenderer');
    }

    /**
     * @param $year
     * @param $limit
     *
     * @return null|string
     */
    public function parseCalendar($year, $limit)
    {
        $which = CalendarService::WHICH_FINAL;

        if (\is_null($year)) {
            $which = CalendarService::WHICH_UPCOMING;
        } else {
            $limit = 999;
        }

        $calendarItems = $this->getCalendarService()->findCalendarItems(
            $which,
            $this->getServiceManager()->get('Application\Authentication\Service')->getIdentity(),
            $year
        )
            ->setMaxResults($limit)->getResult();

        return $this->getRenderer()->render(
            'cms/calendar/calendar-past',
            [
                'calendarItems' => $calendarItems,
                'which'         => $which,
            ]
        );
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
     *
     * @return CalendarHandler
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return null|string
     */
    public function parseUpcomingCalendar()
    {
        $calendarItems = $this->getCalendarService()->findCalendarItems(CalendarService::WHICH_UPCOMING, null)
            ->getResult();

        return $this->getRenderer()->render(
            'cms/calendar/calendar-upcoming',
            ['calendarItems' => $calendarItems]
        );
    }

    /**
     * @param $year
     * @param $type
     * @param $limit
     *
     * @return null|string
     */
    public function parsePastCalendar($year, $type, $limit): string
    {
        $calendarItems = $this->getCalendarService()->findCalendarItems(
            CalendarService::WHICH_PAST,
            null,
            $year,
            $type
        )->setMaxResults($limit)->getResult();

        return $this->getRenderer()->render(
            'cms/calendar/calendar-past',
            ['calendarItems' => $calendarItems]
        );
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param $limit
     *
     * @return null|string
     */
    public function parseCalendarSmall($limit)
    {
        $calendarItems = $this->getCalendarService()->findCalendarItems(CalendarService::WHICH_ON_HOMEPAGE)
            ->setMaxResults($limit)->getResult();

        return $this->getRenderer()->render(
            'cms/calendar/calendar-small',
            ['calendarItems' => $calendarItems, 'calendarService' => $this->getCalendarService()]
        );
    }

    /**
     * Create a list of calls
     *
     * @param int $year
     *
     * @return string
     */
    public function parseYearSelector($year)
    {
        $yearSpan = $this->getCalendarService()->findMinAndMaxYear();

        $years = range($yearSpan->minYear, $yearSpan->maxYear);

        return $this->getRenderer()->render(
            'calendar/partial/year-selector',
            [
                'years'        => $years,
                'selectedYear' => $year,
            ]
        );
    }

    /**
     * @param $id
     *
     * @return CalendarService
     */
    public function setCalendarById($id)
    {
        $this->setCalendar($this->getCalendarService()->findCalendarById($id));
    }
}
