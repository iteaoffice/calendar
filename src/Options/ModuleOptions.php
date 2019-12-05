<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Class ModuleOptions
 *
 * @package Calendar\Options
 */
class ModuleOptions extends AbstractOptions implements CalendarOptionsInterface
{
    /**
     * Turn off strict options mode.
     */
    protected $__strictMode__ = false;

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
