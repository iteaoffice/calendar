<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category  Calendar
 * @package   Form
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Form;

use Calendar\Entity\Document;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

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
            [
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'document',
                'options'    => [
                    'label'      => _("txt-document-name"),
                    'help-block' => _("txt-document-name-explanation")
                ],
                'attributes' => [
                    'required'    => true,
                    'class'       => 'form-control',
                    'placeholder' => _("txt-please-give-a-calendar-document-name")
                ]
            ]
        );
        $this->add(
            [
                'type'       => '\Zend\Form\Element\File',
                'name'       => 'file',
                'options'    => [
                    "label"      => "txt-file",
                    "help-block" => _("txt-file-requirements")
                ],
                'attributes' => [
                    'class' => 'form-control',
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-update")
                ]
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'delete',
                'attributes' => [
                    'class' => "btn btn-danger",
                    'value' => _("txt-delete")
                ]
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'cancel',
                'attributes' => [
                    'class' => "btn btn-warning",
                    'value' => _("txt-cancel")
                ]
            ]
        );
    }
}
