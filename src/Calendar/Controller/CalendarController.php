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
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Calendar\Service\CalendarService;
use Calendar\Service\FormServiceAwareInterface;
use Calendar\Service\FormService;
use Calendar\Entity;

/**
 * @category    Calendar
 * @package     Controller
 */
class CalendarController extends AbstractActionController implements
    FormServiceAwareInterface, ServiceLocatorAwareInterface
{
    /**
     * @var CalendarService
     */
    protected $calendarService;
    /**
     * @var FormService
     */
    protected $formService;

    /**
     * Message container
     * @return array|void
     */
    public function indexAction()
    {
    }

    /**
     * Give a list of calendars
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function calendarsAction()
    {
        $calendars = $this->getCalendarService()->findAll('calendar');

        return new ViewModel(array('calendars' => $calendars));
    }

    /**
     * Show the details of 1 calendar
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function calendarAction()
    {
        $calendar = $this->getCalendarService()->findEntityById(
            'calendar',
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        return new ViewModel(array('calendar' => $calendar));
    }

    /**
     * Edit an entity
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $this->layout(false);
        $entity = $this->getCalendarService()->findEntityById(
            $this->getEvent()->getRouteMatch()->getParam('entity'),
            $this->getEvent()->getRouteMatch()->getParam('id')
        );

        $form = $this->getFormService()->prepare($entity->get('entity_name'), $entity, $_POST);
        $form->setAttribute('class', 'form-vertical live-form-edit');
        $form->setAttribute('id', 'calendar-' . strtolower($entity->get('entity_name')) . '-' . $entity->getId());

        if ($this->getRequest()->isPost() && $form->isValid()) {
            $this->getCalendarService()->updateEntity($form->getData());

            $view = new ViewModel(array($this->getEvent()->getRouteMatch()->getParam('entity') => $form->getData()));
            $view->setTemplate(
                "calendar/partial/" . $this->getEvent()->getRouteMatch()->getParam('entity') . '.twig'
            );

            return $view;
        }

        return new ViewModel(array('form' => $form, 'entity' => $entity));
    }

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
     * @return FormService
     */
    public function getFormService()
    {
        return $this->formService;
    }

    /**
     * @param $formService
     *
     * @return CalendarController
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
        return $this->getServiceLocator()->get('calendar_generic_service');
    }

    /**
     * @param $calendarService
     *
     * @return CalendarController
     */
    public function setCalendarService($calendarService)
    {
        $this->calendarService = $calendarService;

        return $this;
    }
}
