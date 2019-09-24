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

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Textarea;

/**
 * Class SendMessage
 *
 * @package Calendar\Form
 */
final class SendMessage extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('action', '');

        $this->add(
            [
                'type'       => Textarea::class,
                'name'       => 'message',
                'options'    => [
                    'label'      => _('txt-message'),
                    'help-block' => _('txt-send-message-to-calendar-attendees'),
                ],
                'attributes' => [
                    'rows'  => 20,
                    'class' => 'form-control',
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
                    'value' => _('txt-send'),
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
            'message' => [
                'required' => true,
            ],
        ];
    }
}
