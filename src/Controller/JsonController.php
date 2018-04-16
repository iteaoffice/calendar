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

namespace Calendar\Controller;

use Calendar\Entity\Contact;
use Calendar\Entity\ContactRole;
use Calendar\Service\CalendarService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 *
 */
class JsonController extends AbstractActionController
{
    /**
     * @var CalendarService
     */
    protected $calendarService;

    /**
     * JsonController constructor.
     *
     * @param CalendarService $calendarService
     */
    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }


    /**
     * @return JsonModel
     */
    public function getRolesAction(): JsonModel
    {
        /** @var ContactRole[] $calendarRoles */
        $calendarRoles = $this->calendarService->findAll(ContactRole::class);

        $roles = [];
        /**
         * @var $role ContactRole
         */
        foreach ($calendarRoles as $role) {
            $roles[] = [
                'value' => $role->getId(),
                'text'  => $role->getRole(),
            ];
        }

        return new JsonModel($roles);
    }

    /**
     * @return JsonModel|ViewModel
     */
    public function updateRoleAction()
    {
        $calendarContactId = (int)$this->params()->fromPost('pk');
        $roleId = (int)$this->params()->fromPost('value');

        /**
         * @var Contact $calendarContact
         */
        $calendarContact = $this->calendarService->find(Contact::class, $calendarContactId);

        if (null === $calendarContact) {
            return new JsonModel();
        }
        /**
         * @var ContactRole $role
         */
        $role = $this->calendarService->find(ContactRole::class, $roleId);

        if (null === $role) {
            return $this->notFoundAction();
        }

        $calendarContact->setRole($role);
        $this->calendarService->save($calendarContact);

        return new JsonModel();
    }
}
