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

class SugarFieldCommentslogTest extends TestCase
{
    /**
     * @var SugarFieldCommentslog
     */
    private static $commentslog_field;

    /**
     * The commentslogs created so far
     * @var array
     */
    private static $created_commentslogs;

    /**
     * @var RestService
     */
    protected $service = null;

    public static function setUpBeforeClass()
    {
        self::$commentslog_field = new SugarFieldCommentslog('commentslog');
        self::$created_commentslogs = array();
    }

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        $this->removeCommentslog();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestHelper::tearDown();
        self::$created_commentslogs = array();
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
     * @param array $params The $params to save
     * @dataProvider paramsProvider()
     */
    public function testapiFormatField(array $params)
    {
        $parent = SugarTestMeetingUtilities::createMeeting();
        $data = array();

        self::$commentslog_field->apiSave($parent, $params, 'commentslog', array());

        $this->recordSaved($parent);

        // get data out and check
        self::$commentslog_field->apiFormatField($data, $parent, array(), 'commentslog', array(), array(), $this->service);

        $this->assertArrayHasKey('created_by_name', $data['commentslog'][0]);
        $this->assertSame($data['commentslog'][0]['created_by_name'], $GLOBALS['current_user']->full_name);
        $this->assertSame($data['commentslog'][0]['created_by'], $GLOBALS['current_user']->id);
        $this->assertArrayHasKey('date_entered', $data['commentslog'][0]);
        $this->assertSame($data['commentslog'][0]['entry'], $params['commentslog']);
        $this->arrayHasKey('created_by_link', $data['commentslog'][0]);
    }

    /**
     * @param array $params The $params to save
     * @dataProvider paramsProvider()
     */
    public function testapiSave(array $params)
    {
        $parent = SugarTestMeetingUtilities::createMeeting();
        self::$commentslog_field->apiSave($parent, $params, 'commentslog', array());

        $saved = $this->recordSaved($parent);

        $this->assertCount(1, $saved);
        $this->assertSame($params['commentslog'], $saved[self::$created_commentslogs[0]]->entry);
        $this->assertNotEmpty($saved[self::$created_commentslogs[0]]->date_entered);
        $this->assertSame($GLOBALS['current_user']->id, $saved[self::$created_commentslogs[0]]->modified_user_id);
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

        self::$commentslog_field->apiSave($parent, $params, 'commentslog', array());
        SugarTestHelper::tearDown();
        SugarTestHelper::setUp('current_user');

        $this->recordSaved($parent);

        // get data out and check
        self::$commentslog_field->apiFormatField($data, $parent, array(), 'commentslog', array(), array(), $this->service);

        $this->assertSame($data['commentslog'][0]['created_by_name'], '');
        $this->assertArrayHasKey('date_entered', $data['commentslog'][0]);
        $this->assertSame($data['commentslog'][0]['entry'], $params['commentslog']);
    }

    public function paramsProvider()
    {
        return array(
            array(
                array(
                    'commentslog' => 'To infinity, and beyond',
                ),
            ),
        );
    }

    /**
     * Tests having a record in different module
     * @param array $params_set sets of params to save
     * @requires $params_set must to have 3 sets of params
     * @dataProvider multipleCommentslogProvider()
     */
    public function testCommentslogAccrossDifferentModule(array $params_set)
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
     * @dataProvider multipleCommentslogProvider()
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
     * @dataProvider multipleCommentslogProvider()
     */
    public function testDifferentModuleMultipleRecords(array $params_set)
    {
        $parent1 = SugarTestMeetingUtilities::createMeeting();
        $parent2 = SugarTestMeetingUtilities::createMeeting();
        $parent3 = SugarTestTaskUtilities::createTask();

        $this->threeBeanChecker($parent1, $parent2, $parent3, $params_set);
    }

    public function multipleCommentslogProvider()
    {
        $params_set1 = array(
            array(
                'commentslog' => "Cuz I'm batman",
            ),
            array(
                'commentslog' => "Why so serious?",
            ),
            array(
                'commentslog' => "So dark, are you from the DC comic universe?",
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
        self::$commentslog_field->apiSave($parent1, $params_set[0], 'commentslog', array());
        $saved1 = $this->recordSaved($parent1);
        self::$commentslog_field->apiSave($parent2, $params_set[1], 'commentslog', array());
        $saved2 = $this->recordSaved($parent2);
        self::$commentslog_field->apiSave($parent3, $params_set[2], 'commentslog', array());
        $saved3 = $this->recordSaved($parent3);

        $this->assertCount(1, $saved1);
        $this->assertCount(1, $saved2);
        $this->assertCount(1, $saved3);

        $this->assertSame($params_set[0]['commentslog'], $saved1[self::$created_commentslogs[0]]->entry);
        $this->assertSame($params_set[1]['commentslog'], $saved2[self::$created_commentslogs[1]]->entry);
        $this->assertSame($params_set[2]['commentslog'], $saved3[self::$created_commentslogs[2]]->entry);
    }

    /**
     * Puts all the saved commentslog in $parent in $created_commentslog to assist tear_down
     * @param SugarBean $parent The bean to look for saved id
     * @return array The set of saved commentslog bean, in the format of the following
     *               <id> => <bean>
     */
    public function recordSaved(SugarBean $parent)
    {
        $parent->load_relationship('commentslog_link');
        $saved = $parent->commentslog_link->getBeans();

        foreach ($saved as $bean) {
            self::$created_commentslogs[] = $bean->id;
        }

        return $saved;
    }
}
