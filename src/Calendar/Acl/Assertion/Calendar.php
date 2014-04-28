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

use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\ServiceManager\ServiceManager;
use Calendar\Service\CalendarService;
use Calendar\Entity\Calendar as CalendarEntity;

class Calendar implements AssertionInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    /**
     * @var CalendarService
     */
    protected $calendarService;

    /**
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager  = $serviceManager;
        $this->calendarService = $this->serviceManager->get("calendar_calendar_service");
        if ($this->serviceManager->get('zfcuser_auth_service')->hasIdentity()) {
            $this->contact = $this->serviceManager->get('zfcuser_auth_service')->getIdentity();
        } else {
            $this->contact = null;
        }
    }

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
            $calendarId = (int) $this->serviceManager->get("Application")->getMvcEvent()->getRouteMatch()->getParam(
                'id'
            );
            $privilege  = $this->serviceManager->get("Application")->getMvcEvent()->getRouteMatch()->getParam(
                'privilege'
            );
            /**
             * Check if a Contact has access to a meeting. We need to build the meeting first
             */
            $calendar = $this->calendarService->findEntityById('calendar', $calendarId);
        } else {
            $calendar = $resource;
        }

        /**
         * Add the $calendar to the service to be able to do additional queries
         */
        $this->calendarService->setCalendar($calendar);

        switch ($privilege) {
            case 'view':
                return $this->calendarService->canViewCalendar($this->contact);
                break;
        }

        return false;
    }
}
