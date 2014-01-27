<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    ProjectTest
 * @package     Entity
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace CalendarTest\Entity;

use Calendar\Entity\Calendar;

class ProjectTest extends \PHPUnit_Framework_TestCase
{

    public function testCanCreateEntity()
    {
        $calendar = new Calendar();
        $this->assertInstanceOf("Calendar\Entity\Calendar", $calendar);
    }
}
