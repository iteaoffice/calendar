<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category   Project
 * @package    Acl
 * @subpackage Assertion
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2014 ITEA Office
 * @license    http://debranova.org/license.txt proprietary
 * @link       http://debranova.org
 */
namespace Calendar\Acl\Assertion;

use Admin\Entity\Access;
use Admin\Service\AdminService;
use Admin\Service\AdminServiceAwareInterface;
use Calendar\Service\CalendarService;
use Calendar\Service\CalendarServiceAwareInterface;
use Contact\Service\ContactService;
use Contact\Service\ContactServiceAwareInterface;
use Doctrine\ORM\PersistentCollection;
use Zend\Mvc\Router\RouteMatch;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create a link to an document
 *
 * @category   Calendar
 * @package    Acl
 * @subpackage Assertion
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2014 ITEA Office
 * @license    http://debranova.org/license.txt proprietary
 * @link       http://debranova.org
 */
abstract class AssertionAbstract implements
    AssertionInterface,
    ServiceLocatorAwareInterface,
    AdminServiceAwareInterface,
    ContactServiceAwareInterface,
    CalendarServiceAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
    /**
     * @var ContactService
     */
    protected $contactService;
    /**
     * @var AdminService
     */
    protected $adminService;
    /**
     * @var CalendarService
     */
    protected $calendarService;
    /**
     * @var array
     */
    protected $accessRoles = [];

    /**
     * @return RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->getServiceLocator()->get("Application")->getMvcEvent()->getRouteMatch();
    }

    /**
     * Get the service locator.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Set the service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AssertionAbstract
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasContact()
    {
        return !$this->getContactService()->isEmpty();
    }

    /**
     * @return ContactService
     */
    public function getContactService()
    {
        if ($this->contactService->isEmpty() && $this->getServiceLocator()->get('zfcuser_auth_service')->hasIdentity()
        ) {
            $this->contactService->setContact(
                $this->getServiceLocator()->get('zfcuser_auth_service')->getIdentity()
            );
        }

        return $this->contactService;
    }

    /**
     * The contact service
     *
     * @param ContactService $contactService
     *
     * @return $this;
     */
    public function setContactService(ContactService $contactService)
    {
        $this->contactService = $contactService;

        return $this;
    }

    /**
     * Get calendar service
     *
     * @return CalendarService
     */
    public function getCalendarService()
    {
        return $this->calendarService;
    }

    /**
     * The calendar service
     *
     * @param CalendarService $calendarService
     *
     * @return $this
     */
    public function setCalendarService(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;

        return $this;
    }

    /**
     * Returns true when a role or roles have access
     *
     * @param string|array|PersistentCollection $access
     *
     * @return boolean
     */
    public function rolesHaveAccess($access)
    {
        $accessRoles = $this->prepareAccessRoles($access);
        if (sizeof($accessRoles) === 0) {
            return true;
        }

        foreach ($accessRoles as $accessRole) {
            if (strtolower($accessRole->getAccess()) === strtolower(Access::ACCESS_PUBLIC)) {
                return true;
            }
            if ($this->hasContact()) {
                if (in_array(
                    strtolower($accessRole->getAccess()),
                    $this->getAdminService()->findAccessRolesByContactAsArray($this->getContactService()->getContact())
                )
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $access
     *
     * @return Access[]
     */
    protected function prepareAccessRoles($access)
    {
        if (!$access instanceof PersistentCollection) {
            /**
             * We only have a string, so we need to lookup the role
             */
            $access = [
                $this->getAdminService()->findAccessByName($access),
            ];
        }

        return $access;
    }

    /**
     * @return array
     */
    public function getAccessRoles()
    {
        if (empty($this->accessRoles) && !$this->getContactService()->isEmpty()) {
            $this->accessRoles = $this->getAdminService()->findAccessRolesByContactAsArray($this->getContactService()->getContact());
        }

        return $this->accessRoles;
    }

    /**
     * @return AdminService
     */
    public function getAdminService()
    {
        return $this->adminService;
    }

    /**
     * @param AdminService $adminService
     *
     * @return AssertionAbstract
     */
    public function setAdminService(AdminService $adminService)
    {
        $this->adminService = $adminService;

        return $this;
    }
}
