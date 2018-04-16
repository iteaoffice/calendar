<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Controller;

use Calendar\Entity\Type;
use Calendar\Service\CalendarService;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use ZfcTwig\View\TwigRenderer;

/**
 *
 */
class CalendarController extends AbstractActionController
{
    /**
     * @var CalendarService
     */
    protected $calendarService;
    /**
     * @var TwigRenderer
     */
    protected $renderer;

    /**
     * CalendarController constructor.
     *
     * @param CalendarService $calendarService
     * @param TwigRenderer    $renderer
     */
    public function __construct(CalendarService $calendarService, TwigRenderer $renderer)
    {
        $this->calendarService = $calendarService;
        $this->renderer = $renderer;
    }

    /**
     * @return Response
     */
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
        \file_put_contents($cacheFileName, $css);

        /** @var Response $response */
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type: text/css');
        $response->setContent($css);

        return $response;
    }
}
