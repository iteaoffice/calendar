<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Form;

use Calendar\Entity\Document;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Class CreateCalendarDocument
 *
 * @package Calendar\Form
 */
final class CreateCalendarDocument extends Form implements InputFilterProviderInterface
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct();
        $document = new Document();
        $doctrineHydrator = new DoctrineHydrator($entityManager);
        $this->setHydrator($doctrineHydrator)->setObject($document);
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('id', 'create-document');

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Text',
                'name'       => 'document',
                'options'    => [
                    'label'      => _('txt-document-name'),
                    'help-block' => _('txt-document-name-explanation'),
                ],
                'attributes' => [
                    'class'       => 'form-control',
                    'placeholder' => _('txt-please-give-a-calendar-document-name'),
                ],
            ]
        );
        $this->add(
            [
                'type'    => '\Zend\Form\Element\File',
                'name'    => 'file',
                'options' => [
                    'label'      => 'txt-file',
                    'class'      => 'form-control',
                    'help-block' => _('txt-calendar-document-file-help-block'),
                ],
            ]
        );
        $this->add(
            [
                'type' => '\Zend\Form\Element\Csrf',
                'name' => 'csrf',
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-upload-document'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'delete',
                'attributes' => [
                    'class' => 'btn btn-danger',
                    'value' => _('txt-delete'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'cancel',
                'attributes' => [
                    'class' => 'btn btn-warning',
                    'value' => _('txt-cancel'),
                ],
            ]
        );
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'file' => [
                'required' => true,
            ],
        ];
    }
}
