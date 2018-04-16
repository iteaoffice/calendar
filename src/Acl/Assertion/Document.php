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
use Calendar\Entity\Document as DocumentEntity;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class Document extends AbstractAssertion
{
    /**
     * Returns true if and only if the assertion conditions are met.
     *
     * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
     * $role, $document, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param Acl               $acl
     * @param RoleInterface     $role
     * @param ResourceInterface $document
     * @param string            $privilege
     *
     * @return bool
     */
    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $document = null,
        $privilege = null
    ): bool {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        if (!$document instanceof DocumentEntity && null !== $id) {
            $document = $this->calendarService->find(DocumentEntity::class, $id);
        }

        /*
         * No document was found, so return true because we do not now anything about the access
         */
        if (null === $document) {
            return true;
        }

        switch ($this->getPrivilege()) {
            case 'document-community':
            case 'download':
                if ($this->hasPermission($document->getCalendar(), 'view')) {
                    return true;
                }

                return $this->calendarService->canViewCalendar($document->getCalendar(), $this->contact);
            case 'edit-community':
                if ($this->hasPermission($document->getCalendar(), 'edit')) {
                    return true;
                }

                /*
                 * The project leader also has rights to invite users
                 */
                if (null !== $document->getCalendar()->getProjectCalendar()) {
                    if ($this->contactService->contactHasPermit(
                        $this->contact,
                        'edit',
                        $document->getCalendar()->getProjectCalendar()->getProject()
                    )
                    ) {
                        return true;
                    }
                }

                return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
            case 'document-admin':
                return $this->rolesHaveAccess(Access::ACCESS_OFFICE);
        }

        return false;
    }
}
