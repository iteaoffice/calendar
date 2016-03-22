<?php

/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Calendar
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/calendar for the canonical source repository
 */
namespace CalendarTest;

use RuntimeException;
use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);


/**
 * Test bootstrap, for setting up autoloading.
 */
class Bootstrap
{
    /**
     * @var ServiceManager
     */
    protected static $serviceManager;

    /**
     * @return ServiceManager
     */
    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    public static function init()
    {
        $zf2ModulePaths = [dirname(dirname(__DIR__))];
        if (($path = static::findParentPath('vendor'))) {
            $zf2ModulePaths[] = $path;
        }
        if (($path = static::findParentPath('module'))) {
            $zf2ModulePaths[] = $path;
        }
        if (($path = static::findParentPath('src')) !== $zf2ModulePaths[0]) {
            $zf2ModulePaths[] = $path;
        }

        static::initAutoloader();

        // use ModuleManager to load this module and it's dependencies
        $config = [
            'module_listener_options' => [
                'module_paths' => $zf2ModulePaths,
            ],
            'modules'                 => [
                'Calendar',
            ],
        ];


        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;
    }

    public static function chroot()
    {
        $rootPath = dirname(static::findParentPath('module'));
        chdir($rootPath);
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (file_exists($vendorPath . '/autoload.php')) {
            include $vendorPath . '/autoload.php';
        }

        if (!class_exists('Zend\Loader\AutoloaderFactory')) {
            throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install`');
        }

        AutoloaderFactory::factory([
            'Zend\Loader\StandardAutoloader' => [
                'autoregister_zf' => true,
                'namespaces'      => [
                    __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                ],
            ],
        ]);
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }

        return $dir . '/' . $path;
    }
}

Bootstrap::init();
Bootstrap::chroot();
