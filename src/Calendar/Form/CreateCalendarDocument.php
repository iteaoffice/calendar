<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     Form
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Form;

use Zend\Form\Form;
use Doctrine\ORM\EntityManager;
use Calendar\Entity\Document;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 *
 */
class CreateCalendarDocument extends Form
{
    /**
     * Class constructor
     */
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct();

        $document = new Document();

        $doctrineHydrator = new DoctrineHydrator($entityManager);
        $this->setHydrator($doctrineHydrator)->setObject($document);

        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('id', 'create-document');

        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'document',
                'options'    => array(
                    'label'      => _("txt-document-name"),
                    'help-block' => _("txt-document-name-explanation")
                ),
                'attributes' => array(
                    'required'    => true,
                    'class'       => 'form-control',
                    'placeholder' => _("txt-please-give-a-calendar-document-name")
                )
            )
        );

        $this->add(
            array(
                'type' => 'Zend\Form\Element\Csrf',
                'name' => 'csrf',
            )
        );

        $this->add(
            array(
                'type'       => '\Zend\Form\Element\File',
                'name'       => 'file',
                'options'    => array(
                    "label"      => "txt-file",
                    "help-block" => _("txt-file-requirements")
                ),
                'attributes' => array(
                    'class' => 'form-control',
                ),
            )
        );

        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => array(
                    'class' => "btn btn-primary",
                    'value' => _("txt-update")
                )
            )
        );

        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'delete',
                'attributes' => array(
                    'class' => "btn btn-danger",
                    'value' => _("txt-delete")
                )
            )
        );

        $this->add(
            array(
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'cancel',
                'attributes' => array(
                    'class' => "btn btn-warning",
                    'value' => _("txt-cancel")
                )
            )
        );
    }
}
