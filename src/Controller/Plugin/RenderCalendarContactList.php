<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category   Program
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2015 ITEA Office
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */

namespace Calendar\Controller\Plugin;

use Calendar\Entity\Calendar;
use Calendar\Entity\Contact as CalendarContact;
use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use General\Service\GeneralService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create a link to an project.
 *
 * @category   Program
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @license    https://itea3.org/licence.txt proprietary
 *
 * @link       https://itea3.org
 */
class RenderCalendarContactList extends AbstractPlugin
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param Calendar $calendar
     *
     * @return CalendarPdf
     */
    public function render(Calendar $calendar)
    {
        $pdf = new CalendarPdf();
        $pdf->setTemplate($this->getModuleOptions()->getCalendarContactTemplate());
        $pdf->AddPage();
        $pdf->SetFontSize(9);
        $twig = $this->getServiceLocator()->get('ZfcTwigRenderer');

        $calendarContacts = $this->getCalendarService()
            ->findCalendarContactsByCalendar($calendar, CalendarContact::STATUS_NO_DECLINED);

        //Create chunks of arrays per 13, as that amount fits on the screen
        $paginatedContacts = array_chunk($calendarContacts, 13);
        $minAmountOfPages = max(sizeof($paginatedContacts), 2);

        for ($i = 0; $i < $minAmountOfPages; $i++) {
            /*
             * Use the NDA object to render the filename
             */
            $contactListContent = $twig->render('calendar/pdf/calendar-contact', [
                'calendarService'  => $this->getCalendarService(),
                'calendar'         => $calendar,
                'calendarContacts' => isset($paginatedContacts[$i]) ? $paginatedContacts[$i] : [],
            ]);

            $pdf->writeHTMLCell(0, 0, 14, 42, $contactListContent);

            /*
             * Don't add a new page on the last iteration
             */
            if ($i < $minAmountOfPages - 1) {
                $pdf->AddPage();
            }
        }

        return $pdf;
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions()
    {
        return $this->getServiceLocator()->get(ModuleOptions::class);
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return $this
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Gateway to the General Service.
     *
     * @return GeneralService
     */
    public function getGeneralService()
    {
        return $this->getServiceLocator()->get(GeneralService::class);
    }

    /**
     * @return CalendarService
     */
    public function getCalendarService()
    {
        return $this->getServiceLocator()->get(CalendarService::class);
    }
}