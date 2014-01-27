<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Controller
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Calendar\Service\FormServiceAwareInterface;
use Calendar\Service\CalendarService;
use Calendar\Service\FormService;

/**
 *
 */
class CalendarManagerController extends AbstractActionController implements
    FormServiceAwareInterface, ServiceLocatorAwareInterface
{

    /**
     * @var CalendarService;
     */
    protected $calendarService;
    /**
     * @var FormService
     */
    protected $formService;
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Trigger to switch layout
     *
     * @param $layout
     */
    public function layout($layout)
    {
        if (false === $layout) {
            $this->getEvent()->getViewModel()->setTemplate('layout/nolayout');
        } else {
            $this->getEvent()->getViewModel()->setTemplate('layout/' . $layout);
        }
    }

    /**
     * Give a list of messages
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function messagesAction()
    {
        $messages = $this->getCalendarService()->findAll('message');

        return new ViewModel(array('messages' => $messages));
    }

    /**
     * Show the details of 1 message
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function messageAction()
    {
        $message = $this->getCalendarService()->findEntityById(
            'message',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        return new ViewModel(array('message' => $message));
    }

    /**
     * Create a new entity
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function newAction()
    {
        $entity = $this->getEvent()->getRouteMatch()->getParam('entity');
        $form   = $this->getFormService()->prepare($this->params('entity'), null, $_POST);

        $form->setAttribute('class', 'form-horizontal');

        if ($this->getRequest()->isPost() && $form->isValid()) {
            $result = $this->getCalendarService()->newEntity($form->getData());
            $this->redirect()->toRoute(
                'zfcadmin/calendar-manager/' . strtolower($this->params('entity')),
                array('id' => $result->getId())
            );
        }

        return new ViewModel(array('form' => $form, 'entity' => $entity, 'fullVersion' => true));
    }

    /**
     * Edit an entity by finding it and call the corresponding form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $entity = $this->getCalendarService()->findEntityById(
            $this->getEvent()->getRouteMatch()->getParam('entity'),
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        $form = $this->getFormService()->prepare($entity->get('entity_name'), $entity, $_POST);
        $form->setAttribute('class', 'form-horizontal live-form');
        $form->setAttribute('id', 'calendar-calendar-' . $entity->getId());

        if ($this->getRequest()->isPost() && $form->isValid()) {
            $result = $this->getCalendarService()->updateEntity($form->getData());
            $this->redirect()->toRoute(
                'zfcadmin/calendar/' . strtolower($entity->get('dashed_entity_name')),
                array('id' => $result->getId())
            );
        }

        return new ViewModel(array('form' => $form, 'entity' => $entity, 'fullVersion' => true));
    }

    /**
     * (soft-delete) an entity
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $entity = $this->getCalendarService()->findEntityById(
            $this->getEvent()->getRouteMatch()->getParam('entity'),
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        $this->getCalendarService()->removeEntity($entity);

        return $this->redirect()->toRoute(
            'zfcadmin/calendar-manager/' . $entity->get('dashed_entity_name') . 's'
        );
    }

    /**
     * @return \Calendar\Service\FormService
     */
    public function getFormService()
    {
        return $this->formService;
    }

    /**
     * @param $formService
     *
     * @return CalendarManagerController
     */
    public function setFormService($formService)
    {
        $this->formService = $formService;

        return $this;
    }

    /**
     * Gateway to the Calendar Service
     *
     * @return CalendarService
     */
    public function getCalendarService()
    {
        return $this->getServiceLocator()->get('calendar_calendar_service');
    }

    /**
     * @param $calendarService
     *
     * @return CalendarManagerController
     */
    public function setCalendarService($calendarService)
    {
        $this->calendarService = $calendarService;

        return $this;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CalendarManagerController|void
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }
}
