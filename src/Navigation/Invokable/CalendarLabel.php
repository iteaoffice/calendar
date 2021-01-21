<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Calendar\Navigation\Invokable;

use General\Navigation\Invokable\AbstractNavigationInvokable;
use Calendar\Entity\Calendar;
use Laminas\Navigation\Page\Mvc;

/**
 * Class CalendarLabel
 * @package Calendar\Navigation\Invokable
 */
final class CalendarLabel extends AbstractNavigationInvokable
{
    public function __invoke(Mvc $page): void
    {
        $label = $this->translate('txt-nav-calendar');
        if ($this->getEntities()->containsKey(Calendar::class)) {

            /** @var Calendar $calendar */
            $calendar = $this->getEntities()->get(Calendar::class);

            $page->setParams(
                array_merge(
                    $page->getParams(),
                    [
                        'id' => $calendar->getId(),
                    ]
                )
            );
            $label = (string)$calendar;
        }
        $page->set('label', $label);
    }
}
