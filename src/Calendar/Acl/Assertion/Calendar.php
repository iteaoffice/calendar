<?php
/**
 * Debranova copyright message placeholder
 *
 * @category    Calendar
 * @package     Entity
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 Debranova
 */
namespace Calendar\Acl\Assertion;

use Calendar\Entity\Calendar as CalendarEntity;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class Calendar extends AssertionAbstract
{
    /**
     * Returns true if and only if the assertion conditions are met
     *
     * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
     * $role, $resource, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param Acl               $acl
     * @param RoleInterface     $role
     * @param ResourceInterface $resource
     * @param string            $privilege
     *
     * @return bool
     */
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        if (!$resource instanceof CalendarEntity) {
            /**
             * We are coming via the router, so we need to build up the information via the  routeMatch
             * The id and privilege are important
             */
            $calendarId = (int) $this->getRouteMatch()->getParam('id');
            $privilege  = $this->getRouteMatch()->getParam('privilege');
            /**
             * Check if a Contact has access to a meeting. We need to build the meeting first
             */
            $this->getCalendarService()->setCalendarId($calendarId);
        } else {
            $this->getCalendarService()->setCalendar($resource);
        }
        switch ($privilege) {
            case 'view':
                return $this->getCalendarService()->canViewCalendar($this->getContactService()->getContact());
                break;
        }

        return false;
    }
}
