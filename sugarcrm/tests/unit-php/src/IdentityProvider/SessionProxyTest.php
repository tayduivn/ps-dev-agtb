<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestUnit\IdentityProvider;

use Sugarcrm\Sugarcrm\IdentityProvider\SessionProxy;
use Sugarcrm\Sugarcrm\Session\SessionStorage;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\SessionProxy
 */
class SessionProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var sessionStorage
     */
    protected $sessionStorage;

    /**
     * @var SessionProxy
     */
    protected $sessionProxy;

    /**
     * @var SessionStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionStorageMocked;

    /**
     * @var SessionProxy
     */
    protected $sessionProxyMockedStorage;

    /**
     * @var string
     */
    protected $sessionKey;

    /**
     * @var string
     */
    protected $sessionValue;

    /**
     * @covers ::__construct
     */
    public function testIfSessionStarted()
    {
        /** @var SessionStorage|\PHPUnit_Framework_MockObject_MockObject $storage */
        $storage = $this->createMock(SessionStorage::class);
        $storage->method('sessionHasId')
            ->willReturn(true);
        $storage->expects($this->never())
            ->method('start');

        $sessionProxy = new SessionProxy($storage);
    }

    /**
     * @covers ::__construct
     */
    public function testIfSessionNotStarted()
    {
        /** @var SessionStorage|\PHPUnit_Framework_MockObject_MockObject $storage */
        $storage = $this->createMock(SessionStorage::class);

        $storage->method('sessionHasId')->willReturn(false);
        $storage->expects($this->once())
            ->method('start');

        $sessionProxy = new SessionProxy($storage);
    }

    /**
     * @covers ::start
     */
    public function testStart()
    {
        $expectedIsStarted = rand(1000, 9999);
        $this->sessionStorageMocked
            ->expects($this->once())
            ->method('start');
        $this->sessionStorageMocked
            ->expects($this->once())
            ->method('sessionHasId')
            ->willReturn($expectedIsStarted);

        $this->assertEquals($expectedIsStarted, $this->sessionProxyMockedStorage->start());
    }

    /**
     * @covers ::getId
     */
    public function testGetId()
    {
        $expectedSessionId = rand(1000, 9999);
        $this->sessionStorageMocked
            ->expects($this->once())
            ->method('getId')
            ->willReturn($expectedSessionId);
        $this->assertEquals($expectedSessionId, $this->sessionProxyMockedStorage->getId());
    }

    /**
     * @covers ::setId
     */
    public function testSetId()
    {
        $expectedSessionId = rand(1000, 9999);
        $this->sessionStorageMocked
            ->expects($this->once())
            ->method('setId')
            ->with($this->equalTo($expectedSessionId));

        $this->sessionProxyMockedStorage->setId($expectedSessionId);
    }

    /**
     * @covers ::getName
     */
    public function testGetName()
    {
        $this->assertEquals(session_name(), $this->sessionProxyMockedStorage->getName());
    }

    /**
     * @covers ::isStarted
     */
    public function testIsStarted()
    {

        $expectedIsStarted = rand(1000, 9999);
        $this->sessionStorageMocked
            ->expects($this->once())
            ->method('sessionHasId')
            ->willReturn($expectedIsStarted);

        $this->assertEquals($expectedIsStarted, $this->sessionProxyMockedStorage->isStarted());
    }

    /**
     * Testing unsupported methods.
     * @dataProvider unsupportedMethodsProvider
     * @param $methods
     * @param $arguments
     * @expectedException \LogicException
     * @covers ::setId
     * @covers ::setName
     * @covers ::invalidate
     * @covers ::migrate
     * @covers ::getBag
     * @covers ::getMetadataBag
     * @covers ::registerBag
     */
    public function testUnsupportedMethods($methods, $arguments)
    {
        call_user_func_array([$this->sessionProxyMockedStorage, $methods], $arguments);
    }

    /**
     * DataProvider for testUnsupportedMethods.
     * @see testUnsupportedMethods
     * @return array
     */
    public function unsupportedMethodsProvider()
    {
        return [
            'setName' => ['methods' => 'setName', 'arguments' => ['someName']],
            'invalidate' => ['methods' => 'invalidate', 'arguments' => []],
            'migrate' => ['methods' => 'migrate', 'arguments' => []],
            'getBag' => ['methods' => 'getBag', 'arguments' => ['someBagName']],
            'getMetadataBag' => ['methods' => 'getMetadataBag', 'arguments' => []],
            'registerBag' => [
                'methods' => 'registerBag',
                'arguments' => [$this->createMock(SessionBagInterface::class)],
            ],
        ];
    }

    /**
     * @covers ::set
     */
    public function testSet()
    {
        $this->sessionProxy->set($this->sessionKey, $this->sessionValue);

        $this->assertArrayHasKey($this->sessionKey, $this->sessionStorage);
        $this->assertEquals($this->sessionValue, $this->sessionStorage[$this->sessionKey]);
    }

    /**
     * @covers ::has
     */
    public function testHas()
    {
        $this->sessionStorage[$this->sessionKey] = $this->sessionValue;

        $this->assertTrue($this->sessionProxy->has($this->sessionKey));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {

        $this->sessionStorage[$this->sessionKey] = $this->sessionValue;

        $this->assertEquals($this->sessionValue, $this->sessionProxy->get($this->sessionKey));
    }

    /**
     * @covers ::get
     */
    public function testGetUndefined()
    {
        unset($this->sessionStorage[$this->sessionKey]);

        $this->assertEquals($this->sessionValue, $this->sessionProxy->get($this->sessionKey, $this->sessionValue));
    }

    /**
     * @covers ::all
     */
    public function testAll()
    {
        $this->sessionStorageMocked[$this->sessionKey] = $this->sessionValue;

        $this->assertEquals($this->sessionStorageMocked, $this->sessionProxyMockedStorage->all());
    }


    /**
     * @covers ::remove
     */
    public function testRemove()
    {

        $this->sessionStorage[$this->sessionKey] = $this->sessionValue;

        $this->assertEquals($this->sessionValue, $this->sessionProxy->remove($this->sessionKey));
        $this->assertArrayNotHasKey($this->sessionKey, $this->sessionStorage);
    }

    /**
     * @covers ::remove
     */
    public function testRemoveUndefined()
    {
        unset($this->sessionStorageMocked[$this->sessionKey]);

        $this->assertNull($this->sessionProxyMockedStorage->remove($this->sessionKey));
    }

    /**
     * @covers ::replace
     */
    public function testReplace()
    {
        $keys = array(
            'sessionKey' . rand(1000, 9999),
            'sessionKey' . rand(1000, 9999),
            'sessionKey' . rand(1000, 9999),
            $this->sessionKey,
        );
        $values = array(
            'sessionValue' . rand(1000, 9999),
            'sessionValue' . rand(1000, 9999),
            'sessionValue' . rand(1000, 9999),
            'sessionReplacedValue' . rand(1000, 9999),
        );

        $replacement = array_combine($keys, $values);

        $this->sessionStorage[$this->sessionKey] = $this->sessionValue;

        $this->sessionProxy->replace($replacement);

        foreach ($replacement as $key => $value) {
            $this->assertArrayHasKey($key, $this->sessionStorage);
            $this->assertEquals($value, $this->sessionStorage[$key]);
        }
    }

    /**
     * Testing dummies methods.
     * @dataProvider dummiesProvider
     * @param $methods
     * @covers ::save
     * @covers ::clear
     */
    public function testDummies($methods)
    {
        $this->assertEmpty($this->sessionProxyMockedStorage->$methods());
    }

    /**
     * DataProvider for testUnsupportedMethods.
     * @see testDummies
     * @return array
     */
    public function dummiesProvider()
    {
        return [
            'save' => ['methods' => 'save'],
            'clear' => ['methods' => 'clear'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->sessionStorageMocked = $this->createMock(SessionStorage::class);
        $this->sessionProxyMockedStorage = new SessionProxy($this->sessionStorageMocked);

        $this->sessionStorage = SessionStorage::getInstance();
        $this->sessionProxy = new SessionProxy($this->sessionStorage);

        $this->sessionKey = 'sessionKey' . rand(1000, 9999);
        $this->sessionValue = 'sessionValue' . rand(1000, 9999);
    }
}
