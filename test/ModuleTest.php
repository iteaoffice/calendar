<?php

/**
 * Jield BV all rights reserved
 *
 * @author      Dr. ir. Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2004-2017 Jield BV (https://jield.nl)
 * @license     https://jield.net/license.txt proprietary
 * @link        https://jield.net
 */

declare(strict_types=1);

namespace CalendarTest;

use Calendar\Module;
use Testing\Util\AbstractServiceTest;
use Laminas\Mvc\Application;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\View\HelperPluginManager;

use function is_string;

/**
 * Class ModuleTest
 *
 * @package CalendarTest
 */
class ModuleTest extends AbstractServiceTest
{
    public function testCanFindConfiguration(): void
    {
        $module = new Module();
        $config = $module->getConfig();

        $this->assertArrayHasKey('service_manager', $config);
        $this->assertArrayHasKey(ConfigAbstractFactory::class, $config);
    }

    public function testInstantiationOfConfigAbstractFactories(): void
    {
        $module = new Module();
        $config = $module->getConfig();

        $abstractFacories = $config[ConfigAbstractFactory::class] ?? [];

        foreach ($abstractFacories as $service => $dependencies) {
            //Skip the Filters
            if (strpos($service, 'Filter') !== false) {
                continue;
            }
            if (strpos($service, 'Handler') !== false) {
                continue;
            }

            $instantiatedDependencies = [];
            foreach ($dependencies as $dependency) {
                if ($dependency === 'Application') {
                    $dependency = Application::class;
                }
                if ($dependency === 'Config') {
                    $dependency = [];
                }
                if ($dependency === 'ViewHelperManager') {
                    $dependency = HelperPluginManager::class;
                }

                if (is_string($dependency)) {
                    $instantiatedDependencies[] = $this->getMockBuilder($dependency)->disableOriginalConstructor()
                        ->getMock();
                } else {
                    $instantiatedDependencies[] = [];
                }
            }

            $instance = new $service(...$instantiatedDependencies);

            $this->assertInstanceOf($service, $instance);
        }
    }
}
