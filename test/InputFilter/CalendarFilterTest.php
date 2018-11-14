<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Contact
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace ContactTest\Service;

use Calendar\InputFilter\CalendarFilter;
use Testing\Util\AbstractInputFilterTest;

/**
 * Class ContactFilterTest
 *
 * @package ContactTest\Service
 */
class CalendarFilterTest extends AbstractInputFilterTest
{
    /**
     *
     */
    public function testCanCreateCalendarFilterInputFilter(): void
    {

        $calendarFilter = new CalendarFilter();

        $this->assertInstanceOf(CalendarFilter::class, $calendarFilter);


        $this->assertNotNull($calendarFilter->get('calendar_entity_calendar'));
        $this->assertNotNull($calendarFilter->get('calendar_entity_calendar')->get('calendar'));
        $this->assertNotNull($calendarFilter->get('calendar_entity_calendar')->get('location'));
        $this->assertNotNull($calendarFilter->get('calendar_entity_calendar')->get('dateFrom'));
        $this->assertNotNull($calendarFilter->get('calendar_entity_calendar')->get('dateEnd'));
    }
}