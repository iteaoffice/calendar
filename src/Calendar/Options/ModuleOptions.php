<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Project
 * @package     Options
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions implements
    CalendarOptionsInterface
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;
    /**
     * Activate Calendar Contacts in Community
     * @var boolean
     */
    protected $communityCalendarContactEnabled = true;

    /**
     * Sets wether the review invitations should be enabled on the homepage of the community
     * @param $communityCalendarContactEnabled
     * @return boolean
     */
    public function setCommunityCalendarContactEnabled($communityCalendarContactEnabled)
    {
        $this->communityCalendarContactEnabled = $communityCalendarContactEnabled;

        return $this;
    }

    /**
     * Enable the calendar contacts
     * @return boolean
     */
    public function getCommunityCalendarContactEnabled()
    {
       return  $this->communityCalendarContactEnabled;
    }

}
