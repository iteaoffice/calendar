<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
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
