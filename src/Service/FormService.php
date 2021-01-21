<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\Service;

use Calendar\Entity\AbstractEntity;
use Calendar\Form\CreateObject;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class FormService
 *
 * @package Calendar\Service
 */
class FormService
{
    protected EntityManager $entityManager;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container, EntityManager $entityManager)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
    }

    public function prepare($classNameOrEntity, array $data = [], array $options = []): Form
    {
        if (! $classNameOrEntity instanceof AbstractEntity) {
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
