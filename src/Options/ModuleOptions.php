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

use Laminas\Stdlib\AbstractOptions;

/**
 * Class ModuleOptions
 *
 * @package Calendar\Options
 */
class ModuleOptions extends AbstractOptions implements CalendarOptionsInterface
{
    protected string $calendarContactTemplate = '';
    protected string $reviewCalendarTemplate = '';

    public function getCalendarContactTemplate(): string
    {
        return $this->calendarContactTemplate;
    }

    public function setCalendarContactTemplate(string $calendarContactTemplate): ModuleOptions
    {
        $this->calendarContactTemplate = $calendarContactTemplate;

        return $this;
    }

    public function getReviewCalendarTemplate(): string
    {
        return $this->reviewCalendarTemplate;
    }

    public function setReviewCalendarTemplate(string $reviewCalendarTemplate): ModuleOptions
    {
        $this->reviewCalendarTemplate = $reviewCalendarTemplate;

        return $this;
    }
}
