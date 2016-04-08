<?php

/**
 * Jield copyright message placeholder.
 *
 * @category    Contact
 *
 * @author      Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2004-2015 Jield (http://jield.nl)
 */

namespace Calendar\Form;

use Contact\Entity\Selection;
use Contact\Service\SelectionService;
use Zend\Form\Form;

/**
 * Jield copyright message placeholder.
 *
 * @category    Contact
 *
 * @author      Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2004-2015 Jield (http://jield.nl)
 */
class CalendarContacts extends Form
{
    /**
     * CalendarContacts constructor.
     *
     * @param SelectionService $selectionService
     */
    public function __construct(SelectionService $selectionService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute("onsubmit", "return storeChanges();");

        $selections = [];
        /** @var Selection $selection */
        foreach ($selectionService->findAll(Selection::class) as $selection) {
            $selections[$selection->getId()] = $selection->getSelection();
        }

        asort($selections);

        $this->add([
            'type'       => 'Zend\Form\Element\Select',
            'name'       => 'selection',
            'options'    => [
                'inline'        => true,
                'value_options' => $selections
            ],
            'attributes' => [
                'id'    => 'selection',
                'class' => 'form-control',
            ],
        ]);


        $this->add([
            'type'       => 'Zend\Form\Element\Hidden',
            'name'       => 'added',
            'attributes' => [
                'id' => 'added',
            ],
        ]);

        $this->add([
            'type'       => 'Zend\Form\Element\Hidden',
            'name'       => 'removed',
            'attributes' => [
                'id' => 'removed',
            ],
        ]);

        $this->add([
            'type'       => 'Zend\Form\Element\Submit',
            'name'       => 'submit',
            'attributes' => [
                'id'    => 'submit',
                'class' => 'btn btn-primary',
                'value' => _('txt-submit'),
            ],
        ]);

        $this->add([
            'type'       => 'Zend\Form\Element\Submit',
            'name'       => 'cancel',
            'attributes' => [
                'id'    => 'cancel',
                'class' => 'btn btn-warning',
                'value' => _('txt-cancel'),
            ],
        ]);
    }
}
