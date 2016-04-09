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
use Zend\Navigation\Page\Mvc;

/**
 * Class ProjectLabel
 *
 * @package Project\Navigation\Invokable
 */
class CalendarLabel extends AbstractNavigationInvokable
{
    /**
     * @param Mvc $page
     *
     * @return void;
     */
    public function __invoke(Mvc $page)
    {
        if ($this->getEntities()->containsKey(Calendar::class)) {
            /** @var Calendar $calendar */
            $calendar = $this->getEntities()->get(Calendar::class);


            $page->setParams(array_merge($page->getParams(), [
                'id' => $calendar->getId(),
            ]));
            $label = (string)$calendar;
        } else {
            $label = $this->translate('txt-nav-calendar');
        }
        $page->set('label', $label);
    }
}