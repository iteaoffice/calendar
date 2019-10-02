<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Controller;

use Calendar\Entity\Contact;
use Calendar\Entity\ContactRole;
use Calendar\Service\CalendarService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Class JsonController
 *
 * @package Calendar\Controller
 */
final class JsonController extends AbstractActionController
{
    private $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

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

    public function updateStatusAction(): JsonModel
    {
        $calendarContactId = (int)$this->params()->fromPost('id');
        $statusId = (string)$this->params()->fromPost('status');

        /** @var Contact $calendarContact */
        $calendarContact = $this->calendarService->find(Contact::class, $calendarContactId);

        if (null === $calendarContact) {
            return new JsonModel(['result' => 'error']);
        }
        $this->calendarService->updateContactStatus($calendarContact, $statusId);

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }

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
