<?php

/**
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

declare(strict_types=1);

namespace Calendar\Navigation\Invokable;

use General\Navigation\Invokable\AbstractNavigationInvokable;
use Calendar\Entity\Calendar;
use Laminas\Navigation\Page\Mvc;

/**
 * Class ProjectLabel
 *
 * @package Project\Navigation\Invokable
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
