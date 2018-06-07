<?php

/**
 * Jield copyright message placeholder.
 *
 * @category    Contact
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Form;

use Contact\Entity\Selection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use DoctrineORMModule\Form\Element\EntitySelect;
use Zend\Form\Form;

/**
 * Class CalendarContacts
 *
 * @package Calendar\Form
 */
class CalendarContacts extends Form
{
    /**
     * CalendarContacts constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('onsubmit', 'return storeChanges();');

        $this->add(
            [
                'type'    => EntitySelect::class,
                'name'    => 'selection',
                'options' => [
                    'target_class'   => Selection::class,
                    'object_manager' => $entityManager,
                    'label'          => _("txt-form-calendar-contacts-selection-label"),
                    'help-block'     => _("txt-form-calendar-contacts-selection-help-block"),
                    'find_method'    => [
                        'name'   => 'findBy',
                        'params' => [
                            'criteria' => [],
                            'orderBy'  => ['id' => Criteria::DESC],
                        ],
                    ],
                ],
            ]
        );


        $this->add(
            [
                'type'       => 'Zend\Form\Element\Hidden',
                'name'       => 'added',
                'attributes' => [
                    'id' => 'added',
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Hidden',
                'name'       => 'removed',
                'attributes' => [
                    'id' => 'removed',
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => [
                    'id'    => 'submit',
                    'class' => 'btn btn-primary',
                    'value' => _('txt-submit'),
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'cancel',
                'attributes' => [
                    'id'    => 'cancel',
                    'class' => 'btn btn-warning',
                    'value' => _('txt-cancel'),
                ],
            ]
        );
    }
}
