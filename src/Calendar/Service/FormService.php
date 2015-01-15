<?php

/**
 * ITEA Office copyright message placeholder
 *
 * @category  Calendar
 * @package   Service
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Service;

use Zend\Form\Form;

class FormService extends ServiceAbstract
{
    /**
     * @var Form
     */
    protected $form;

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
            $entity = $this->getEntity($className);
        }
        $formName = 'calendar_'.$entity->get('underscore_entity_name').'_form';
        $form = $this->getServiceLocator()->get($formName);
        $filterName = 'calendar_'.$entity->get('underscore_entity_name').'_form_filter';
        $filter = $this->getServiceLocator()->get($filterName);
        $form->setInputFilter($filter);
        if ($bind) {
            $form->bind($entity);
        }

        return $form;
    }

    /**
     * @param string $className
     * @param null   $entity
     * @param array  $data
     *
     * @return array|object
     */
    public function prepare($className, $entity = null, $data = [])
    {
        $form = $this->getForm($className, $entity, true);
        $form->setData($data);

        return $form;
    }
}
