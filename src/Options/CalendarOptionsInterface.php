<?php

/**
 * ITEA Office all rights reserved
 *
 * @category    Calendar
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Options;

/**
 * Interface CalendarOptionsInterface.
 */
interface CalendarOptionsInterface
{
    public function setCalendarContactTemplate(string $calendarContactTemplate): ModuleOptions;

    public function getCalendarContactTemplate(): string;

    public function setReviewCalendarTemplate(string $reviewCalendarTemplate): ModuleOptions;

    public function getReviewCalendarTemplate(): string;
}
