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
     * @var SessionStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionStorage;

    /**
     * @var SessionProxy
     */
    protected $sessionProxy;

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
        $this->sessionStorage
            ->expects($this->once())
            ->method('start');
        $this->sessionStorage
            ->expects($this->once())
            ->method('sessionHasId')
            ->willReturn($expectedIsStarted);

        $this->assertEquals($expectedIsStarted, $this->sessionProxy->start());
    }

    /**
     * @covers ::getId
     */
    public function testGetId()
    {
        $expectedSessionId = rand(1000, 9999);
        $this->sessionStorage
            ->expects($this->once())
            ->method('getId')
            ->willReturn($expectedSessionId);
        $this->assertEquals($expectedSessionId, $this->sessionProxy->getId());
    }

    /**
     * @covers ::setId
     */
    public function testSetId()
    {
        $expectedSessionId = rand(1000, 9999);
        $this->sessionStorage
            ->expects($this->once())
            ->method('setId')
            ->with($this->equalTo($expectedSessionId));

        $this->sessionProxy->setId($expectedSessionId);
    }

    /**
     * @covers ::getName
     */
    public function testGetName()
    {
        $this->assertEquals(session_name(), $this->sessionProxy->getName());
    }

    /**
     * @covers ::isStarted
     */
    public function testIsStarted()
    {

        $expectedIsStarted = rand(1000, 9999);
        $this->sessionStorage
            ->expects($this->once())
            ->method('sessionHasId')
            ->willReturn($expectedIsStarted);

        $this->assertEquals($expectedIsStarted, $this->sessionProxy->isStarted());
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
        call_user_func_array([$this->sessionProxy, $methods], $arguments);
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
        $this->sessionStorage->expects($this->once())
            ->method('offsetSet')
            ->with($this->sessionKey, $this->sessionValue);

        $this->sessionProxy->set($this->sessionKey, $this->sessionValue);
    }

    /**
     * @covers ::has
     */
    public function testHas()
    {
        $this->sessionStorage->expects($this->once())
            ->method('offsetExists')
            ->with($this->sessionKey)
            ->willReturn(true);

        $this->assertTrue($this->sessionProxy->has($this->sessionKey));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $this->sessionStorage->method('offsetExists')
            ->with($this->sessionKey)
            ->willReturn(true);

        $this->sessionStorage->expects($this->once())
            ->method('offsetGet')
            ->with($this->sessionKey)
            ->willReturn($this->sessionValue);

        $this->assertEquals($this->sessionValue, $this->sessionProxy->get($this->sessionKey));
    }

    /**
     * @covers ::get
     */
    public function testGetUndefined()
    {
        $this->sessionStorage->method('offsetExists')
            ->with($this->sessionKey)
            ->willReturn(false);

        $this->sessionStorage->expects($this->never())
            ->method('offsetGet');

        $this->assertEquals(
            $this->sessionValue,
            $this->sessionProxy->get($this->sessionKey, $this->sessionValue)
        );
    }

    /**
     * @covers ::all
     */
    public function testAll()
    {
        $this->sessionStorage[$this->sessionKey] = $this->sessionValue;

        $this->assertEquals($this->sessionStorage, $this->sessionProxy->all());
    }

    /**
     * @covers ::remove
     */
    public function testRemove()
    {
        $this->sessionStorage->method('offsetExists')
            ->with($this->sessionKey)
            ->willReturn(true);
        $this->sessionStorage->method('offsetGet')
            ->willReturn($this->sessionValue);
        $this->sessionStorage->expects($this->once())
            ->method('offsetUnset')
            ->with($this->sessionKey);

        $this->assertEquals($this->sessionValue, $this->sessionProxy->remove($this->sessionKey));
    }

    /**
     * @covers ::remove
     */
    public function testRemoveUndefined()
    {
        unset($this->sessionStorage[$this->sessionKey]);

        $this->assertNull($this->sessionProxy->remove($this->sessionKey));
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

        $this->sessionStorage->expects($this->exactly(4))
            ->method('offsetSet')
            ->withConsecutive(
                [$keys[0], $values[0]],
                [$keys[1], $values[1]],
                [$keys[2], $values[2]]
            );
        $replacement = array_combine($keys, $values);

        $this->sessionProxy->replace($replacement);
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
     * Testing dummies methods.
     * @dataProvider dummiesProvider
     * @param $methods
     * @covers ::save
     * @covers ::clear
     */
    public function testDummies($methods)
    {
        $this->assertEmpty($this->sessionProxy->$methods());
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->sessionStorage = $this->createMock(SessionStorage::class);
        $this->sessionStorage->method('sessionHasId')->willReturn(true);
        $this->sessionProxy = new SessionProxy($this->sessionStorage);

        $this->sessionKey = 'sessionKey' . rand(1000, 9999);
        $this->sessionValue = 'sessionValue' . rand(1000, 9999);
    }
}
