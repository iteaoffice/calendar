<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category   SoloDB
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2014 ITEA Office (https://itea3.org)
 *
 * @version    4.0
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
class Module implements Feature\AutoloaderProviderInterface, Feature\ServiceProviderInterface, Feature\ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/../../autoload_classmap.php',
            ],
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Go to the service configuration.
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../../config/services.config.php';
    }

    /**
     * @return array
     */
    public function getViewHelperConfig()
    {
        return include __DIR__ . '/../../config/viewhelpers.config.php';
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
