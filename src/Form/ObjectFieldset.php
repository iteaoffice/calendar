<?php
/**
 * Jield BV all rights reserved.
 *
 * PHP Version 5
 *
 * @category    Project
 *
 * @author      Dr. ir. Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2004-2017 Jield BV (https://jield.nl)
 * @license     https://jield.nl/license.txt proprietary
 *
 */

declare(strict_types=1);

namespace Calendar\Form;

use Calendar\Entity;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Element\EntityMultiCheckbox;
use DoctrineORMModule\Form\Element\EntityRadio;
use DoctrineORMModule\Form\Element\EntitySelect;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\Element;
use Zend\Form\Element\Radio;
use Zend\Form\Fieldset;

/**
 * Class ObjectFieldset
 *
 * @package Calendar\Form
 */
class ObjectFieldset extends Fieldset
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager, Entity\AbstractEntity $object)
    {
        parent::__construct($object->get('underscore_entity_name'));
        $this->entityManager = $entityManager;
        $doctrineHydrator = new DoctrineHydrator($entityManager);
        $this->setHydrator($doctrineHydrator)->setObject($object);
        $builder = new AnnotationBuilder();

        // createForm() already creates a proper form, so attatching its elements
        // to $this is only for backward compatibility
        $data = $builder->createForm($object);
        $this->addElements($data, $object, $this);
    }

    protected function addElements(
        Fieldset $dataFieldset,
        Entity\AbstractEntity $object,
        Fieldset $baseFieldset = null
    ): void {
        /** @var Element $element */
        foreach ($dataFieldset->getElements() as $element) {
            $this->parseElement($element, $object);
            // Add only when a type is provided
            if (!array_key_exists('type', $element->getAttributes())) {
                continue;
            }

            if ($baseFieldset instanceof Fieldset) {
                $baseFieldset->add($element);
            } else {
                $dataFieldset->add($element);
            }
        }

        // Add sub-fieldsets
        foreach ($dataFieldset->getFieldsets() as $subFieldset) {
            /** @var Fieldset $subFieldset */
            $subFieldset->setHydrator($this->getHydrator());
            $subFieldset->setAllowedObjectBindingClass(\get_class($subFieldset->getObject()));
            $this->addElements($subFieldset, $subFieldset->getObject());
            $this->add($subFieldset);
        }
    }

    protected function parseElement(Element $element, Entity\AbstractEntity $object): void
    {
        // Go over each element to add the objectManager to the EntitySelect
        /** Element $element */
        if ($element instanceof EntitySelect || $element instanceof EntityMultiCheckbox
            || $element instanceof EntityRadio
        ) {
            $element->setOptions(
                array_merge(
                    $element->getOptions(),
                    ['object_manager' => $this->entityManager]
                )
            );
        }
        if ($element instanceof Radio && !$element instanceof EntityRadio) {
            $attributes = $element->getAttributes();
            $valueOptionsArray = sprintf('get%s', ucfirst($attributes['array']));
            $element->setOptions(
                array_merge(
                    $element->getOptions(),
                    ['value_options' => $object::$valueOptionsArray()]
                )
            );
        }
    }
}
