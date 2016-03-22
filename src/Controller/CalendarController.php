<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Calendar\Controller;

use Calendar\Entity\Type;

/**
 *
 */
class CalendarController extends CalendarAbstractController
{
    /**
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function calendarTypeColorCssAction()
    {
        $calendarTypes = $this->getCalendarService()->findAll('Type');
        $calendarType = new Type();
        $cacheFileName = $calendarType->getCacheCssFileName();

        $css = $this->getRenderer()->render('calendar/calendar/calendar-type-color-css', [
            'calendarTypes' => $calendarTypes,
        ]);
        //Save a copy of the file in the caching-folder
        file_put_contents($cacheFileName, $css);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type: text/css');
        $response->setContent($css);

        return $response;
    }
}
