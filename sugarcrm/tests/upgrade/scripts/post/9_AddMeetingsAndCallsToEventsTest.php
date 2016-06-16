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

namespace Sugarcrm\SugarcrmTests\upgrade\scripts\post;

use Sugarcrm\Sugarcrm\Util\Uuid as Uuid;

require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/9_AddMeetingsAndCallsToEvents.php';

/**
 * Class SugarUpgradeAddMeetingsAndCallsToEventsTest
 * @package Sugarcrm\SugarcrmTests\upgrade\scripts\post
 * @covers \SugarUpgradeAddMeetingsAndCallsToEvents
 */
class SugarUpgradeAddMeetingsAndCallsToEventsTest extends \UpgradeTestCase
{
    /**
     * @var \SugarUpgradeAddMeetingsAndCallsToEvents|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calDavUpgrader = null;

    /**
     * @var \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterFactory = null;

    /**
     * @var \Sugarcrm\Sugarcrm\JobQueue\Manager\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jobQueueManager = null;

    /**
     * @var \SugarTestDatabaseMock
     */
    protected $db = null;

    /**
     * @var \Configurator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurator = null;

    /**
     * @var array
     */
    protected $supportedModules = array('Calls');
//    protected $supportedModules = array('Calls', 'Meetings');

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->db = \SugarTestHelper::setUp('mock_db');
        $this->upgrader->db = $this->db;

        $this->jobQueueManager = $this->getMock('Sugarcrm\Sugarcrm\JobQueue\Manager\Manager', array('CalDavRebuild'));
        $this->configurator = $this->getMock('Configurator');
        $this->configurator->config = array('caldav_enable_sync' => false);
        $this->adapterFactory = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory');
        $this->adapterFactory->method('getSupportedModules')->willReturn($this->supportedModules);

        $this->calDavUpgrader = $this->getMock(
            'SugarUpgradeAddMeetingsAndCallsToEvents',
            array('getCalDavAdapterFactory', 'getConfigurator', 'getJQManager'),
            array($this->upgrader)
        );
        $this->calDavUpgrader->method('getCalDavAdapterFactory')->willReturn($this->adapterFactory);
        $this->calDavUpgrader->method('getConfigurator')->willReturn($this->configurator);
        $this->calDavUpgrader->method('getJQManager')->willReturn($this->jobQueueManager);

        \BeanFactory::setBeanClass('Calls', __NAMESPACE__.'\Call1633');
        Call1633::$savedBeans = array();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        \BeanFactory::setBeanClass('Calls', null);

        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Testing is queued re-export call and meeting to job queue.
     *
     * @dataProvider runEnableProvider
     * @param boolean $enable
     * @covers \SugarUpgradeAddMeetingsAndCallsToEvents::run
     */
    public function testSendingToQueue($version, $enable, $queued)
    {
        $this->calDavUpgrader->from_version = $version;
        $this->configurator->config = array('caldav_enable_sync' => $enable);
        $this->adapterFactory->method('getSupportedModules')->willReturn($this->supportedModules);

        if ($queued) {
            $this->jobQueueManager->expects($this->once())->method('CalDavRebuild');
        } else {
            $this->jobQueueManager->expects($this->never())->method('CalDavRebuild');
        }

        $this->calDavUpgrader->run();
    }

    /**
     * Data provider for testSendingToQueue.
     *
     * @see Sugarcrm\SugarcrmTests\upgrade\scripts\post\SugarUpgradeAddMeetingsAndCallsToEventsTest::testSendingToQueue
     * @return array
     */
    public function runEnableProvider()
    {
        return array(
            'enabledForOldVersion' => array('7.8.0.0RC1', true, true),
            'enabledForNewVersion' => array('7.8.0.0RC4', true, false),
            'disabledForOldVersion' => array('7.8.0.0RC1', false, false),
            'disabledForNewVersion' => array('7.8.0.0RC4', false, false),
        );
    }

    /**
     * Testing upgrade mechanizm.
     *
     * @dataProvider runProvider
     * @param array $list
     * @param boolean $invalidResult
     * @param array $fieldDefinitions
     */
    public function testRun($list, $invalidResult, $fieldDefinitions)
    {
        $this->db->addQuerySpy("TRUNCATECalDavSynchronization", "/TRUNCATE caldav_synchronization/");
        $this->db->addQuerySpy("TRUNCATECalDavQueue", "/TRUNCATE caldav_queue/");

        Call1633::$beans = $list;
        $this->db->addQuerySpy("SelectCalls", "/FROM calls/", $list);
        Call1633::$fieldDefinitions = $fieldDefinitions;

        $this->calDavUpgrader->run();

        $this->assertEquals(
            1,
            $this->db->getQuerySpyRunCount('TRUNCATECalDavQueue'),
            'not truncated table caldav_queue'
        );
        $this->assertEquals(
            1,
            $this->db->getQuerySpyRunCount('TRUNCATECalDavSynchronization'),
            'not truncated table caldav_synchronization'
        );

        foreach (Call1633::$savedBeans as $savedCall) {
            $this->assertFalse($savedCall['arguments'][0]);
            $this->assertEquals(array('disableCalDavHook' => true), $savedCall['arguments'][1]);

            if ($invalidResult) {
                if ($savedCall['bean']['repeat_type'] == 'Weekly') {
                    $this->assertNotEmpty($savedCall['bean']['repeat_dow']);
                } else {
                    $this->assertEmpty($savedCall['bean']['repeat_dow']);
                }

                if (empty($savedCall['bean']['repeat_parent_id'])) {
                    $this->assertEquals($savedCall['bean']['id'], $savedCall['bean']['repeat_root_id']);
                } else {
                    $this->assertEquals($savedCall['bean']['repeat_parent_id'], $savedCall['bean']['repeat_root_id']);
                }
            } else {
                $this->assertNotEmpty($savedCall['bean']['repeat_dow']);
            }
        }
    }

    /**
     * Data provider.
     *
     * @see Sugarcrm\SugarcrmTests\upgrade\scripts\post\SugarUpgradeAddMeetingsAndCallsToEventsTest::testRun
     * @return array
     */
    public function runProvider()
    {
        $list = array();
        for ($i = 0; $i < rand(5, 10); $i++) {
            $id = Uuid::uuid1();
            $callArr = array(
                'id' => $id,
                'name' => "name{$i}_" . rand(1000, 9999),
                'repeat_dow' => 'SomeValue' . rand(1000, 9999),
            );
            if (0 == $i % 2) {
                $callArr['repeat_parent_id'] = Uuid::uuid1();
                $callArr['repeat_type'] = 'Weekly';
            }
            $list[$id] = $callArr;
        }
        return array(
            'invalidResult' => array(
                'list' => $list,
                'invalidResult' => true,
                'fieldDefinitions' => array(
                    'id' => array(
                        'name' => 'id',
                        'type' => 'id',
                    ),
                    'name' => array(
                        'name' => 'name',
                        'type' => 'text',
                    ),
                    'repeat_root_id' => array(
                        'name' => 'repeat_root_id',
                        'type' => 'text',
                    ),
                    'repeat_parent_id' => array(
                        'name' => 'repeat_parent_id',
                        'type' => 'text',
                        'source' => 'db',
                    ),
                    'repeat_dow' => array(
                        'name' => 'repeat_dow',
                        'type' => 'text',
                    ),
                    'repeat_type' => array(
                        'name' => 'repeat_type',
                        'type' => 'text',
                        'source' => 'db',
                    ),
                ),
            ),
            'validResult' => array(
                'list' => $list,
                'invalidResult' => false,
                'fieldDefinitions' => array(
                    'id' => array(
                        'name' => 'id',
                        'type' => 'id',
                    ),
                    'name' => array(
                        'name' => 'name',
                        'type' => 'text',
                    ),
                    'repeat_root_id' => array(
                        'name' => 'repeat_root_id',
                        'type' => 'text',
                        'source' => 'non-db',
                    ),
                    'repeat_parent_id' => array(
                        'name' => 'repeat_parent_id',
                        'type' => 'text',
                        'source' => 'non-db',
                    ),
                    'repeat_dow' => array(
                        'name' => 'repeat_dow',
                        'type' => 'text',
                        'source' => 'non-db',
                    ),
                    'repeat_type' => array(
                        'name' => 'repeat_type',
                        'type' => 'text',
                        'source' => 'non-db',
                    ),
                ),
            ),
        );
    }
}

/**
 * Mock for SugarBean Call.
 *
 * Class Call1633
 * @package Sugarcrm\SugarcrmTests\upgrade\scripts\post
 */
class Call1633 extends \Call
{
    public $module_name = 'Calls';

    public static $fieldDefinitions = array();

    public static $savedBeans = array();

    public static $beans = array();

    public function __construct()
    {
        $this->db = \DBManagerFactory::getInstance();
        $this->field_defs = static::$fieldDefinitions;
    }

    /**
     * @inheritDoc
     */
    public function addVisibilityFrom(&$query, $options = null)
    {
        return parent::addVisibilityFrom($query, $options); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritDoc
     */
    public function addVisibilityQuery($query, $options = null)
    {

    }

    /**
     * @inheritDoc
     */
    public function save($check_notify = false, $options = array())
    {
        self::$savedBeans[] = array('bean' => $this->toArray(), 'arguments' => func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        $this->fromArray(self::$beans[$id]);
    }
}
