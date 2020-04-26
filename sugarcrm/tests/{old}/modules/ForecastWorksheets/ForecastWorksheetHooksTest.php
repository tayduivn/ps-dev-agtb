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

use PHPUnit\Framework\TestCase;

class ForecastWorksheetHooksTest extends TestCase
{
    /**
     * @var ForecastWorksheet
     */
    protected $worksheet;

    protected function setUp() : void
    {
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');

        $this->worksheet = $this->createPartialMock('ForecastWorksheet', array('save', 'load_relationship'));
    }

    protected function tearDown() : void
    {
        $this->worksheet = null;
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ForecastWorksheetHooks::managerNotifyCommitStage
     */
    public function testManagerNotifyCommitStageNewBeanReturnsFalse()
    {
        $this->markTestSkipped('Skipped for now as Notifications are not working currently');
        $this->worksheet->draft = 0;
        $this->worksheet->fetched_row = array();

        /* @var $hook ForecastWorksheetHooks */
        $hook = new MockForecastWorksheetHooks();
        $hook::$_isForecastSetup = false;
        $ret = $hook::managerNotifyCommitStage($this->worksheet, 'before_save', array());
        $this->assertFalse($ret);
    }

    /**
     * @covers ForecastWorksheetHooks::managerNotifyCommitStage
     */
    public function testManagerNotifyCommitStageNotMatchingForecastByTypeReturnsFalse()
    {
        $this->markTestSkipped('Skipped for now as Notifications are not working currently');
        $this->worksheet->draft = 0;
        $this->worksheet->fetched_row = array(
            'commit_stage' => 'include'
        );
        $this->worksheet->parent_type = 'Test';


        $hook = new MockForecastWorksheetHooks();
        $hook::$_isForecastSetup = false;
        /* @var $hook ForecastWorksheetHooks */
        $hook::$settings = array('forecast_by' => 'Test1');
        $ret = $hook::managerNotifyCommitStage($this->worksheet, 'before_save', array());
        $this->assertFalse($ret);
    }

    /**
     * @covers ForecastWorksheetHooks::managerNotifyCommitStage
     */
    public function testManagerNotifyCommitStageCommitStageEqualToIncludeReturnsFalse()
    {
        $this->markTestSkipped('Skipped for now as Notifications are not working currently');
        $this->worksheet->draft = 0;
        $this->worksheet->fetched_row = array(
            'commit_stage' => 'include'
        );
        $this->worksheet->commit_stage = 'include';
        $this->worksheet->parent_type = 'Test';


        $hook = new MockForecastWorksheetHooks();
        $hook::$_isForecastSetup = false;
        /* @var $hook ForecastWorksheetHooks */
        $hook::$settings = array('forecast_by' => 'Test');
        $ret = $hook::managerNotifyCommitStage($this->worksheet, 'before_save', array());
        $this->assertFalse($ret);
    }

    /**
     * @dataProvider dataProviderCheckRelatedName
     */
    public function testCheckRelatedName($field, $value, $isEmpty)
    {
        $hook = new MockForecastWorksheetHooks();

        $worksheet = $this->createPartialMock('ForecastWorksheet', array('save'));

        $fn = $field . '_name';
        $fi = $field . '_id';

        $worksheet->$fn = 'Test Value';
        $worksheet->$fi = $value;

        $hook->checkRelatedName($worksheet, 'before_save', array());

        $this->assertEquals($isEmpty, empty($worksheet->$fn));
    }

    public function dataProviderCheckRelatedName()
    {
        return array(
            array('account', '', true),
            array('account', 'some_value', false),
            array('opportunity', '', true),
            array('opportunity', 'some_value', false),
        );
    }

    public function testAfterRelationshipDelete()
    {
        $hook = new MockForecastWorksheetHooks();

        /**
         * @var $dbMock SugarTestDatabaseMock
         */
        $dbMock = SugarTestHelper::setUp('mock_db');

        $worksheet = $this->createPartialMock('ForecastWorksheet', array('save', 'load_relationship', 'getFieldDefinition'));
        $worksheet->db = $dbMock;

        $worksheet->expects($this->once())
            ->method('load_relationship')
            ->will($this->returnValue(true));
        $worksheet->expects($this->once())
            ->method('getFieldDefinition')
            ->will($this->returnValue(true));

        $relMock = $this->getMockBuilder('One2MBeanRelationship', array('__get'))
            ->disableOriginalConstructor()
            ->getMock();
        $relMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnCallback(
                    function () {
                        return array('rhs_key' => 'test_id');
                    }
                )
            );

        $linkMock = $this->getMockBuilder('Link2')
            ->setMethods(array('getRelationshipObject'))
            ->disableOriginalConstructor()
            ->getMock();

        $linkMock->expects($this->any())
            ->method('getRelationshipObject')
            ->will($this->returnValue($relMock));

        $worksheet->test = $linkMock;

        $dbMock->addQuerySpy(
            'name_query',
            '/test_name/'
        );
        /* @var $hook ForecastWorksheetHooks */
        /* @var $worksheet ForecastWorksheet */
        $hook->afterRelationshipDelete($worksheet, 'after_relationship_delete', array('link' => 'test'));

        $this->assertEquals(1, $dbMock->getQuerySpyRunCount('name_query'));
    }
}

class MockForecastWorksheetHooks extends ForecastWorksheetHooks
{
    /**
     * Allow us to easily change it depending on the test
     * @var bool
     */
    public static $_isForecastSetup = true;

    /**
     * Allow us to set a custom notification bean
     *
     * @var null|SugarBean
     */
    public static $_notificationBean = null;

    public static $_languageStringsMock = array();

    public static function isForecastSetup()
    {
        return static::$_isForecastSetup;
    }

    public static function getNotificationBean()
    {
        return static::$_notificationBean;
    }

    public static function getLanguageStrings($key)
    {
        return static::$_languageStringsMock;
    }
}
