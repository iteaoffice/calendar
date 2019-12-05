<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Calendar\Entity\Type;
use Calendar\Service\FormService;
use Calendar\Service\CalendarService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

/**
* Class TypeController
 * @package Calendar\Controller
 */
final class TypeController extends AbstractActionController
{
    private CalendarService $calendarService;
    private FormService $formService;

    public function __construct(CalendarService $calendarService, FormService $formService)
    {
        $this->calendarService = $calendarService;
        $this->formService = $formService;
    }

    public function viewAction(): ViewModel
    {
        $type = $this->calendarService->find(Type::class, (int)$this->params('id'));

        if (null === $type) {
            return $this->notFoundAction();
        }

        return new ViewModel(['type' => $type]);
    }

    public function newAction()
    {
        $data = $this->getRequest()->getPost()->toArray();

        $form = $this->formService->prepare(Type::class, $data);
        $form->remove('delete');

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('zfcadmin/calendar/calendar/type/list');
            }

            if ($form->isValid()) {

                /** @var Type $type */
                $type = $form->getData();

                $this->calendarService->save($type);

                return $this->redirect()->toRoute(
                    'zfcadmin/calendar/calendar/type/view',
                    [
                        'id' => $type->getId(),
                    ]
                );
            }
        }

        return new ViewModel(['form' => $form]);
    }

    public function editAction()
    {
        /** @var Type $type */
        $type = $this->calendarService->find(Type::class, (int)$this->params('id'));

        $data = $this->getRequest()->getPost()->toArray();

        $form = $this->formService->prepare($type, $data);

        if (!$type->getCalendar()->isEmpty()) {
            $form->remove('delete');
        }

        if ($this->getRequest()->isPost()) {
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/calendar/calendar/type/view',
                    [
                        'id' => $type->getId(),
                    ]
                );
            }

            if (isset($data['delete'])) {
                $this->calendarService->delete($type);

                return $this->redirect()->toRoute('zfcadmin/calendar/calendar/type/list');
            }

            if ($form->isValid()) {
                /** @var Type $type */
                $type = $form->getData();

                $this->calendarService->save($type);

                return $this->redirect()->toRoute(
                    'zfcadmin/calendar/calendar/type/view',
                    [
                        'id' => $type->getId(),
                    ]
                );
            }
        }

        return new ViewModel(['form' => $form, 'type' => $type]);
    }
}
