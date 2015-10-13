<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category   SoloDB
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 *
 * @version    4.0
 */

namespace Calendar;

use Calendar\Controller\Plugin\RenderCalendarContactList;
use Calendar\Controller\Plugin\RenderReviewCalendar;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature;
use Zend\Mvc\Application;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\MvcEvent;

/**
 * @author
 */
class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\ServiceProviderInterface,
    Feature\ConfigProviderInterface,
    Feature\BootstrapListenerInterface
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

    /**
     * Listen to the bootstrap event.
     *
     * @param EventInterface $e
     *
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {
        /**
         * @var $app Application
         */
        $app = $e->getParam('application');
        $em = $app->getEventManager();
        $em->attach(MvcEvent::EVENT_DISPATCH, function (MvcEvent $event) {
            $event->getApplication()->getServiceManager()
                ->get('calendar_navigation_service')->update();
        });
    }
}
