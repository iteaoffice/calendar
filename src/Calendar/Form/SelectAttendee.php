<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Calendar\Form;

use Calendar\Service\CalendarService;
use Contact\Service\ContactService;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 *
 */
class SelectAttendee extends Form implements InputFilterProviderInterface
{
    /**
     * @param CalendarService $calendarService
     * @param ContactService  $contactService
     */
    public function __construct(CalendarService $calendarService, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('action', '');

        $contacts = [];
        foreach ($contactService->findPossibleContactByCalendar($calendarService->getCalendar()) as $contact) {
            $contacts[$contact->getId()] = $contact->getDisplayName();
        }

        $this->add(
            [
                'type'    => 'Zend\Form\Element\MultiCheckbox',
                'name'    => 'contact',
                'options' => [
                    'value_options' => $contacts,
                    'label'         => _("txt-contact-name"),
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'Zend\Form\Element\Submit',
                'name'       => 'submit',
                'attributes' => [
                    'class' => "btn btn-primary",
                    'value' => _("txt-update"),
                ],
            ]
        );
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
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'contact' => [
                'required' => true,
            ],
        ];
    }
}
