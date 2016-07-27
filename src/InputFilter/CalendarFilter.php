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
use DoctrineModule\Validator;
use Partner\Entity\Affiliation;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Callback;

/**
 * Jield webdev copyright message placeholder.
 *
 * @category    CalendarFilter
 *
 * @author      Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2015 Jield (http://jield.nl)
 */
class CalendarFilter extends InputFilter
{
    /**
     * PartnerFilter constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name'     => 'calendar',
            'required' => true,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'location',
            'required' => false,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'dateFrom',
            'required'   => true,
            'filters'    => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'DateTime',
                    'options' => [
                        'pattern' => 'yyyy-mm-dd HH:mm',
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name'     => 'final',
            'required' => true,
        ]);
        $inputFilter->add([
            'name'     => 'onHomepage',
            'required' => true,
        ]);
        $inputFilter->add([
            'name'       => 'sequence',
            'required'   => false,
            'filters'    => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                ['name' => 'Int'],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'url',
            'required' => false,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'imageUrl',
            'required' => false,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'call',
            'required' => false,
        ]);
        $inputFilter->add([
            'name'       => 'dateEnd',
            'required'   => false,
            'filters'    => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'DateTime',
                    'options' => [
                        'pattern' => 'yyyy-mm-dd HH:mm',
                    ],
                ],
                [
                    'name'    => 'Callback',
                    'options' => [
                        'messages' => [
                            Callback::INVALID_VALUE => 'The end date cannot be smaller than start date',
                        ],
                        'callback' => function ($value, $context = []) {
                            $dateFrom = \DateTime::createFromFormat('Y-m-d H:i', $context['dateFrom']);
                            $dateEnd = \DateTime::createFromFormat('Y-m-d H:i', $value);

                            return $dateEnd >= $dateFrom;
                        },
                    ],
                ],
            ],
        ]);

        $this->add($inputFilter, 'calendar_entity_calendar');
    }
}