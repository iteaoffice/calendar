<?php

namespace CalendarTest\Navigation\Factory;

use Calendar\Navigation\Factory\CalendarNavigationServiceFactory;
use PHPUnit_Framework_TestCase as BaseTestCase;
use Zend\ServiceManager\ServiceManager;

class CalendarNavigationServiceFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN()
    {

        $name = 'testFactory';
        $factory = new CalendarNavigationServiceFactory($name);

        $translate = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $viewHelperManager = $this->getMock("");

        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Configuration',
            array(
                'doctrine' => array(
                    'authentication' => array(
                        $name => array(
                            'objectManager'      => $translate,
                            'identityClass'      => 'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
                            'identityProperty'   => 'username',
                            'credentialProperty' => 'password'
                        ),
                    ),
                ),
            )
        );

        $adapter = $factory->createService($serviceManager);
        $this->assertInstanceOf('Calendar\Navigation\Factory\CalendarNavigationServiceFactory', $adapter);
    }
}
