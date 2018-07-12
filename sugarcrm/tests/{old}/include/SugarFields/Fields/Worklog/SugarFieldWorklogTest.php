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

class SugarFieldWorklogTest extends TestCase
{
    /**
     * @var SugarFieldWorklog
     */
    private static $worklog_field;

    /**
     * The worklogs created so far
     * @var array
     */
    private static $created_worklogs;

    public static function setUpBeforeClass()
    {
        self::$worklog_field = new SugarFieldWorklog('worklog');
        self::$created_worklogs = array();
    }

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        $this->removeWorklog();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestHelper::tearDown();
        self::$created_worklogs = array();
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
     * @param array $params The $params to save
     * @dataProvider paramsProvider()
     */
    public function testapiFormatField(array $params)
    {
        $parent = SugarTestMeetingUtilities::createMeeting();
        $data = array();

        self::$worklog_field->apiSave($parent, $params, 'worklog', array());

        $this->recordSaved($parent);

        // get data out and check
        self::$worklog_field->apiFormatField($data, $parent, array(), 'worklog', array());

        $this->assertSame($data['worklog'][0]['author_name'], $GLOBALS['current_user']->full_name);
        $this->assertSame(
            $data['worklog'][0]['author_link'],
            '#bwc/index.php?action=DetailView&module=Employees&record=' . $GLOBALS['current_user']->id
        );
        $this->assertArrayHasKey('date_entered', $data['worklog'][0]);
        $this->assertSame($data['worklog'][0]['entry'], $params['worklog']);
    }

    /**
     * @param array $params The $params to save
     * @dataProvider paramsProvider()
     */
    public function testapiSave(array $params)
    {
        $parent = SugarTestMeetingUtilities::createMeeting();
        self::$worklog_field->apiSave($parent, $params, 'worklog', array());

        $saved = $this->recordSaved($parent);

        $this->assertCount(1, $saved);
        $this->assertSame($params['worklog'], $saved[self::$created_worklogs[0]]->entry);
        $this->assertNotEmpty($saved[self::$created_worklogs[0]]->date_entered);
        $this->assertSame($GLOBALS['current_user']->id, $saved[self::$created_worklogs[0]]->modified_user_id);
    }

    /**
     * Tests whether the user will be blank when the user is deleted from the db
     * @param array $params The $params to save
     * @dataProvider paramsProvider()
     */
    public function testDisplayDeletedUser($params)
    {
        $parent = SugarTestMeetingUtilities::createMeeting();
        $data = array();

        self::$worklog_field->apiSave($parent, $params, 'worklog', array());
        SugarTestHelper::tearDown();
        SugarTestHelper::setUp('current_user');

        $this->recordSaved($parent);

        // get data out and check
        self::$worklog_field->apiFormatField($data, $parent, array(), 'worklog', array());

        $this->assertSame($data['worklog'][0]['author_name'], '');
        $this->assertSame($data['worklog'][0]['author_link'], '');
        $this->assertArrayHasKey('date_entered', $data['worklog'][0]);
        $this->assertSame($data['worklog'][0]['entry'], $params['worklog']);
    }

    public function paramsProvider()
    {
        return array(
            array(
                array(
                    'worklog' => 'To infinity, and beyond',
                ),
            ),
        );
    }

    /**
     * Tests having a record in different module
     * @param array $params_set sets of params to save
     * @requires $params_set must to have 3 sets of params
     * @dataProvider multipleWorklogProvider()
     */
    public function testWorklogAccrossDifferentModule(array $params_set)
    {
        $parent1 = SugarTestMeetingUtilities::createMeeting();
        $parent2 = SugarTestCaseUtilities::createCase();
        $parent3 = SugarTestTaskUtilities::createTask();

        $this->threeBeanChecker($parent1, $parent2, $parent3, $params_set);
    }

    /**
     * Testing having multiple records in a single module
     * @param array $params_set sets of params to save
     * @requires $params_set must to have 3 sets of params
     * @dataProvider multipleWorklogProvider()
     */
    public function testOneModuleMultipleRecord(array $params_set)
    {
        $parent1 = SugarTestMeetingUtilities::createMeeting();
        $parent2 = SugarTestMeetingUtilities::createMeeting();
        $parent3 = SugarTestMeetingUtilities::createMeeting();

        $this->threeBeanChecker($parent1, $parent2, $parent3, $params_set);
    }

    /**
     * Tests having multiple records in multiple different modules
     * @param array $params_set sets of params to save
     * @requires $params_set must to have 3 sets of params
     * @dataProvider multipleWorklogProvider()
     */
    public function testDifferentModuleMultipleRecords(array $params_set)
    {
        $parent1 = SugarTestMeetingUtilities::createMeeting();
        $parent2 = SugarTestMeetingUtilities::createMeeting();
        $parent3 = SugarTestTaskUtilities::createTask();

        $this->threeBeanChecker($parent1, $parent2, $parent3, $params_set);
    }

    public function multipleWorklogProvider()
    {
        $params_set1 = array(
            array(
                'worklog' => "Cuz I'm batman",
            ),
            array(
                'worklog' => "Why so serious?",
            ),
            array(
                'worklog' => "So dark, are you from the DC comic universe?",
            ),
        );

        return array(
            array($params_set1),
        );
    }

    /**
     * A helper testing function for record module combination testing
     * @param SugarBean $parent1 First record to check
     * @param SugarBean $parent2 Second record to check
     * @param SugarBean $parent3 Third record to check
     * @param array $params_set The data saved to the three beans, in same sequence as
     *                          the order of the three parents
     */
    public function threeBeanChecker(
        SugarBean $parent1,
        SugarBean $parent2,
        SugarBean $parent3,
        array $params_set
    ) {
        self::$worklog_field->apiSave($parent1, $params_set[0], 'worklog', array());
        $saved1 = $this->recordSaved($parent1);
        self::$worklog_field->apiSave($parent2, $params_set[1], 'worklog', array());
        $saved2 = $this->recordSaved($parent2);
        self::$worklog_field->apiSave($parent3, $params_set[2], 'worklog', array());
        $saved3 = $this->recordSaved($parent3);

        $this->assertCount(1, $saved1);
        $this->assertCount(1, $saved2);
        $this->assertCount(1, $saved3);

        $this->assertSame($params_set[0]['worklog'], $saved1[self::$created_worklogs[0]]->entry);
        $this->assertSame($params_set[1]['worklog'], $saved2[self::$created_worklogs[1]]->entry);
        $this->assertSame($params_set[2]['worklog'], $saved3[self::$created_worklogs[2]]->entry);
    }

    /**
     * Puts all the saved worklog in $parent in $created_worklog to assist tear_down
     * @param SugarBean $parent The bean to look for saved id
     * @return array The set of saved worklog bean, in the format of the following
     *               <id> => <bean>
     */
    public function recordSaved(SugarBean $parent)
    {
        $parent->load_relationship('worklog_link');
        $saved = $parent->worklog_link->getBeans();

        foreach ($saved as $bean) {
            self::$created_worklogs[] = $bean->id;
        }

        return $saved;
    }
}
