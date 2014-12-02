<?php
/**
 * ITEA Office copyright message placeholder
 *
 * PHP Version 5
 *
 * @category    Project
 * @package     Factory
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2014 ITEA Office
 * @license     http://debranova.org/license.txt proprietary
 * @link        http://debranova.org
 */
namespace Calendar\Factory;

use Calendar\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create a link to an project
 *
 * @category   Calendar
 * @package    Factory
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @license    http://debranova.org/licence.txt proprietary
 * @link       http://debranova.org
 */
class OptionServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return ModuleOptions
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        return new ModuleOptions($config['calendar_option']);
    }
}