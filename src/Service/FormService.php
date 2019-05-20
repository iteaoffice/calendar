<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Calendar
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2018 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/main for the canonical source repository
 */
declare(strict_types=1);

namespace Calendar\Service;

use Calendar\Entity\AbstractEntity;
use Calendar\Form\CreateObject;
use Doctrine\ORM\EntityManager;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class FormService
 *
 * @package Calendar\Service
 */
class FormService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var ServiceLocatorInterface
     */
    private $container;

    public function __construct(ServiceLocatorInterface $container, EntityManager $entityManager)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
    }

    public function prepare($classNameOrEntity, array $data = [], array $options = []): Form
    {
        if (!$classNameOrEntity instanceof AbstractEntity) {
            $classNameOrEntity = new $classNameOrEntity();
        }

        $form = $this->getForm($classNameOrEntity, $options);
        $form->setData($data);

        return $form;
    }

    private function getForm(AbstractEntity $entity, array $options = []): Form
    {
        $formName = $entity->get('entity_form_name');
        $filterName = $entity->get('entity_inputfilter_name');

        /**
         * The filter and the form can dynamically be created by pulling the form from the serviceManager
         * if the form or filter is not give in the serviceManager we will create it by default
         */
        if ($this->container->has($formName)) {
            $form = $this->container->build($formName, $options);
        } else {
            $form = new CreateObject($this->entityManager, $entity, $this->container);
        }

        if ($this->container->has($filterName)) {
            /** @var InputFilter $filter */
            $filter = $this->container->get($filterName);
            $form->setInputFilter($filter);
        }

        $form->setAttribute('role', 'form');
        $form->setAttribute('action', '');
        $form->setAttribute('class', 'form-horizontal');

        $form->bind($entity);

        return $form;
    }
}
