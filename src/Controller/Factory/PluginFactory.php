<?php

/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */
declare(strict_types=1);

namespace Calendar\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class ControllerFactory
 *
 * @package Partner\Controller\Factory
 */
final class PluginFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface|PluginManager $container
     * @param                                      $requestedName
     * @param array|null $options
     *
     * @return AbstractPlugin
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var AbstractPlugin $plugin */
        $plugin = new $requestedName($options);

        if (method_exists($plugin, 'setServiceLocator')) {
            $plugin->setServiceLocator($container);
        }

        return $plugin;
    }
}
