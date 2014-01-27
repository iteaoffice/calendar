<?php

/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Service
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form;

class FormService implements ServiceLocatorAwareInterface
{

    /**
     * @var \Zend\Form\Form
     */
    protected $form;
    /**
     * @var \Calendar\Service\CalendarService
     */
    protected $calendarService;
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param null $className
     * @param null $entity
     * @param bool $bind
     *
     * @return array|object
     */
    public function getForm($className = null, $entity = null, $bind = true)
    {
        if (!$entity) {
            $entity = $this->getCalendarService()->getEntity($className);
        }

        $formName = 'calendar_' . $entity->get('underscore_entity_name') . '_form';
        $form     = $this->getServiceLocator()->get($formName);

        $filterName = 'calendar_' . $entity->get('underscore_entity_name') . '_form_filter';
        $filter     = $this->getServiceLocator()->get($filterName);

        $form->setInputFilter($filter);

        if ($bind) {
            $form->bind($entity);
        }

        return $form;
    }

    /**
     * @param       $className
     * @param null  $entity
     * @param array $data
     *
     * @return array|object
     */
    public function prepare($className, $entity = null, $data = array())
    {
        $form = $this->getForm($className, $entity, true);
        $form->setData($data);

        return $form;
    }

    /**
     * @param CalendarService $calendarService
     */
    public function setCalendarService($calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Get calendarService.
     *
     * @return CalendarService.
     */
    public function getCalendarService()
    {
        if (null === $this->calendarService) {
            $this->calendarService = $this->getServiceLocator()->get('calendar_generic_service');
        }

        return $this->calendarService;
    }

    /**
     * Set the service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get the service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
