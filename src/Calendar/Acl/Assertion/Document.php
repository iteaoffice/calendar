<?php
/**
 * Debranova copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 Debranova
 */

namespace Calendar\Acl\Assertion;

use Admin\Entity\Access;
use Calendar\Entity\Document as DocumentEntity;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class Document extends AssertionAbstract
{
    /**
     * Returns true if and only if the assertion conditions are met.
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
        if (!$resource instanceof DocumentEntity) {
            /*
             * We are coming via the router, so we need to build up the information via the  routeMatch
             * The id and privilege are important
             */
            $documentId = (int) $this->getRouteMatch()->getParam('id');
            $privilege = $this->getRouteMatch()->getParam('privilege');
            /*
             * Check if a Contact has access to a meeting. We need to build the meeting first
             */
            $resource = $this->getCalendarService()->findEntityById('Document', $documentId);
        }

        /*
         * No document was found, so return true because we do not now anything about the access
         */
        if (is_null($resource)) {
            return true;
        }

        //Inject the calendar into the calendarService to have the access rights there
        $this->getCalendarService()->setCalendar($resource->getCalendar());

        switch ($privilege) {
            case 'document-community':
            case 'download':
                return $this->getCalendarService()->canViewCalendar($this->getContactService()->getContact());
            case 'edit-community':
                if ($this->getContactService()->hasPermit('edit', $resource->getCalendar())) {
                    return true;
                }

                /*
                 * The project leader also has rights to invite users
                 */
                if (!is_null($resource->getCalendar()->getProjectCalendar())) {
                    if ($this->getContactService()->hasPermit(
                        'edit',
                        $resource->getCalendar()->getProjectCalendar()->getProject()
                    )
                    ) {
                        return true;
                    }
                }

                return $this->rolesHaveAccess([Access::ACCESS_OFFICE]);
        }

        return false;
    }
}
