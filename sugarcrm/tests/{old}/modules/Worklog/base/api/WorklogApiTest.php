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

class WorklogApiTest extends TestCase
{
    /**
     * @var RestService
     */
    protected $service = null;

    /**
     * @var WorklogApi
     */
    protected $api = null;

    /**
     * The ids of the created worklog, for tear down
     * @var array
     */
    private static $created_worklog;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('app_list_strings');

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new WorklogApi();
        self::$created_worklog = array();
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        $ids = "'" . implode("','", self::$created_worklog) . "'";

        $db->query("DELETE FROM worklog WHERE id IN ($ids)");
        $db->query("DELETE FROM worklog_index WHERE worklog_id IN ($ids)");

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
     * Test of the api returns the parent record instead of the worklog record
     */
    public function testRetrieveRecord()
    {
        $meeting = SugarTestMeetingUtilities::createMeeting();
        $worklog_entry = "hakuna matata";
        $worklog_field = new SugarFieldWorklog('worklog');

        $worklog_field->apiSave($meeting, array('worklog' => $worklog_entry), array(), array());

        $meeting->load_relationship('worklog_link');
        $worklog_beans = $meeting->worklog_link->getBeans();

        self::$created_worklog[] = array_keys($worklog_beans)[0];

        // start testing
        $actual = $this->api->retrieveRecord($this->service, array(
            'module' => 'Worklog',
            'record' => array_keys($worklog_beans)[0],
        ));

        $this->assertSame($actual['id'], $meeting->id);
    }
}
