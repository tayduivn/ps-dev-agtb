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

use Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler as JobQueueHandler;
use Sugarcrm\Sugarcrm\Notification\JobQueue\Manager as JobQueueManager;

/**
 * Class ManagerTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\JobQueue\Manager
 */
class ManagerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var JobQueueManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerMock = null;

    /** @var string */
    protected $className = '';

    /** @var string */
    protected $filePath = '';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('files');
        $this->className = 'Notification' . rand(1000, 1999);
        $this->filePath = sugar_cached($this->className . '.php');
        $classCode = "<?php
            class {$this->className}
            {

            }
        ";
        \SugarTestHelper::saveFile($this->filePath);
        sugar_file_put_contents($this->filePath, $classCode);
        require_once $this->filePath;
        $this->managerMock = $this->getMock('Sugarcrm\Sugarcrm\Notification\JobQueue\Manager', array('addJob'));
        $this->managerMock->registerHandler(
            'managerBaseHandlerCRYS1290',
            'Sugarcrm\SugarcrmTests\Notification\JobQueue\JobQueueHandlerCRYS1290'
        );
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
     * Data provider for testCallScalar.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\JobQueue\ManagerTest::testCallScalar
     * @return array
     */
    public static function callScalarProvider()
    {
        $userId = create_guid();
        return array(
            'firstArgumentNull' => array(
                'arguments' => array(null, true, 5),
                'expectedArguments' =>  array(null, array('', serialize(true)), array('', serialize(5))),
            ),
            'firstArgumentExists' => array(
                'arguments' => array($userId, true, 5),
                'expectedArguments' =>  array($userId, array('', serialize(true)), array('', serialize(5))),
            ),
        );
    }

    /**
     * Should check if handler relates to notifications and wraps scalar values if it is true.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\JobQueue\Manager::__call
     * @dataProvider callScalarProvider
     * @param array $arguments
     * @param array $expectedArguments
     */
    public function testCallScalar($arguments, $expectedArguments)
    {
        call_user_func_array(array($this->managerMock, 'managerBaseHandlerCRYS1290'), $arguments);
        $this->assertEquals($expectedArguments, JobQueueHandlerCRYS1290::$arguments);
    }

    /**
     * Data provider for testCallObject.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\JobQueue\ManagerTest::testCallObject
     * @return array
     */
    public static function callObjectProvider()
    {
        $userId = create_guid();
        return array(
            'firstArgumentNull' => array(
                'arguments' => array(null, true),
                'expectedArguments' => array(
                    null,
                    array('', serialize(true)),
                ),
            ),
            'firstArgumentExists' => array(
                'arguments' => array($userId, true),
                'expectedArguments' => array(
                    $userId,
                    array('', serialize(true)),
                ),
            ),
        );
    }

    /**
     * Should check if handler relates to notifications and wraps objects values if it is true.
     *
     * @dataProvider callObjectProvider
     * @covers Sugarcrm\Sugarcrm\Notification\JobQueue\Manager::__call
     * @param array $arguments
     * @param array $expectedArguments
     */
    public function testCallObject($arguments, $expectedArguments)
    {
        $obj = new $this->className();
        $arguments[] = $obj;
        $expectedArguments[] = array(realpath($this->filePath), serialize($obj));
        call_user_func_array(array($this->managerMock, 'managerBaseHandlerCRYS1290'), $arguments);
        $this->assertEquals($expectedArguments, JobQueueHandlerCRYS1290::$arguments);
    }
}

/**
 * Class JobQueueHandlerCRYS1290 uses for testing correct values wrapping.
 * Uses for testing Sugarcrm\Sugarcrm\Notification\JobQueue\Manager::__call magic method.
 *
 * @package Sugarcrm\SugarcrmTests\Notification\JobQueue
 */
class JobQueueHandlerCRYS1290 extends JobQueueHandler
{
    /** @var array */
    public static $arguments = array();

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        static::$arguments = func_get_args();
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {

    }
}
