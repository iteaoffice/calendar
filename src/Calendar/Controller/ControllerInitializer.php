<?php
/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Controller
 * @package     Service
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   2004-2014 Japaveh Webdesign
 * @license     http://solodb.net/license.txt proprietary
 * @link        http://solodb.net
 */
namespace Calendar\Controller;

use Calendar\Service\CalendarServiceAwareInterface;
use Calendar\Service\FormServiceAwareInterface;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Japaveh Webdesign copyright message placeholder
 *
 * @category    Controller
 * @package     Service
 * @author      Johan van der Heide <info@japaveh.nl>
 * @copyright   2004-2014 Japaveh Webdesign
 * @license     http://solodb.net/license.txt proprietary
 * @link        http://solodb.net
 */
class ControllerInitializer implements InitializerInterface
{
    /**
     * @param                                           $instance
     * @param ServiceLocatorInterface|ControllerManager $serviceLocator
     *
     * @return CalendarAbstractController
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if (!is_object($instance)) {
            return;
        }

        $arrayCheck = [
            FormServiceAwareInterface::class     => 'calendar_form_service',
            CalendarServiceAwareInterface::class => 'calendar_calendar_service',
        ];

        /**
         * @var $sm ServiceLocatorInterface
         */
        $sm = $serviceLocator->getServiceLocator();

        foreach ($arrayCheck as $interface => $serviceName) {
            if (isset(class_implements($instance)[$interface])) {
                $this->setInterface($instance, $interface, $sm->get($serviceName));
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
