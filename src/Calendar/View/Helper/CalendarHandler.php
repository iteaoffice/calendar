<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category   Calendar
 * @package    View
 * @subpackage Helper
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\View\Helper;

use Calendar\Entity\Calendar;
use Calendar\Service\CalendarService;
use Content\Entity\Content;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use ZfcTwig\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;

/**
 * Class CountryHandler
 * @package Country\View\Helper
 */
class CalendarHandler extends AbstractHelper implements ServiceLocatorAwareInterface
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

                if ($this->getCalendarService()->isEmpty()) {
                    $this->getServiceLocator()->get("response")->setStatusCode(404);

                    return ("The selected calendar item cannot be found");
                }

                $this->serviceLocator->get('headtitle')->append($this->translate("txt-calendar"));
                $this->serviceLocator->get('headtitle')->append((string) $this->getCalendarService()->getCalendar());
                $this->serviceLocator->get('headmeta')->setProperty('og:type', $this->translate("txt-calendar"));
                $this->serviceLocator->get('headmeta')->setProperty(
                    'og:title',
                    $this->getCalendarService()->getCalendar()
                );
                $this->serviceLocator->get('headmeta')->setProperty(
                    'og:description',
                    $this->getCalendarService()->getCalendar()->getDescription()
                );
                /**
                 * @var $calendarLink CalendarLink
                 */
                $calendarLink = $this->serviceLocator->get('calendarLink');
                $this->serviceLocator->get('headmeta')->setProperty(
                    'og:url',
                    $calendarLink(
                        $this->getCalendarService()->getCalendar(),
                        'view',
                        'social'
                    )
                );
                return $this->parseCalendarItem($this->getCalendarService()->getCalendar());
            case 'calendar':
                $this->serviceLocator->get('headtitle')->append($this->translate("txt-calendar"));
                return $this->parseCalendar($this->getLimit());
            case 'calendar_past':
                $this->serviceLocator->get('headtitle')->append($this->translate("txt-past-events"));
                return $this->parsePastCalendar($this->getLimit());

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

    public function extractContentParam(Content $content)
    {
        //Give default the docRef to the handler, this does not harm
        if (!is_null($this->getRouteMatch()->getParam('docRef'))) {
            $this->setCalendarDocRef($this->getRouteMatch()->getParam('docRef'));
        }

        foreach ($content->getContentParam() as $param) {
            /**
             * When the parameterId is 0 (so we want to get the article from the URL
             */
            switch ($param->getParameter()->getParam()) {
                case 'docRef':
                    if (!is_null($this->getRouteMatch()->getParam($param->getParameter()->getParam()))) {
                        $this->setCalendarDocRef($this->getRouteMatch()->getParam('docRef'));
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
                        $this->setYear($this->getCalendarService()->getOptions()->getDefaultYear());
                    } else {
                        $this->setYear($param->getParameterId());
                    }
                    break;
                default:
                    $this->setCalendarId($param->getParameterId());
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
    public function setCalendarDocRef($docRef)
    {
        $calendar = $this->getCalendarService()->findCalendarByDocRef($docRef);

        return $this->getCalendarService()->setCalendar($calendar);
    }

    /**
     * @return CalendarService
     */
    public function getCalendarService()
    {
        return $this->getServiceLocator()->get(CalendarService::class);
    }

    /**
     * @param $id
     *
     * @return CalendarService
     */
    public function setCalendarId($id)
    {
        return $this->getCalendarService()->setCalendarId($id);
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
        return $this->getRenderer()->render(
            'calendar/partial/entity/calendar',
            ['calendar' => $calendar]
        );
    }

    /**
     * @return TwigRenderer
     */
    public function getRenderer()
    {
        return $this->getServiceLocator()->get('ZfcTwigRenderer');
    }

    /**
     * Produce a list of upcoming events
     *
     * @return string
     */
    public function parseCalendar()
    {
        $calendarItems = $this->getCalendarService()
            ->findCalendarItems(
                CalendarService::WHICH_UPCOMING,
                $this->getServiceLocator()->get('zfcuser_auth_service')->getIdentity()
            )
            ->setMaxResults((int) $this->getLimit())
            ->getResult();

        return $this->getRenderer()->render(
            $this->getCalendarService()->getOptions()->getCalendarUpcomingTemplate(),
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
     * Produce a list of upcoming events
     *
     * @return string
     */
    public function parsePastCalendar()
    {
        $calendarItems = $this->getCalendarService()
            ->findCalendarItems(
                CalendarService::WHICH_PAST,
                $this->getServiceLocator()->get('zfcuser_auth_service')->getIdentity(),
                $this->getYear(),
                $this->getType()
            )
            ->setMaxResults((int) $this->getLimit())
            ->getResult();

        return $this->getRenderer()->render(
            $this->getCalendarService()->getOptions()->getCalendarPastTemplate(),
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
     */
    public function setYear($year)
    {
        if(is_null($year)){
            $this->year = null;
        } else {
            $this->year = (int)$year;
        }
    }

    /**
     * Create a list of all countries which are active (have projects)
     *
     * @return string
     */
    public function parseCalendarSmall()
    {
        $calendarItems = $this->getCalendarService()
            ->findCalendarItems(CalendarService::WHICH_ON_HOMEPAGE)
            ->setMaxResults((int) $this->getLimit())
            ->getResult();

        return $this->getRenderer()->render(
            'calendar/partial/list/calendar-small',
            ['calendarItems' => $calendarItems]
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

        return $this->getRenderer()->render(
            'calendar/partial/year-selector',
            [
                'years'        => $years,
                'selectedYear' => $year,
            ]
        );
    }
}
