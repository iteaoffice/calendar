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

use Calendar\Options\ModuleOptions;
use Contact\Service\ContactService;
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
class RenderReviewCalendar extends AbstractPlugin
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param array $calendarItems
     *
     * @return CalendarPdf
     */
    public function render(array $calendarItems)
    {
        $pdf = new CalendarPdf();

        $pdf->setTemplate($this->getModuleOptions()->getReviewCalendarTemplate());
        $pdf->setPageOrientation('L');
        $pdf->addPage();

        $pdf->SetFontSize(8);
        $twig = $this->getServiceLocator()->get('ZfcTwigRenderer');

        /*
         * Use the NDA object to render the filename
         */
        $contactListContent = $twig->render(
            'calendar/pdf/review-calendar',
            [
                'calendarItems' => $calendarItems,
            ]
        );

        $pdf->writeHTMLCell(0, 0, 12, 35, $contactListContent, 0, 0, 0, false);

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
     * Gateway to the Contact Service.
     *
     * @return ContactService
     */
    public function getContactService()
    {
        return $this->getServiceLocator()->get(ContactService::class);
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
}
