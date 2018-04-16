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
use Calendar\Entity\Calendar as CalendarEntity;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class Calendar extends AbstractAssertion
{
    /**
     * Returns true if and only if the assertion conditions are met.
     *
     * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
     * $role, $calendar, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param Acl                                      $acl
     * @param RoleInterface                            $role
     * @param ResourceInterface|CalendarEntity|\object $calendar
     * @param string                                   $privilege
     *
     * @return bool
     */
    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $calendar = null,
        $privilege = null
    ): bool {
        $this->setPrivilege($privilege);
        $id = $id = $this->getId();

        if (!$calendar instanceof CalendarEntity && null !== $id) {
            $calendar = $this->calendarService->findCalendarById((int)$id);
        }

        switch ($this->getPrivilege()) {
            case 'edit':
                return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
            case 'select-attendees':
                /**
                 * Stop this case when there is no project calendar
                 */
                if (null === $calendar->getProjectCalendar()) {
                    return false;
                }

                if ($this->hasPermission($calendar, 'edit')) {
                    return true;
                }

                return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
            case 'add-document':
            case 'presence-list':
            case 'signature-list':
                if ($this->hasPermission($calendar, 'edit')) {
                    return true;
                }

                return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
            case 'list':
                return true;
            case 'overview-admin':
            case 'view-admin':
            case 'edit-attendees-admin':
            case 'set-roles-admin':
            case 'new':
                return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
            case 'overview':
            case 'review-calendar':
            case 'download-review-calendar':
            case 'contact':
                return $this->hasContact();
            case 'view-community':
            case 'send-message':
            case 'download-binder':
                /*
                 * Access can be granted via the type or via the permit-editor.
                 * We will first check the permit and have a fail over to the type
                 */
                if ($this->hasPermission($calendar, 'view')) {
                    return true;
                }

                return $this->rolesHaveAccess($calendar->getType()->getAccess());

            case 'view':
                return $this->calendarService->canViewCalendar($calendar, $this->contact);
        }

        return false;
    }
}
