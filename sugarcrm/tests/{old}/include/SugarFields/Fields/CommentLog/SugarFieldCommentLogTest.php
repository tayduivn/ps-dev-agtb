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

class SugarFieldCommentLogTest extends TestCase
{
    /**
     * @var SugarFieldCommentLog
     */
    private static $commentlog_field;

    /**
     * The commentlogs created so far
     * @var array
     */
    private static $created_commentlogs;

    /**
     * @var RestService
     */
    protected $service = null;

    public static function setUpBeforeClass()
    {
        self::$commentlog_field = new SugarFieldCommentLog('commentlog');
        self::$created_commentlogs = array();
    }

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        $this->removeCommentLog();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestHelper::tearDown();
        self::$created_commentlogs = array();
    }

    /**
     * Removes the commentlog in $created_commentlogs
     */
    public function removeCommentLog()
    {
        $db = DBManagerFactory::getInstance();
        $ids = "'" . implode("','", self::$created_commentlogs) . "'";

        $db->query("DELETE FROM commentlog WHERE id IN ($ids)");
        $db->query("DELETE FROM commentlog_rel WHERE commentlog_id IN ($ids)");
    }

    /**
     * @param array $params The $params to save
     * @dataProvider paramsProvider()
     */
    public function testapiFormatField(array $params)
    {
        $parent = SugarTestMeetingUtilities::createMeeting();
        $data = array();

        self::$commentlog_field->apiSave($parent, $params, 'commentlog', array());

        $this->recordSaved($parent);

        // get data out and check
        self::$commentlog_field->apiFormatField($data, $parent, array(), 'commentlog', array(), array(), $this->service);

        $this->assertArrayHasKey('created_by_name', $data['commentlog'][0]);
        $this->assertSame($data['commentlog'][0]['created_by_name'], $GLOBALS['current_user']->full_name);
        $this->assertSame($data['commentlog'][0]['created_by'], $GLOBALS['current_user']->id);
        $this->assertArrayHasKey('date_entered', $data['commentlog'][0]);
        $this->assertSame($data['commentlog'][0]['entry'], $params['commentlog']);
        $this->arrayHasKey('created_by_link', $data['commentlog'][0]);
    }

    /**
     * @param array $params The $params to save
     * @dataProvider paramsProvider()
     */
    public function testapiSave(array $params)
    {
        $parent = SugarTestMeetingUtilities::createMeeting();
        self::$commentlog_field->apiSave($parent, $params, 'commentlog', array());

        $saved = $this->recordSaved($parent);

        $this->assertCount(1, $saved);
        $this->assertSame($params['commentlog'], $saved[self::$created_commentlogs[0]]->entry);
        $this->assertNotEmpty($saved[self::$created_commentlogs[0]]->date_entered);
        $this->assertSame($GLOBALS['current_user']->id, $saved[self::$created_commentlogs[0]]->modified_user_id);
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

        self::$commentlog_field->apiSave($parent, $params, 'commentlog', array());
        SugarTestHelper::tearDown();
        SugarTestHelper::setUp('current_user');

        $this->recordSaved($parent);

        // get data out and check
        self::$commentlog_field->apiFormatField($data, $parent, array(), 'commentlog', array(), array(), $this->service);

        $this->assertSame($data['commentlog'][0]['created_by_name'], '');
        $this->assertArrayHasKey('date_entered', $data['commentlog'][0]);
        $this->assertSame($data['commentlog'][0]['entry'], $params['commentlog']);
    }

    public function paramsProvider()
    {
        return array(
            array(
                array(
                    'commentlog' => 'To infinity, and beyond',
                ),
            ),
        );
    }

    /**
     * Tests having a record in different module
     * @param array $params_set sets of params to save
     * @requires $params_set must to have 3 sets of params
     * @dataProvider multipleCommentLogProvider()
     */
    public function testCommentLogAccrossDifferentModule(array $params_set)
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
     * @dataProvider multipleCommentLogProvider()
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
     * @dataProvider multipleCommentLogProvider()
     */
    public function testDifferentModuleMultipleRecords(array $params_set)
    {
        $parent1 = SugarTestMeetingUtilities::createMeeting();
        $parent2 = SugarTestMeetingUtilities::createMeeting();
        $parent3 = SugarTestTaskUtilities::createTask();

        $this->threeBeanChecker($parent1, $parent2, $parent3, $params_set);
    }

    public function multipleCommentLogProvider()
    {
        $params_set1 = array(
            array(
                'commentlog' => "Cuz I'm batman",
            ),
            array(
                'commentlog' => "Why so serious?",
            ),
            array(
                'commentlog' => "So dark, are you from the DC comic universe?",
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
        self::$commentlog_field->apiSave($parent1, $params_set[0], 'commentlog', array());
        $saved1 = $this->recordSaved($parent1);
        self::$commentlog_field->apiSave($parent2, $params_set[1], 'commentlog', array());
        $saved2 = $this->recordSaved($parent2);
        self::$commentlog_field->apiSave($parent3, $params_set[2], 'commentlog', array());
        $saved3 = $this->recordSaved($parent3);

        $this->assertCount(1, $saved1);
        $this->assertCount(1, $saved2);
        $this->assertCount(1, $saved3);

        $this->assertSame($params_set[0]['commentlog'], $saved1[self::$created_commentlogs[0]]->entry);
        $this->assertSame($params_set[1]['commentlog'], $saved2[self::$created_commentlogs[1]]->entry);
        $this->assertSame($params_set[2]['commentlog'], $saved3[self::$created_commentlogs[2]]->entry);
    }

    /**
     * Puts all the saved commentlog in $parent in $created_commentlog to assist tear_down
     * @param SugarBean $parent The bean to look for saved id
     * @return array The set of saved commentlog bean, in the format of the following
     *               <id> => <bean>
     */
    public function recordSaved(SugarBean $parent)
    {
        $parent->load_relationship('commentlog_link');
        $saved = $parent->commentlog_link->getBeans();

        foreach ($saved as $bean) {
            self::$created_commentlogs[] = $bean->id;
        }

        return $saved;
    }
}
