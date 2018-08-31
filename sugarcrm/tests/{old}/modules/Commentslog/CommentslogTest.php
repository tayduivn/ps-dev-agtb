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
 * @coversDefaultClass \commentslog
 */
class CommentslogTest extends TestCase
{
    /**
     * @var SugarBean The Commentslog SugarBean
     */
    private $bean;

    /**
     * @var array Stores the id of the created commentslog
     */
    private static $created_commentslogs;

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
        $this->bean = BeanFactory::newBean('Commentslog');
        self::$created_commentslogs = array();
    }

    public function tearDown()
    {
        $this->removeCommentslog();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
    }

    /**
     * Removes the commentslog in $created_commentslogs
     */
    public function removeCommentslog()
    {
        $db = DBManagerFactory::getInstance();
        $ids = "'" . implode("','", self::$created_commentslogs) . "'";

        $db->query("DELETE FROM commentslog WHERE id IN ($ids)");
        $db->query("DELETE FROM commentslog_rel WHERE commentslog_id IN ($ids)");
    }

    /**
     * Checks whether the fields are there in the bean->field_defs
     * @dataProvider CheckCommentslogBeanProvider
     * @param SugarBean $bean The $bean to test against
     * @param string $property The property we would like to check
     */
    public function testCheckCommentslogBean(SugarBean $bean, string $property)
    {
        $this->assertArrayHasKey($property, $bean->field_defs);
    }

    public function CheckCommentslogBeanProvider()
    {
        $bean = BeanFactory::getBean("Commentslog");

        return array(
            array($bean, 'id'),
            array($bean, 'date_entered'),
            array($bean, 'entry'),
        );
    }

    /**
     * Check if the database table setup for commentslog module is setup
     * correctly
     */
    public function testCheckCommentslogModuleDBTableSetup()
    {
        $this->assertTrue(DBManagerFactory::getInstance()->tableExists('commentslog')); // verify that the table exists
    }

    /**
     * Checks whether the $required field is in $reality
     * @param string $required The required field name
     * @param array $reality The actual list of field names in commentslog DB
     * @dataProvider CheckCommentslogDBFieldSetupProvider
     */
    public function testCheckCommentslogDBFieldSetup(string $required, array $reality)
    {
        $this->assertArrayHasKey($required, $reality);
    }

    public function CheckCommentslogDBFieldSetupProvider()
    {
        $db = DBManagerFactory::getInstance();
        $columns = $db->get_columns('commentslog');

        return array(
            array('id', $columns),
            array('date_entered', $columns),
            array('entry', $columns),
        );
    }

    /**
     * Tests whether setModule works correctly
     * @param string $module The module to set in commentslog
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
                    "commentslog" => "The is a well grown bean",
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
                    "commentslog" => "This bean, has a weakness",
                ),
            ),
        );
    }

    public function testgetParentRecord()
    {
        $record = SugarTestMeetingUtilities::createMeeting();
        $commentslog_field = new SugarFieldCommentslog('commentslog');

        $commentslog_field->apiSave($record, array('commentslog' => 'watashigakita!!'), 'commentslog', array());

        $record->load_relationship('commentslog_link');
        $commentslog_beans = $record->commentslog_link->getBeans();

        self::$created_commentslogs[] = array_keys($commentslog_beans)[0];

        $bean = BeanFactory::retrieveBean('Commentslog', array_keys($commentslog_beans)[0]);

        $actual = $bean->getParentRecord();

        $this->assertEquals($record->id, $actual['record']);
        $this->assertEquals($record->getModuleName(), $actual['module']);
    }
}
