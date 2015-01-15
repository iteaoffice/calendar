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

use Calendar\Service\CalendarService;
use Contact\Service\ContactService;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 *
 */
class SendMessage extends Form implements InputFilterProviderInterface
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

        $this->add(
            [
                'type'      => 'Zend\Form\Element\Textarea',
                'name'      => 'message',
                'options'   => [
                    'label'      => _("txt-message"),
                    'help-block' => _("txt-send-message-to-calendar-attendees"),
                ],
                'attributes' => [
                    'rows'  => 20,
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
                    'value' => _("txt-send"),
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
            'message' => [
                'required' => true,
            ]
        ];
    }
}
