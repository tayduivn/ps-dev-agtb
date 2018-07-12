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

/**
 * @coversDefaultClass \Worklog
 */
class WorklogTest extends TestCase
{
    /**
     * @var SugarBean The Worklog SugarBean
     */
    private $bean;

    /**
     * @var array Stores the id of the created worklog
     */
    private static $created_worklogs;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        $this->bean = BeanFactory::newBean('Worklog');
        self::$created_worklogs = array();
    }

    public function tearDown()
    {
        $this->removeWorklog();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
    }

    /**
     * Removes the worklog in $created_worklogs
     */
    public function removeWorklog()
    {
        $db = DBManagerFactory::getInstance();
        $ids = "'" . implode("','", self::$created_worklogs) . "'";

        $db->query("DELETE FROM worklog WHERE id IN ($ids)");
        $db->query("DELETE FROM worklog_index WHERE worklog_id IN ($ids)");
    }

    /**
     * Checks whether the fields are there in the bean->field_defs
     * @dataProvider CheckWorklogBeanProvider
     * @param SugarBean $bean The $bean to test against
     * @param string $property The property we would like to check
     */
    public function testCheckWorklogBean(SugarBean $bean, string $property)
    {
        $this->assertArrayHasKey($property, $bean->field_defs);
    }

    public function CheckWorklogBeanProvider()
    {
        $bean = BeanFactory::getBean("Worklog");

        return array(
            array($bean, 'id'),
            array($bean, 'date_entered'),
            array($bean, 'entry'),
        );
    }

    /**
     * Check if the database table setup for worklog module is setup
     * correctly
     */
    public function testCheckWorklogModuleDBTableSetup()
    {
        $this->assertTrue(DBManagerFactory::getInstance()->tableExists('worklog')); // verify that the table exists
    }

    /**
     * Checks whether the $required field is in $reality
     * @param string $required The required field name
     * @param array $reality The actual list of field names in worklog DB
     * @dataProvider CheckWorklogDBFieldSetupProvider
     */
    public function testCheckWorklogDBFieldSetup(string $required, array $reality)
    {
        $this->assertArrayHasKey($required, $reality);
    }

    public function CheckWorklogDBFieldSetupProvider()
    {
        $db = DBManagerFactory::getInstance();
        $columns = $db->get_columns('worklog');

        return array(
            array('id', $columns),
            array('date_entered', $columns),
            array('entry', $columns),
        );
    }

    /**
     * Tests whether setModule works correctly
     * @param string $module The module to set in worklog
     * @param bool $setted Whether $module should be set in the bean or not
     * @dataProvider SetModuleProvider
     */
    public function testSetModule(string $module, bool $setted)
    {
        $result = $this->bean->setModule($module);

        $this->assertEquals($result, $setted);

        if ($setted) {
            $this->assertEquals($this->bean->module, $module);
        }
    }

    public function SetModuleProvider()
    {
        return array(
            array("Accounts", true,),
            array("Bugs", true,),
            array("I would be suprised If I'm a module", false,),
        );
    }

    /**
     * Cases when only the bean has the information about parent module
     */
    public function BeanHasModuleProvider()
    {
        return array(
            array(
                "Meetings",
                array(
                    "worklog" => "The is a well grown bean",
                ),
            ),
        );
    }

    /**
     * Cases when both bean and $params has info anout parent module
     */
    public function ParamsHasModuleProvider()
    {
        return array(
            array(
                "Bugs",
                array(
                    "module" => "Bugs",
                    "worklog" => "This bean, has a weakness",
                ),
            ),
        );
    }

    public function testgetParentRecord()
    {
        $record = SugarTestMeetingUtilities::createMeeting();
        $worklog_field = new SugarFieldWorklog('worklog');

        $worklog_field->apiSave($record, array('worklog' => 'watashigakita!!'), 'worklog', array());

        $record->load_relationship('worklog_link');
        $worklog_beans = $record->worklog_link->getBeans();

        self::$created_worklogs[] = array_keys($worklog_beans)[0];

        $bean = BeanFactory::retrieveBean('Worklog', array_keys($worklog_beans)[0]);

        $actual = $bean->getParentRecord();

        $this->assertEquals($record->id, $actual['record']);
        $this->assertEquals($record->getModuleName(), $actual['module']);
    }
}
