<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category   Program
 * @package    Controller
 * @subpackage Plugin
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2014 ITEA Office
 * @license    http://debranova.org/license.txt proprietary
 * @link       http://debranova.org
 */
namespace Calendar\Controller\Plugin;

use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use General\Service\GeneralService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create a link to an project
 *
 * @category   Program
 * @package    Controller
 * @subpackage Plugin
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @license    http://debranova.org/licence.txt proprietary
 * @link       http://debranova.org
 */
class RenderCalendarContactList extends AbstractPlugin
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param CalendarService $calendarService
     *
     * @return CalendarPdf
     */

    public function render(CalendarService $calendarService)
    {
        $pdf = new CalendarPdf();
        $pdf->setTemplate($this->getModuleOptions()->getCalendarContactTemplate());
        $pdf->addPage();
        $pdf->SetFontSize(9);
        $twig = $this->getServiceLocator()->get('ZfcTwigRenderer');

        $calendarContacts = $calendarService->findCalendarContactsByCalendar($calendarService->getCalendar());

        //Create chunks of arrays per 13, as that amount fits on the screen
        $paginatedContacts = array_chunk($calendarContacts, 13);
        $minAmountOfPages = max(sizeof($paginatedContacts), 2);

        for ($i = 0; $i < $minAmountOfPages; $i++) {
            /**
             * Use the NDA object to render the filename
             */
            $contactListContent = $twig->render(
                'calendar/pdf/calendar-contact',
                [
                    'calendarService'  => $calendarService,
                    'calendarContacts' => isset($paginatedContacts[$i]) ? $paginatedContacts[$i] : [],
                ]
            );

            $pdf->writeHTMLCell(0, 0, 14, 42, $contactListContent);

            /**
             * Don't add a new page on the last iteration
             */
            if ($i < $minAmountOfPages - 1) {
                $pdf->addPage();
            }

        }

        return $pdf;
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions()
    {
        return $this->getServiceLocator()->get('calendar_module_options');
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
     * Gateway to the General Service
     *
     * @return GeneralService
     */
    public function getGeneralService()
    {
        return $this->getServiceLocator()->get(GeneralService::class);
    }
}
