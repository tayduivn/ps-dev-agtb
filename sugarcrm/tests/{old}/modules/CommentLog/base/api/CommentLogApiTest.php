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

class CommentLogApiTest extends TestCase
{
    /**
     * @var RestService
     */
    protected $service = null;

    /**
     * @var CommentLogApi
     */
    protected $api = null;

    /**
     * The ids of the created commentlog, for tear down
     * @var array
     */
    private static $created_commentlog;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('app_list_strings');

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new CommentLogApi();
        self::$created_commentlog = array();
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        $ids = "'" . implode("','", self::$created_commentlog) . "'";

        $db->query("DELETE FROM commentlog WHERE id IN ($ids)");
        $db->query("DELETE FROM commentlog_rel WHERE commentlog_id IN ($ids)");

        SugarTestMeetingUtilities::removeAllCreatedMeetings();

        SugarTestHelper::tearDown();
    }

    /**
     * @expectedException SugarApiExceptionNoMethod
     */
    public function testAccessBlocker()
    {
        $this->api->accessBlocker($this->service, array());
    }

    /**
     * Test of the api returns the parent record instead of the commentlog record
     */
    public function testRetrieveRecord()
    {
        $meeting = SugarTestMeetingUtilities::createMeeting();
        $commentlog_entry = "hakuna matata";
        $commentlog_field = new SugarFieldCommentLog('commentlog');

        $commentlog_field->apiSave($meeting, array('commentlog' => $commentlog_entry), array(), array());

        $meeting->load_relationship('commentlog_link');
        $commentlog_beans = $meeting->commentlog_link->getBeans();

        self::$created_commentlog[] = array_keys($commentlog_beans)[0];

        // start testing
        $actual = $this->api->retrieveRecord($this->service, array(
            'module' => 'commentlog',
            'record' => array_keys($commentlog_beans)[0],
        ));

        $this->assertSame($actual['id'], $meeting->id);
    }
}
