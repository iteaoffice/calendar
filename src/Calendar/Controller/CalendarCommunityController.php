<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Controller
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Calendar\Service\CalendarService;

/**
 *
 */
class CalendarCommunityController extends CalendarAbstractController
{
    /**
     * @return ViewModel
     */
    public function overviewAction()
    {
        $which = $this->getEvent()->getRouteMatch()->getParam('which', 'upcoming');
        $page  = $this->getEvent()->getRouteMatch()->getParam('page', 1);

        $calendarItems = $this->getCalendarService()->findCalendarItems($which);
        $paginator     = new Paginator(new PaginatorAdapter(new ORMPaginator($calendarItems)));
        $paginator->setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 15);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator->getDefaultItemCountPerPage()));

        $whichValues = $this->getCalendarService()->getWhichValues();

        return new ViewModel(
            array(
                'which'       => $which,
                'paginator'   => $paginator,
                'whichValues' => $whichValues
            )
        );
    }

    /**
     * Special action which produces an HTML version of the review calendar
     *
     * @return ViewModel
     */
    public function reviewCalendarAction()
    {
        $calendarItems = $this->getCalendarService()->findCalendarItems(CalendarService::WHICH_REVIEWS)->getResult();

        return new ViewModel(
            array(
                'calendarItems' => $calendarItems,
            )
        );
    }

    /**
     * @return ViewModel
     */
    public function calendarAction()
    {
        $calendarService = $this->getCalendarService()->setCalendarId(
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        if (is_null($calendarService->getCalendar()->getId())) {
            return $this->notFoundAction();
        }

        return new ViewModel(
            array(
                'calendar' => $calendarService->getCalendar(),
            )
        );
    }
}
