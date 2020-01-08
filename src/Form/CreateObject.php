<?php

/**
 * Jield BV all rights reserved.
 *
 * @category    Application
 *
 * @author      Dr. ir. Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2004-2017 Jield (https://jield.nl)
 */

declare(strict_types=1);

namespace Calendar\Form;

use Calendar\Entity\AbstractEntity;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\Form\Element;
use Laminas\Form\Form;

/**
 * Class CreateObject
 *
 * @package General\Form
 */
final class CreateObject extends Form
{
    public function __construct(
        EntityManager $entityManager,
        AbstractEntity $object,
        ContainerInterface $serviceManager
    ) {
        parent::__construct($object->get('entity_name'));

        /**
         * There is an option to drag the fieldset from the serviceManager,
         * We then need to check if if an factory is present,
         * If not we will use the default ObjectFieldset
         */

        $objectSpecificFieldset = $object->get('entity_fieldset_name');

        /**
         * Load a specific fieldSet when present
         */
        if ($serviceManager->has($objectSpecificFieldset)) {
            $objectFieldset = $serviceManager->get($objectSpecificFieldset);
        } elseif (class_exists($objectSpecificFieldset)) {
            $objectFieldset = new $objectSpecificFieldset($entityManager, $object);
        } else {
            $objectFieldset = new ObjectFieldset($entityManager, $object);
        }

        $objectFieldset->setUseAsBaseFieldset(true);
        $this->add($objectFieldset);

        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');

        $this->add(
            [
                'type' => Element\Csrf::class,
                'name' => 'csrf',
            ]
        );

        $this->add(
            [
                'type'       => Element\Submit::class,
                'name'       => 'submit',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-submit"),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Element\Submit::class,
                'name'       => 'cancel',
                'attributes' => [
                    'class' => "btn btn-warning",
                    'value' => _("txt-cancel"),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Element\Submit::class,
                'name'       => 'delete',
                'attributes' => [
                    'class' => "btn btn-danger",
                    'value' => _("txt-delete"),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Element\Submit::class,
                'name'       => 'restore',
                'attributes' => [
                    'class' => "btn btn-info",
                    'value' => _("txt-restore"),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Element\Submit::class,
                'name'       => 'redirect',
                'attributes' => [
                    'class' => "btn btn-info",
                    'value' => _("txt-redirect-to-front"),
                ],
            ]
        );
    }
}
