<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category   Calendar
 * @package    View
 * @subpackage Helper
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */
namespace Calendar\View\Helper;

use Calendar\Entity\Calendar;
use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use Content\Entity\Content;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use ZfcTwig\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;

/**
 * Class CountryHandler
 *
 * @package Country\View\Helper
 */
class CalendarHandler extends AbstractHelper
{
    /**
     * @var HelperPluginManager
     */
    protected $serviceLocator;
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
    protected $limit = 5;

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
                if (is_null($this->getCalendar())) {
                    $this->getServiceLocator()->get("response")->setStatusCode(404);

                    return ("The selected calendar item cannot be found");
                }

                $this->serviceLocator->get('headtitle')->append($this->translate("txt-calendar"));
                $this->serviceLocator->get('headtitle')->append((string)$this->getCalendar()->getCalendar());
                $this->serviceLocator->get('headmeta')->setProperty('og:type', $this->translate("txt-calendar"));
                $this->serviceLocator->get('headmeta')->setProperty('og:title', $this->getCalendar()->getCalendar());
                $this->serviceLocator->get('headmeta')
                    ->setProperty('og:description', $this->getCalendar()->getDescription());
                /**
                 * @var $calendarLink CalendarLink
                 */
                $calendarLink = $this->serviceLocator->get('calendarLink');
                $this->serviceLocator->get('headmeta')
                    ->setProperty('og:url', $calendarLink($this->getCalendar()->getCalendar(), 'view', 'social'));

                return $this->parseCalendarItem($this->getCalendar());
            case 'calendar':
                $this->serviceLocator->get('headtitle')->append($this->translate("txt-calendar"));

                return $this->parseCalendar($this->getLimit());
            case 'calendar_past':
                $this->serviceLocator->get('headtitle')->append($this->translate("txt-past-events"));

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
        //Give default the docRef to the handler, this does not harm
        if (!is_null($this->getRouteMatch()->getParam('docRef'))) {
            $this->setCalendarByDocRef($this->getRouteMatch()->getParam('docRef'));
        }

        foreach ($content->getContentParam() as $param) {
            /**
             * When the parameterId is 0 (so we want to get the article from the URL
             */
            switch ($param->getParameter()->getParam()) {
                case 'docRef':
                    if (!is_null($this->getRouteMatch()->getParam($param->getParameter()->getParam()))) {
                        $this->setCalendarByDocRef($this->getRouteMatch()->getParam('docRef'));
                    }
                    break;
                case 'limit':
                    if ('0' === $param->getParameterId()) {
                        $this->setLimit(null);
                    } else {
                        $this->setLimit($param->getParameterId());
                    }
                    break;
                case 'type':
                    $this->setType($param->getParameterId());
                    break;

                case 'year':
                    if (!is_null($year = $this->getRouteMatch()->getParam($param->getParameter()->getParam()))) {
                        $this->setYear($year);
                    } elseif ('0' === $param->getParameterId()) {
                        $this->setYear($this->getModuleOptions()->getDefaultYear());
                    } else {
                        $this->setYear($param->getParameterId());
                    }
                    break;
                default:
                    $this->setCalendarById($param->getParameterId());
                    break;
            }
        }
    }

    /**
     * @return RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->getServiceLocator()->get('application')->getMvcEvent()->getRouteMatch();
    }

    /**
     * Get the service locator.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator->getServiceLocator();
    }

    /**
     * Set the service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AbstractHelper
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
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
        return $this->getServiceLocator()->get(CalendarService::class);
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions()
    {
        return $this->getServiceLocator()->get(ModuleOptions::class);
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

    /**
     * @param $string
     *
     * @return string
     */
    public function translate($string)
    {
        return $this->serviceLocator->get('translate')->__invoke($string);
    }

    /**
     * Show the details of 1 calendar item
     *
     * @param Calendar $calendar
     *
     * @return string
     */
    public function parseCalendarItem(Calendar $calendar)
    {
        return $this->getRenderer()->render('calendar/partial/entity/calendar', ['calendar' => $calendar]);
    }

    /**
     * @return TwigRenderer
     */
    public function getRenderer()
    {
        return $this->getServiceLocator()->get('ZfcTwigRenderer');
    }

    /**
     * @param $limit
     *
     * @return null|string
     */
    public function parseCalendar($limit)
    {
        $calendarItems = $this->getCalendarService()->findCalendarItems(
            CalendarService::WHICH_UPCOMING,
            $this->getServiceLocator()->get('Application\Authentication\Service')->getIdentity()
        )->setMaxResults($limit)
            ->getResult();

        return $this->getRenderer()->render(
            $this->getModuleOptions()->getCalendarUpcomingTemplate(),
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
     * @param $year
     * @param $type
     * @param $limit
     *
     * @return null|string
     */
    public function parsePastCalendar($year, $type, $limit)
    {
        $calendarItems = $this->getCalendarService()->findCalendarItems(
            CalendarService::WHICH_PAST,
            $this->getServiceLocator()->get('Application\Authentication\Service')->getIdentity(),
            $year,
            $type
        )
            ->setMaxResults($limit)->getResult();

        return $this->getRenderer()->render(
            $this->getModuleOptions()->getCalendarPastTemplate(),
            ['calendarItems' => $calendarItems]
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
     * @param $limit
     *
     * @return null|string
     */
    public function parseCalendarSmall($limit)
    {
        $calendarItems = $this->getCalendarService()->findCalendarItems(CalendarService::WHICH_ON_HOMEPAGE)
            ->setMaxResults($limit)->getResult();

        return $this->getRenderer()->render(
            'calendar/partial/list/calendar-small',
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
        /**
         * take the last three years for the calendar
         */
        $years = range(date("Y"), date("Y") - 2);

        return $this->getRenderer()->render('calendar/partial/year-selector', [
            'years'        => $years,
            'selectedYear' => $year,
        ]);
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
}
