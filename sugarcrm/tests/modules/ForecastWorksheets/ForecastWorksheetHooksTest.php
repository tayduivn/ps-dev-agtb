<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once('modules/ForecastWorksheets/ForecastWorksheetHooks.php');
class ForecastWorksheetHooksTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var ForecastWorksheet
     */
    protected $worksheet;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');

        $this->worksheet = $this->getMock('ForecastWorksheet', array('save', 'load_relationship'));
    }

    public function tearDown()
    {
        $this->worksheet = null;
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @covers ForecastWorksheetHooks::managerNotifyCommitStage
     */
    public function testManagerNotifyCommitStageDraftBeanReturnFalse()
    {
        $this->worksheet->draft = 1;

        /* @var $hook ForecastWorksheetHooks */
        $hook = $this->getMock('ForecastWorksheetHooks', array('isForecastSetup', 'getNotificationBean'));
        $ret = $hook::managerNotifyCommitStage($this->worksheet, 'before_save', array());
        $this->assertFalse($ret);
    }

    /**
     * @covers ForecastWorksheetHooks::managerNotifyCommitStage
     */
    public function testManagerNotifyCommitStageNewBeanReturnsFalse()
    {
        $this->worksheet->draft = 0;
        $this->worksheet->fetched_row = array();

        /* @var $hook ForecastWorksheetHooks */
        $hook = $this->getMock('ForecastWorksheetHooks', array('isForecastSetup', 'getNotificationBean'));
        $ret = $hook::managerNotifyCommitStage($this->worksheet, 'before_save', array());
        $this->assertFalse($ret);
    }

    /**
     * @covers ForecastWorksheetHooks::managerNotifyCommitStage
     */
    public function testManagerNotifyCommitStageForecastNotSetupReturnsFalse()
    {
        $this->worksheet->draft = 0;
        $this->worksheet->fetched_row = array(
            'commit_stage' => 'include'
        );


        $hook = $this->getMock('ForecastWorksheetHooks', array('isForecastSetup', 'getNotificationBean'));
        $hook::staticExpects($this->once())
            ->method('isForecastSetup')
            ->will($this->returnValue(false));
        /* @var $hook ForecastWorksheetHooks */
        $ret = $hook::managerNotifyCommitStage($this->worksheet, 'before_save', array());
        $this->assertFalse($ret);
    }

    /**
     * @covers ForecastWorksheetHooks::managerNotifyCommitStage
     */
    public function testManagerNotifyCommitStageNotMatchingForecastByTypeReturnsFalse()
    {
        $this->worksheet->draft = 0;
        $this->worksheet->fetched_row = array(
            'commit_stage' => 'include'
        );
        $this->worksheet->parent_type = 'Test';


        $hook = $this->getMock('ForecastWorksheetHooks', array('isForecastSetup', 'getNotificationBean'));
        $hook::staticExpects($this->once())
            ->method('isForecastSetup')
            ->will($this->returnValue(true));
        /* @var $hook ForecastWorksheetHooks */
        $hook::$settings = array('forecast_by' => 'Test1');
        $ret = $hook::managerNotifyCommitStage($this->worksheet, 'before_save', array());
        $this->assertFalse($ret);
    }

    /**
     * @covers ForecastWorksheetHooks::managerNotifyCommitStage
     */
    public function testManagerNotifyCommitStageFetchedCommitStageNotEqualToIncludeReturnsFalse()
    {
        $this->worksheet->draft = 0;
        $this->worksheet->fetched_row = array(
            'commit_stage' => 'exclude'
        );
        $this->worksheet->parent_type = 'Test';


        $hook = $this->getMock('ForecastWorksheetHooks', array('isForecastSetup', 'getNotificationBean'));
        $hook::staticExpects($this->once())
            ->method('isForecastSetup')
            ->will($this->returnValue(true));
        /* @var $hook ForecastWorksheetHooks */
        $hook::$settings = array('forecast_by' => 'Test');
        $ret = $hook::managerNotifyCommitStage($this->worksheet, 'before_save', array());
        $this->assertFalse($ret);
    }

    /**
     * @covers ForecastWorksheetHooks::managerNotifyCommitStage
     */
    public function testManagerNotifyCommitStageCommitStageEqualToIncludeReturnsFalse()
    {
        $this->worksheet->draft = 0;
        $this->worksheet->fetched_row = array(
            'commit_stage' => 'include'
        );
        $this->worksheet->commit_stage = 'include';
        $this->worksheet->parent_type = 'Test';


        $hook = $this->getMock('ForecastWorksheetHooks', array('isForecastSetup', 'getNotificationBean'));
        $hook::staticExpects($this->once())
            ->method('isForecastSetup')
            ->will($this->returnValue(true));
        /* @var $hook ForecastWorksheetHooks */
        $hook::$settings = array('forecast_by' => 'Test');
        $ret = $hook::managerNotifyCommitStage($this->worksheet, 'before_save', array());
        $this->assertFalse($ret);
    }

    /**
     * @covers ForecastWorksheetHooks::managerNotifyCommitStage
     */
    public function testManagerNotifyCommitStageWithUserWithNoReportsToReturnsFalse()
    {
        $user = $this->getMock('User', array('save'));
        $user->id = 'test';

        $link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $link2->expects($this->once())
            ->method('getBeans')
            ->will($this->returnValue(array($user)));

        $worksheet = $this->worksheet;
        $worksheet->draft = 0;
        $worksheet->fetched_row = array(
            'commit_stage' => 'include'
        );
        $worksheet->commit_stage = 'exclude';
        $worksheet->parent_type = 'Test';


        $worksheet->expects($this->once())
            ->method('load_relationship')
            ->will(
                $this->returnCallback(
                    function ($o) use (&$worksheet, &$link2) {
                        $worksheet->$o = $link2;
                        return $o;
                    }
                )
            );

        $hook = $this->getMock('ForecastWorksheetHooks', array('isForecastSetup', 'getNotificationBean'));
        $hook::staticExpects($this->once())
            ->method('isForecastSetup')
            ->will($this->returnValue(true));
        /* @var $hook ForecastWorksheetHooks */
        $hook::$settings = array('forecast_by' => 'Test');
        $ret = $hook::managerNotifyCommitStage($this->worksheet, 'before_save', array());
        $this->assertFalse($ret);
    }

    /**
     * @covers ForecastWorksheetHooks::managerNotifyCommitStage
     */
    public function testManagerNotifyCommitStageWithUserWithReportsToCreatesNotification()
    {
        $user = $this->getMock('User', array('save'));
        $user->id = 'test';
        $user->reports_to_id = 'test1';

        $link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $link2->expects($this->once())
            ->method('getBeans')
            ->will($this->returnValue(array($user)));

        $worksheet = $this->worksheet;
        $worksheet->draft = 0;
        $worksheet->fetched_row = array(
            'commit_stage' => 'include'
        );
        $worksheet->commit_stage = 'exclude';
        $worksheet->parent_type = 'Test';


        $worksheet->expects($this->once())
            ->method('load_relationship')
            ->will(
                $this->returnCallback(
                    function ($o) use (&$worksheet, &$link2) {
                        $worksheet->$o = $link2;
                        return $o;
                    }
                )
            );

        $hook = $this->getMock(
            'ForecastWorksheetHooks',
            array('isForecastSetup', 'getNotificationBean', 'getLanguageStrings')
        );
        $hook::staticExpects($this->once())
            ->method('isForecastSetup')
            ->will($this->returnValue(true));

        $notification = $this->getMock('Notifications', array('save'));
        $notification->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $hook::staticExpects($this->once())
            ->method('getNotificationBean')
            ->will($this->returnValue($notification));

        $hook::staticExpects($this->any())
            ->method('getLanguageStrings')
            ->will(
                $this->onConsecutiveCalls(
                    array('LBL_MANAGER_NOTIFY' => 'Message One'),
                    array('LBL_MODULE_NAME_SINGULAR' => 'Message Two')
                )
            );
        /* @var $hook ForecastWorksheetHooks */
        $hook::$settings = array('forecast_by' => 'Test');
        $ret = $hook::managerNotifyCommitStage($this->worksheet, 'before_save', array());

        $this->assertTrue($ret);
        /* @var $notification Notifications */
        $this->assertEquals('test1', $notification->assigned_user_id);
        $this->assertEquals('Message One', $notification->name);
    }
}
