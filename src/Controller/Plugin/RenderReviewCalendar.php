<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\Controller\Plugin;

use Calendar\Options\ModuleOptions;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use ZfcTwig\View\TwigRenderer;

/**
 * Class RenderReviewCalendar
 *
 * @package Calendar\Controller\Plugin
 */
final class RenderReviewCalendar extends AbstractPlugin
{
    private TwigRenderer $twigRenderer;
    private ModuleOptions $moduleOptions;

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
