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

class CommentslogApiTest extends TestCase
{
    /**
     * @var RestService
     */
    protected $service = null;

    /**
     * @var CommentslogApi
     */
    protected $api = null;

    /**
     * The ids of the created commentslog, for tear down
     * @var array
     */
    private static $created_commentslog;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('app_list_strings');

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new CommentslogApi();
        self::$created_commentslog = array();
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        $ids = "'" . implode("','", self::$created_commentslog) . "'";

        $db->query("DELETE FROM commentslog WHERE id IN ($ids)");
        $db->query("DELETE FROM commentslog_rel WHERE commentslog_id IN ($ids)");

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
     * Test of the api returns the parent record instead of the commentslog record
     */
    public function testRetrieveRecord()
    {
        $meeting = SugarTestMeetingUtilities::createMeeting();
        $commentslog_entry = "hakuna matata";
        $commentslog_field = new SugarFieldCommentslog('commentslog');

        $commentslog_field->apiSave($meeting, array('commentslog' => $commentslog_entry), array(), array());

        $meeting->load_relationship('commentslog_link');
        $commentslog_beans = $meeting->commentslog_link->getBeans();

        self::$created_commentslog[] = array_keys($commentslog_beans)[0];

        // start testing
        $actual = $this->api->retrieveRecord($this->service, array(
            'module' => 'commentslog',
            'record' => array_keys($commentslog_beans)[0],
        ));

        $this->assertSame($actual['id'], $meeting->id);
    }
}
