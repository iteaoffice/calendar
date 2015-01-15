<?php
/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category  Controller
 * @package   Service
 * @author    Johan van der Heide <info@japaveh.nl>
 * @copyright 2004-2014 Japaveh Webdesign
 * @license   http://solodb.net/license.txt proprietary
 * @link      http://solodb.net
 */
namespace Calendar\Controller;

use Calendar\Service\CalendarService;
use Calendar\Service\CalendarServiceAwareInterface;
use Calendar\Service\FormService;
use Calendar\Service\FormServiceAwareInterface;
use Calendar\Service\ModuleOptionAwareInterface;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category  Controller
 * @package   Service
 * @author    Johan van der Heide <info@japaveh.nl>
 * @copyright 2004-2014 Japaveh Webdesign
 * @license   http://solodb.net/license.txt proprietary
 * @link      http://solodb.net
 */
class ControllerInitializer implements InitializerInterface
{
    /**
     * @param                                           $instance
     * @param ControllerManager|ServiceLocatorInterface $controllerManager
     *
     * @return void
     */
    public function initialize($instance, ServiceLocatorInterface $controllerManager)
    {
        if (!is_object($instance)) {
            return;
        }
        $arrayCheck = [
            FormServiceAwareInterface::class     => FormService::class,
            CalendarServiceAwareInterface::class => CalendarService::class,
            ModuleOptionAwareInterface::class    => 'calendar_module_options',
        ];
        /**
         * @var $controllerManager ControllerManager
         */
        $sm = $controllerManager->getServiceLocator();
        /**
         * Go over each interface to see if we should add an interface
         */
        foreach (class_implements($instance) as $interface) {
            if (array_key_exists($interface, $arrayCheck)) {
                $this->setInterface($instance, $interface, $sm->get($arrayCheck[$interface]));
            }
        }

        return;
    }

    /**
     * @param $interface
     * @param $instance
     * @param $service
     */
    protected function setInterface($instance, $interface, $service)
    {
        foreach (get_class_methods($interface) as $setter) {
            if (strpos($setter, 'set') !== false) {
                $instance->$setter($service);
            }
        }
    }
}
