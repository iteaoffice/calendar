<?php
/**
 * ITEA Office all rights reserved
 *
 * @category   Program
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */

declare(strict_types=1);

namespace Calendar\Controller\Plugin;

use Calendar\Options\ModuleOptions;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use ZfcTwig\View\TwigRenderer;

/**
 * Class RenderReviewCalendar
 *
 * @package Calendar\Controller\Plugin
 */
class RenderReviewCalendar extends AbstractPlugin
{
    /**
     * @var TwigRenderer
     */
    private $twigRenderer;
    /**
     * @var ModuleOptions
     */
    private $moduleOptions;

    public function __construct(TwigRenderer $twigRenderer, ModuleOptions $moduleOptions)
    {
        $this->twigRenderer = $twigRenderer;
        $this->moduleOptions = $moduleOptions;
    }

    public function __invoke(array $calendarItems): CalendarPdf
    {
        $pdf = new CalendarPdf();

        $pdf->setTemplate($this->moduleOptions->getReviewCalendarTemplate());
        $pdf->setPageOrientation('L');
        $pdf->AddPage();
        $pdf->SetFont('freesans', '', 12);

        $contactListContent = $this->twigRenderer->render(
            'calendar/pdf/review-calendar',
            [
                'calendarItems' => $calendarItems,
            ]
        );

        $pdf->writeHTMLCell(0, 0, 12, 35, $contactListContent, 0, 0, 0, false);

        return $pdf;
    }
}
