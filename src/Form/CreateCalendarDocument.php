<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\Form;

use Calendar\Entity\Document;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\ORM\EntityManager;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

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
        $document         = new Document();
        $doctrineHydrator = new DoctrineHydrator($entityManager);
        $this->setHydrator($doctrineHydrator)->setObject($document);
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('id', 'create-document');

        $this->add(
            [
                'type'       => Text::class,
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
                'type'    => File::class,
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
                'type' => Csrf::class,
                'name' => 'csrf',
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'submit',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-upload-document'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'delete',
                'attributes' => [
                    'class' => 'btn btn-danger',
                    'value' => _('txt-delete'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
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
