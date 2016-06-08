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

namespace Sugarcrm\SugarcrmTests\Notification\JobQueue;

use \Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler as JobQueueBaseHandler;

/**
 * Class BaseHandlerTest
 *
 * @package Sugarcrm\SugarcrmTests\Notification\JobQueue
 * @covers Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler
 */
class BaseHandlerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $notificationClassName;

    /** @var string */
    protected $notificationFilePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('files');
        $this->notificationClassName = 'NotificationSomeClass' . rand(1000, 1999);
        $this->notificationFilePath = sugar_cached($this->notificationClassName . '.php');
        $classCode = '<?php
            class ' . $this->notificationClassName . ' implements Sugarcrm\Sugarcrm\Notification\EventInterface
            {
                public function __toString()
                {
                    return "name";
                }
                public function serialize()
                {        
                    return serialize(array(1));
                }
                
                public static function unserialize($value)
                {        
                    return new static();
                }
            }
        ';
        \SugarTestHelper::saveFile($this->notificationFilePath);
        sugar_file_put_contents($this->notificationFilePath, $classCode);
        require_once $this->notificationFilePath;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Data provider for testInitializeCallScalar.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\JobQueue\BaseHandlerTest::testInitializeCallScalar
     * @return array
     */
    public static function initializeCallScalarProvider()
    {
        $rand1 = rand(100, 199)/100;
        $rand2 = rand(200, 299)/100;
        return array(
            'serializedScalar' => array(
                'constructorArguments' => array(
                    'userId' => rand(2000, 2999),
                    'arg' => array('', null, serialize($rand1)),
                    'event' => array('', null, serialize('create')),
                ),
                'expectedArguments' => array(
                    $rand1,
                    'create',
                ),
            ),
            'fourArguments' => array(
                'constructorArguments' => array(
                    'userId' => rand(1000, 1999),
                    'arg' => array('', null, serialize($rand2)),
                    'event' => array('', null, serialize('update')),
                    'date' => array('', null, serialize('2015-12-12')),
                ),
                'expectedArguments' => array(
                    $rand2,
                    'update',
                    '2015-12-12',
                ),
            ),
        );
    }

    /**
     * Should call initialize method with properly unpacked scalars.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler::__construct
     * @dataProvider initializeCallScalarProvider
     * @param array $constructorArguments
     * @param array $expectedArguments
     */
    public function testInitializeCallScalar($constructorArguments, $expectedArguments)
    {
        $handler =  new JobQueueHandlerCRYS1289;
        call_user_func_array(array($handler, '__construct'), $constructorArguments);
        $this->assertEquals($expectedArguments, $handler->arguments);
    }

    /**
     * Data provider for testInitializeCallObject.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\JobQueue\BaseHandlerTest::testInitializeCallObject
     * @return array
     */
    public static function initializeCallObjectProvider()
    {
        return array(
            'serializedObjectWithUserId' => array(
                'userId' => rand(1000, 1999),
                'serializedEvent' => array('', null, serialize('update')),
                'expectedEvent' => 'update',
            ),
            'serializedObjectWithoutUser' => array(
                'userId' => null,
                'serializedEvent' => array('', null, serialize('update')),
                'expectedEvent' => 'update',
            ),
        );
    }

    /**
     * Should call initialize method with properly unpacked objects.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler::__construct
     * @dataProvider initializeCallObjectProvider
     * @param null|int $userId
     * @param string $serializedEvent serialized event data.
     * @param string $expectedEvent
     */
    public function testInitializeCallObject($userId, $serializedEvent, $expectedEvent)
    {
        $obj = new $this->notificationClassName();
        $objSerialized = array($this->notificationFilePath, $this->notificationClassName, $obj->serialize());

        $handler = new JobQueueHandlerCRYS1289($userId, $objSerialized, $serializedEvent);
        $this->assertEquals($obj, $handler->arguments[0]);
        $this->assertEquals($expectedEvent, $handler->arguments[1]);
    }

    /**
     * The method should throws an exception if initialize method is not implemented.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler::__construct
     * @expectedException \Exception
     * @expectedExceptionMessage Initialize method is not implemented
     */
    public function testScalarDataInitializeNotPresent()
    {
        /** @var JobQueueBaseHandler|\PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockForAbstractClass('Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler');
        $reflectedClass = new \ReflectionClass($handler);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($handler);
    }
}

/**
 * Class JobQueueHandlerCRYS1289
 *
 * @package Sugarcrm\SugarcrmTests\Notification\JobQueue
 */
class JobQueueHandlerCRYS1289 extends JobQueueBaseHandler
{
    /** @var null|array */
    public $arguments = null;

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->arguments = func_get_args();
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {

    }
}
