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

use Admin\Navigation\Invokable\AbstractNavigationInvokable;
use Calendar\Entity\Calendar;
use Calendar\Entity\Document;
use Laminas\Navigation\Page\Mvc;

/**
 * Class ProjectLabel
 *
 * @package Project\Navigation\Invokable
 */
final class DocumentLabel extends AbstractNavigationInvokable
{
    public function __invoke(Mvc $page): void
    {
        $label = $this->translate('txt-nav-document');

        if ($this->getEntities()->containsKey(Document::class)) {
            /** @var Document $document */
            $document = $this->getEntities()->get(Document::class);
            $this->getEntities()->set(Calendar::class, $document->getCalendar());

            $page->setParams(
                array_merge(
                    $page->getParams(),
                    [
                        'id' => $document->getId(),
                    ]
                )
            );
            $label = (string)$document;
        }
        $page->set('label', $label);
    }
}
