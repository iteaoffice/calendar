<?php
/**
 * ITEA Office all rights reserved
 *
 * @category   Project
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */

declare(strict_types=1);

namespace Calendar\Acl\Assertion;

use Admin\Entity\Access;
use Admin\Service\AdminService;
use Calendar\Service\CalendarService;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Doctrine\ORM\PersistentCollection;
use Interop\Container\ContainerInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AssertionAbstract
 * @package Calendar\Acl\Assertion
 */
abstract class AssertionAbstract implements AssertionInterface
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
     * @var Contact
     */
    protected $contact;
    /**
     * @var AdminService
     */
    protected $adminService;
    /**
     * @var CalendarService
     */
    protected $calendarService;
    /**
     * @var string
     */
    protected $privilege;
    /**
     * @var array
     */
    protected $accessRoles = [];

    /**
     * Returns true when a role or roles have access.
     *
     * @param string|array|PersistentCollection $access
     *
     * @return boolean
     */
    public function rolesHaveAccess($access): bool
    {
        $accessRoles = $this->prepareAccessRoles($access);
        if (count($accessRoles) === 0) {
            return true;
        }

        foreach ($accessRoles as $accessRole) {
            if (strtolower($accessRole->getAccess()) === strtolower(Access::ACCESS_PUBLIC)) {
                return true;
            }
            if ($this->hasContact() &&
                in_array(
                    strtolower($accessRole->getAccess()),
                    $this->getAdminService()->findAccessRolesByContactAsArray($this->getContact()),
                    true
                )) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $access
     * @return iterable|Access[]
     */
    protected function prepareAccessRoles($access): iterable
    {
        if (!$access instanceof PersistentCollection) {
            /*
             * We only have a string, so we need to lookup the role
             */
            $access = [
                $this->getAdminService()->findAccessByName($access),
            ];
        }

        return $access;
    }

    /**
     * @return AdminService
     */
    public function getAdminService(): AdminService
    {
        return $this->adminService;
    }

    /**
     * @param AdminService $adminService
     */
    public function setAdminService($adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * @return bool
     */
    public function hasContact(): bool
    {
        return !$this->getContact()->isEmpty();
    }

    /**
     * @return Contact
     */
    public function getContact(): Contact
    {
        if (is_null($this->contact)) {
            $this->contact = new Contact();
        }

        return $this->contact;
    }

    /**
     * @param Contact $contact
     *
     * @return AssertionAbstract
     */
    public function setContact($contact): AssertionAbstract
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return array
     */
    public function getAccessRoles(): array
    {
        if (empty($this->accessRoles) && $this->hasContact()) {
            $this->accessRoles = $this->getAdminService()->findAccessRolesByContactAsArray($this->getContact());
        }

        return $this->accessRoles;
    }

    /**
     * @return string
     */
    public function getPrivilege()
    {
        return $this->privilege;
    }

    /**
     * @param string $privilege
     *
     * @return AssertionAbstract
     */
    public function setPrivilege($privilege)
    {
        /**
         * When the privilege is_null (not given by the isAllowed helper), get it from the routeMatch
         */
        if (is_null($privilege) && $this->hasRouteMatch()) {
            $this->privilege = $this->getRouteMatch()
                ->getParam('privilege', $this->getRouteMatch()->getParam('action'));
        } else {
            $this->privilege = $privilege;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRouteMatch(): bool
    {
        return !is_null($this->getRouteMatch());
    }

    /**
     * @return RouteMatch
     */
    public function getRouteMatch(): RouteMatch
    {
        return $this->getServiceLocator()->get("Application")->getMvcEvent()->getRouteMatch();
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceLocator(): ContainerInterface
    {
        return $this->serviceLocator;
    }

    /**
     * @param ContainerInterface $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return int|null
     */
    public function getId():?int
    {
        if (!is_null($id = $this->getRequest()->getPost('id'))) {
            return (int)$id;
        }
        if (is_null($this->getRouteMatch())) {
            return null;
        }
        if (!is_null($id = $this->getRouteMatch()->getParam('id'))) {
            return (int)$id;
        }

        return null;
    }

    /**
     * Proxy to the original request object to handle form.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->getServiceLocator()->get('application')->getMvcEvent()->getRequest();
    }

    /**
     * @return ContactService
     */
    public function getContactService(): ContactService
    {
        return $this->contactService;
    }

    /**
     * @param ContactService $contactService
     */
    public function setContactService($contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * @return CalendarService
     */
    public function getCalendarService(): CalendarService
    {
        return $this->calendarService;
    }

    /**
     * @param CalendarService $calendarService
     */
    public function setCalendarService($calendarService)
    {
        $this->calendarService = $calendarService;
    }
}
