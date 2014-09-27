<?php
/**
 * Debranova copyright message placeholder
 *
 * @category  Calendar
 * @package   Entity
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 Debranova
 */
namespace Calendar\Acl\Assertion;

use Admin\Entity\Access;
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

        $id = (int) $this->getRouteMatch()->getParam('id');

        if (is_null($privilege)) {
            $privilege = $this->getRouteMatch()->getParam('privilege');
        }

        if (!$resource instanceof CalendarEntity) {
            /**
             * We are coming via the router, so we need to build up the information via the  routeMatch
             * The id and privilege are important
             */
            /**
             * Check if a Contact has access to a meeting. We need to build the meeting first
             */
            $resource = $this->getCalendarService()->setCalendarId($id)->getCalendar();
        } else {
            $this->getCalendarService()->setCalendar($resource);
        }

        switch ($privilege) {
            case 'edit':
                return $this->rolesHaveAccess([Access::ACCESS_OFFICE]);
            case 'select-attendees':
                if ($this->getContactService()->hasPermit('edit', $resource)) {
                    return true;
                }

                /**
                 * The project leader also has righs to invite users
                 */
                if (!is_null($resource->getProjectCalendar())) {
                    if ($this->getContactService()->hasPermit('edit', $resource->getProjectCalendar()->getProject())) {
                        return true;
                    }
                }

                return $this->rolesHaveAccess([Access::ACCESS_OFFICE]);
            case 'list':
                return true;
            case 'overview-admin':
            case 'view-admin':
            case 'review-calendar':
                return $this->rolesHaveAccess([Access::ACCESS_OFFICE]);
            case 'overview':
            case 'contact':
                return $this->hasContact();
            case 'view-community':
                /**
                 * Access can be granted via the type or via the permit-editor.
                 * We will first check the permit and have a fail over to the type
                 */
                if ($this->getContactService()->hasPermit('view', $resource)) {
                    return true;
                }

                return $this->rolesHaveAccess($resource->getType()->getAccess());

            case 'view':
                return $this->getCalendarService()->canViewCalendar($this->getContactService()->getContact());

        }

        return false;
    }
}
