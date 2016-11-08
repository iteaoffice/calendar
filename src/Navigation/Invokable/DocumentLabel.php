<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

namespace Calendar\Navigation\Invokable;

use Admin\Navigation\Invokable\AbstractNavigationInvokable;
use Calendar\Entity\Calendar;
use Calendar\Entity\Document;
use Zend\Navigation\Page\Mvc;

/**
 * Class ProjectLabel
 *
 * @package Project\Navigation\Invokable
 */
class DocumentLabel extends AbstractNavigationInvokable
{
    /**
     * @param Mvc $page
     *
     * @return void;
     */
    public function __invoke(Mvc $page)
    {
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
        } else {
            $label = $this->translate('txt-nav-document');
        }
        $page->set('label', $label);
    }
}
