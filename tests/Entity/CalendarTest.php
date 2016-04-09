<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    CalendarTest
 * @package     Entity
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */
namespace CalendarTest\Entity;

use Calendar\Entity\Calendar;

class CalendarTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateEntity()
    {
        $calendar = new Calendar();
        $this->assertInstanceOf("Calendar\Entity\Calendar", $calendar);
    }
}