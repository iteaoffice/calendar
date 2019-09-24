<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Calendar
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\Form;

use Calendar\Entity\Calendar;
use Contact\Entity\Contact;
use Contact\Service\ContactService;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\MultiCheckbox;

/**
 * Class SelectAttendee
 *
 * @package Calendar\Form
 */
final class SelectAttendee extends Form implements InputFilterProviderInterface
{
    public function __construct(Calendar $calendar, ContactService $contactService)
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('action', '');

        $contacts = [];
        /** @var Contact $contact */
        foreach ($contactService->findPossibleContactByCalendar($calendar) as $contact) {
            $contacts[$contact->getId()] = sprintf(
                '%s (%s, %s)',
                $contact->getDisplayName(),
                $contact->getContactOrganisation()->getOrganisation(),
                $contact->getContactOrganisation()->getOrganisation()->getCountry()
            );
        }

        $this->add(
            [
                'type'    => MultiCheckbox::class,
                'name'    => 'contact',
                'options' => [
                    'value_options' => $contacts,
                    'label'         => _('txt-contact-name'),
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
                    'value' => _('txt-update'),
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
            'contact' => [
                'required' => true,
            ],
        ];
    }
}
