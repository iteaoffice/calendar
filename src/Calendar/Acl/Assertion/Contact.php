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
use Calendar\Entity\Contact as ContactEntity;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class Contact extends AssertionAbstract
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

        $id = (int) $this->getServiceLocator()->get("Application")->getMvcEvent()->getRequest()->getPost('id');

        if (is_null($privilege)) {
            $privilege = $this->getRouteMatch()->getParam('privilege');
        }

        if (!$resource instanceof ContactEntity) {
            $resource = $this->getCalendarService()->findEntityById('Contact', $id);
        }

        $this->getCalendarService()->setCalendar($resource->getCalendar());

        switch ($privilege) {

            case 'update-status':
                if ($this->getCalendarService()->calendarHasContact(
                    $this->getCalendarService()->getCalendar(),
                    $this->getContactService()->getContact()
                )
                ) {
                    return true;
                }

                return $this->rolesHaveAccess([Access::ACCESS_OFFICE]);
        }

        return false;
    }
}
