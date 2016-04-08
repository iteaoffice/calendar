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

namespace Calendar;

use Calendar\Controller\Plugin\RenderCalendarContactList;
use Calendar\Controller\Plugin\RenderReviewCalendar;
use Calendar\Navigation;
use Zend\ModuleManager\Feature;
use Zend\Mvc\Controller\PluginManager;

/**
 * @author
 */
class Module implements Feature\AutoloaderProviderInterface, Feature\ConfigProviderInterface
{
    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/../autoload_classmap.php',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Move this to here to have config cache working.
     *
     * @return array
     */
    public function getControllerPluginConfig()
    {
        return [
            'factories' => [
                'renderCalendarContactList' => function (PluginManager $sm) {
                    $renderCalendarContactList
                        = new RenderCalendarContactList();
                    $renderCalendarContactList->setServiceLocator($sm->getServiceLocator());

                    return $renderCalendarContactList;
                },
                'renderReviewCalendar'      => function (PluginManager $sm) {
                    $renderReviewCalendar = new RenderReviewCalendar();
                    $renderReviewCalendar->setServiceLocator($sm->getServiceLocator());

                    return $renderReviewCalendar;
                },
            ],
        ];
    }
}
