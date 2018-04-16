<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
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
    /**
     * Location of the PDF having the calendar contact template.
     *
     * @var string
     */
    protected $calendarContactTemplate = '';
    /*
     * Location of the PDF having the NDA template
     *
     * @var string
     */
    protected $reviewCalendarTemplate = '';
    /**
     * Default year to show past events
     *
     */
    protected $defaultYear;

    /**
     * Template to use for upcoming events
     *
     * @var string
     */
    protected $calendarUpcomingTemplate = '';

    /**
     * Template to use for past events
     *
     * @var string
     */
    protected $calendarPastTemplate = '';

    /**
     * Enable the calendar contacts.
     *
     * @return boolean
     */
    public function getCommunityCalendarContactEnabled()
    {
        return $this->communityCalendarContactEnabled;
    }

    /**
     * Sets whether the review invitations should be enabled on the homepage of the community.
     *
     * @param $communityCalendarContactEnabled
     *
     * @return ModuleOptions
     */
    public function setCommunityCalendarContactEnabled($communityCalendarContactEnabled)
    {
        $this->communityCalendarContactEnabled = $communityCalendarContactEnabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getCalendarContactTemplate()
    {
        return $this->calendarContactTemplate;
    }

    /**
     * @param $calendarContactTemplate
     *
     * @return ModuleOptions
     */
    public function setCalendarContactTemplate($calendarContactTemplate)
    {
        $this->calendarContactTemplate = $calendarContactTemplate;

        return $this;
    }

    /**
     * @return string
     */
    public function getReviewCalendarTemplate()
    {
        return $this->reviewCalendarTemplate;
    }

    /**
     * @param $reviewCalendarTemplate
     *
     * @return ModuleOptions
     */
    public function setReviewCalendarTemplate($reviewCalendarTemplate)
    {
        $this->reviewCalendarTemplate = $reviewCalendarTemplate;

        return $this;
    }


    /**
     * Returns the default year
     *
     * @return int
     */
    public function getDefaultYear()
    {
        return $this->defaultYear;
    }

    /**
     * @param $defaultYear
     *
     * @return $this
     */
    public function setDefaultYear($defaultYear)
    {
        $this->defaultYear = $defaultYear;

        return $this;
    }
}
