<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category  Content
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */

namespace Calendar\Form;

use Calendar\Entity\EntityAbstract;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManager;

/**
 *
 */
class CreateObject extends Form
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Class constructor.
     */
    public function __construct(ServiceManager $serviceManager, EntityAbstract $object)
    {
        parent::__construct($object->get('underscore_entity_name'));
        $this->serviceManager = $serviceManager;
        $entityManager = $this->serviceManager->get('doctrine.entitymanager.orm_default');
        $objectSpecificFieldset = '\Calendar\Form\\'.ucfirst($object->get('entity_name')).'Fieldset';
        /*
         * Load a specific fieldSet when present
         */
        if (class_exists($objectSpecificFieldset)) {
            $objectFieldset = new $objectSpecificFieldset($entityManager, $object);
        } else {
            $objectFieldset = new ObjectFieldset($entityManager, $object);
        }
        $objectFieldset->setUseAsBaseFieldset(true);
        $this->add($objectFieldset);
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'cancel',
                'attributes' => [
                    'class' => "btn btn-warning",
                    'value' => _("txt-cancel"),
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'delete',
                'attributes' => [
                    'class' => "btn btn-danger",
                    'value' => _("txt-delete"),
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-submit"),
                ],
            ]
        );
    }
}
