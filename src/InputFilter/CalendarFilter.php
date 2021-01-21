<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\InputFilter;

use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Callback;

/**
 * Class CalendarFilter
 *
 * @package Calendar\InputFilter
 */
final class CalendarFilter extends InputFilter
{
    public function __construct()
    {
        $inputFilter = new InputFilter();

        $inputFilter->add(
            [
                'name'     => 'calendar',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'location',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ]
        );
        $inputFilter->add(
            [
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
            ]
        );
        $inputFilter->add(
            [
                'name'       => 'dateEnd',
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
            ]
        );

        $inputFilter->add(
            [
                'name'     => 'final',
                'required' => true,
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'onHomepage',
                'required' => true,
            ]
        );
        $inputFilter->add(
            [
                'name'       => 'sequence',
                'required'   => false,
                'filters'    => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    ['name' => 'Int'],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'url',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'imageUrl',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'image',
                'required' => false,
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'call',
                'required' => false,
            ]
        );
        $inputFilter->add(
            [
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
                                $dateEnd  = \DateTime::createFromFormat('Y-m-d H:i', $value);

                                return $dateEnd >= $dateFrom;
                            },
                        ],
                    ],
                ],
            ]
        );

        $this->add($inputFilter, 'calendar_entity_calendar');
    }
}
