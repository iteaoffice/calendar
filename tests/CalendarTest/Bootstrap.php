<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category    Program
 * @package     Test
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace CalendarTest;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaValidator;
use RuntimeException;
use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $serviceManager;

    public static function init()
    {
        $zf2ModulePaths = array(dirname(dirname(__DIR__)));
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
        $config = include __DIR__ . '/../config/application.config.php';
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;

        if (defined("TEST_SUITE") && constant("TEST_SUITE") == 'full') {

            $entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
            //Validate the schema;
            $validator = new SchemaValidator($entityManager);
            $errors = $validator->validateMapping();
            if (count($errors) > 0) {
                foreach ($errors as $entity => $errors) {
                    echo "Error in Entity: '" . $entity . "':\n";
                    echo implode("\n", $errors);
                    echo "\n";
                }
                die();
            }
            //Create the schema
            $tool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
            $mdFactory = $entityManager->getMetadataFactory();
            $mdFactory->getAllMetadata();
            $tool->dropDatabase();
            $tool->createSchema($mdFactory->getAllMetadata());
            $loader = new Loader();
            $loader->addFixture(new \AdminTest\Fixture\LoadAccessData());
            $purger = new ORMPurger();
            $executor = new ORMExecutor($entityManager, $purger);
            $executor->execute($loader->getFixtures());
        }
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

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');
        $zf2Path = getenv('ZF2_PATH');
        if (!$zf2Path) {
            if (defined('ZF2_PATH')) {
                $zf2Path = ZF2_PATH;
            } elseif (is_dir($vendorPath . '/ZF2/library')) {
                $zf2Path = $vendorPath . '/ZF2/library';
            } elseif (is_dir($vendorPath . '/zendframework/zendframework/library')) {
                $zf2Path = $vendorPath . '/zendframework/zendframework/library';
            }
        }
        if (!$zf2Path) {
            throw new RuntimeException(
                'Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.'
            );
        }
        if (file_exists($vendorPath . '/autoload.php')) {
            include $vendorPath . '/autoload.php';
        }
        include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
        AutoloaderFactory::factory(
            array(
                'Zend\Loader\StandardAutoloader' => array(
                    'autoregister_zf' => true,
                    'namespaces'      => array(
                        __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                        'Admin'       => __DIR__ . '/../../../../module/Admin'
                    ),
                ),
            )
        );
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }
}

Bootstrap::init();
