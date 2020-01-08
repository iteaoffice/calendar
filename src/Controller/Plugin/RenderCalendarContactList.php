<?php

/**
 * ITEA Office all rights reserved
 *
 * @category   Program
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */

declare(strict_types=1);

namespace Calendar\Controller\Plugin;

use Calendar\Entity\Calendar;
use Calendar\Entity\Contact as CalendarContact;
use Calendar\Options\ModuleOptions;
use Calendar\Service\CalendarService;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use ZfcTwig\View\TwigRenderer;

use function array_chunk;
use function count;
use function max;

/**
 * Class RenderCalendarContactList
 *
 * @package Calendar\Controller\Plugin
 */
final class RenderCalendarContactList extends AbstractPlugin
{
    private TwigRenderer $twigRenderer;
    private ModuleOptions $moduleOptions;
    private CalendarService $calendarService;

    public function __construct(
        TwigRenderer $twigRenderer,
        ModuleOptions $moduleOptions,
        CalendarService $calendarService
    ) {
        $this->twigRenderer = $twigRenderer;
        $this->moduleOptions = $moduleOptions;
        $this->calendarService = $calendarService;
    }

    public function renderPresenceList(Calendar $calendar): CalendarPdf
    {
        $pdf = new CalendarPdf();
        $pdf->setTemplate($this->moduleOptions->getCalendarContactTemplate());
        $pdf->AddPage();
        $pdf->SetFont('freesans', '', 10);

        $calendarContacts = $this->calendarService->findCalendarContactsByCalendar(
            $calendar,
            CalendarContact::STATUS_ALL,
            'organisation'
        );

        //Create chunks of arrays per 22, as that amount fits on the screen
        $paginatedContacts = array_chunk($calendarContacts, 22);
        $minAmountOfPages = max(count($paginatedContacts), 1);

        for ($i = 0; $i < $minAmountOfPages; $i++) {
            $contactListContent = $this->twigRenderer->render(
                'calendar/pdf/presence-list',
                [
                    'calendarService'  => $this->calendarService,
                    'calendar'         => $calendar,
                    'calendarContacts' => $paginatedContacts[$i] ?? [],
                ]
            );

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

    public function renderSignatureList(Calendar $calendar): CalendarPdf
    {
        $pdf = new CalendarPdf();
        $pdf->setTemplate($this->moduleOptions->getCalendarContactTemplate());
        $pdf->AddPage();
        $pdf->SetFont('freesans', '', 10);

        $calendarContacts = $this->calendarService->findCalendarContactsByCalendar(
            $calendar,
            CalendarContact::STATUS_NO_DECLINED
        );

        //Create chunks of arrays per 13, as that amount fits on the screen
        $paginatedContacts = array_chunk($calendarContacts, 13);
        $minAmountOfPages = max(count($paginatedContacts), 2);

        for ($i = 0; $i < $minAmountOfPages; $i++) {
            /*
             * Use the NDA object to render the filename
             */
            $contactListContent = $this->twigRenderer->render(
                'calendar/pdf/signature-list',
                [
                    'calendarService'  => $this->calendarService,
                    'calendar'         => $calendar,
                    'calendarContacts' => $paginatedContacts[$i] ?? [],
                ]
            );

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
}
