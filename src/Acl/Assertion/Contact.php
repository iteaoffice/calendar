<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Acl\Assertion;

use Admin\Entity\Access;
use Calendar\Entity\Contact as ContactEntity;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class Contact extends AssertionAbstract
{
    /**
     * Returns true if and only if the assertion conditions are met.
     *
     * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
     * $role, $contact, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param Acl $acl
     * @param RoleInterface $role
     * @param ResourceInterface $contact
     * @param string $privilege
     *
     * @return bool
     */
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $contact = null, $privilege = null)
    {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (!$contact instanceof ContactEntity) {
            $contact = $this->getCalendarService()->findEntityById(ContactEntity::class, $id);
        }

        switch ($this->getPrivilege()) {
            case 'update-status':
                if ($this->getCalendarService()->calendarHasContact($contact->getCalendar(), $this->getContact())) {
                    return true;
                }

                return $this->rolesHaveAccess([Access::ACCESS_OFFICE]);
        }

        return false;
    }
}
