<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

namespace Calendar\InputFilter;

use Doctrine\ORM\EntityManager;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\File\Size;

/**
 * Jield webdev copyright message placeholder.
 *
 * @category    DocumentFilter
 *
 * @author      Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2015 Jield (http://jield.nl)
 */
class DocumentFilter extends InputFilter
{
    /**
     * PartnerFilter constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $inputFilter = new InputFilter();

        $inputFilter->add(
            [
                'name'       => 'document',
                'required'   => false,
                'filters'    => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 5,
                            'max'      => 100,
                        ],
                    ],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'contact',
                'required' => false,
            ]
        );
        $fileUpload = new FileInput('file');
        $fileUpload->setRequired(true);
        $fileUpload->getValidatorChain()->attachByName(
            Size::class,
            [
                'min' => '10kB',
                'max' => '8MB',
            ]
        );
        $inputFilter->add($fileUpload);

        $this->add($inputFilter, 'calendar_entity_document');
    }
}
