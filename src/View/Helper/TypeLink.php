<?php

/**
 * Jield copyright message placeholder.
 *
 * @category    Admin
 *
 * @author      Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2004-2015 Jield (http://jield.nl)
 */

declare(strict_types=1);

namespace Calendar\View\Helper;

use Calendar\Entity\Type;

/**
 * Class TypeLink
 *
 * @package Calendar\View\Helper
 */
final class TypeLink extends AbstractLink
{
    public function __invoke(Type $type = null, string $action = 'view', string $show = 'name'): string
    {
        $this->reset();

        $this->extractLinkContentFromEntity($type, ['type']);
        $this->extractRouterParams($type, ['id']);


        $this->parseAction($action, $type);

        return $this->createLink($show);
    }

    private function parseAction(string $action, ?Type $type): void
    {
        $this->action = $action;

        switch ($action) {
            case 'edit':
                $this->setRouter('zfcadmin/calendar/type/edit');
                $this->setText(sprintf($this->translate('txt-edit-calendar-type-%s'), $type));
                break;
            case 'view':
                $this->setRouter('zfcadmin/calendar/type/view');
                $this->setText(sprintf($this->translate('txt-view-calendar-type-%s'), $type));
                break;
            case 'new':
                $this->setRouter('zfcadmin/calendar/type/new');
                $this->setText($this->translate('txt-new-calendar-type'));
                break;
        }
    }
}
