<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\Controller;

use Calendar\Entity\Type;
use Calendar\Service\CalendarService;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use ZfcTwig\View\TwigRenderer;

use function file_put_contents;

/**
 * Class CalendarController
 *
 * @package Calendar\Controller
 */
final class CalendarController extends AbstractActionController
{
    private CalendarService $calendarService;
    private TwigRenderer $renderer;

    public function __construct(CalendarService $calendarService, TwigRenderer $renderer)
    {
        $this->calendarService = $calendarService;
        $this->renderer = $renderer;
    }

    public function calendarTypeColorCssAction(): Response
    {
        $calendarTypes = $this->calendarService->findAll(Type::class);
        $calendarType = new Type();
        $cacheFileName = $calendarType->getCacheCssFileName();

        $css = $this->renderer->render(
            'calendar/calendar/calendar-type-color-css',
            [
                'calendarTypes' => $calendarTypes,
            ]
        );
        //Save a copy of the file in the caching-folder
        file_put_contents($cacheFileName, $css);

        /** @var Response $response */
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type: text/css');
        $response->setContent($css);

        return $response;
    }
}
