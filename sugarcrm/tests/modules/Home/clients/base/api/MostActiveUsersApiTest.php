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

require_once 'include/api/RestService.php';
require_once 'modules/Home/clients/base/api/MostActiveUsersApi.php';

/**
 * Tests MostActiveUsers dashlet api.
 */
class MostActiveUsersApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Meeting
     */
    protected $meeting;

    /**
     * @var Call
     */
    protected $call;

    /**
     * @var Email
     */
    protected $outboundEmail;

    /**
     * @var Email
     */
    protected $inboundEmail;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var User
     */
    protected $additionalUser;

    /**
     * @var MostActiveUsersApi
     */
    protected $api;

    protected function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));

        $this->api = new MostActiveUsersApi();

        $this->user = $GLOBALS['current_user'];
        $this->additionalUser = SugarTestUserUtilities::createAnonymousUser();

        $this->meeting = SugarTestMeetingUtilities::createMeeting();
        $this->meeting->status = 'Held';
        $this->meeting->assigned_user_id = $this->user->id;
        $this->meeting->save();

        $this->call = SugarTestCallUtilities::createCall();
        $this->call->status = 'Held';
        $this->call->assigned_user_id = $this->user->id;
        $this->call->save();

        $this->outboundEmail = SugarTestEmailUtilities::createEmail();
        $this->outboundEmail->assigned_user_id = $this->user->id;
        $this->outboundEmail->save();

        $this->inboundEmail = SugarTestEmailUtilities::createEmail();
        $this->inboundEmail->assigned_user_id = $this->user->id;
        $this->inboundEmail->type = 'inbound';
        $this->inboundEmail->save();
    }

    protected function tearDown()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        SugarTestHelper::tearDown();
    }

    /**
     * Returns meetings, calls, inbound and outbound emails records.
     */
    public function testReturnsSpecificSet()
    {
        $expected = array(
            'user_id' => $this->user->id,
            'count' => '1',
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name
        );

        $actual = $this->api->getMostActiveUsers(new RestService(), array());

        $this->assertEquals($expected, $actual['meetings']);
        $this->assertEquals($expected, $actual['calls']);
        $this->assertEquals($expected, $actual['inbound_emails']);
        $this->assertEquals($expected, $actual['outbound_emails']);
    }

    /**
     * Returns the bigest count of records for user despite of created date.
     */
    public function testReturnsWithBiggestCount()
    {
        // Asssigh two meetings for the first user and one for the second.
        $meeting2 = SugarTestMeetingUtilities::createMeeting();
        $meeting2->status = 'Held';
        $meeting2->assigned_user_id = $this->user->id;
        $meeting2->save();

        $meeting3 = SugarTestMeetingUtilities::createMeeting();
        $meeting3->status = 'Held';
        $meeting3->assigned_user_id = $this->additionalUser->id;
        $meeting3->save();

        $expected = array(
            'user_id' => $this->user->id,
            'count' => '2',
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name
        );

        $actual = $this->api->getMostActiveUsers(new RestService(), array());

        $this->assertEquals($expected, $actual['meetings']);
    }

    /**
     * Test date filter.
     * Should return records where entered date is less than 10 days.
     */
    public function testDateFilter()
    {
        $dt = new SugarDateTime('-12 day');

        $outboundEmail2 = SugarTestEmailUtilities::createEmail();
        $outboundEmail2->assigned_user_id = $this->additionalUser->id;
        // Set the date and block its update.
        $outboundEmail2->update_date_modified = false;
        $outboundEmail2->update_date_entered = true;
        $outboundEmail2->date_entered = $dt->asDb();
        $outboundEmail2->save();

        $outboundEmail3 = SugarTestEmailUtilities::createEmail();
        $outboundEmail3->assigned_user_id = $this->additionalUser->id;
        $outboundEmail3->date_entered = $dt->asDb();
        $outboundEmail3->update_date_modified = false;
        $outboundEmail3->update_date_entered = true;
        $outboundEmail3->save();

        $expected = array(
            'user_id' => $this->user->id,
            'count' => '1',
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name
        );

        $actual = $this->api->getMostActiveUsers(new RestService(), array('days' => 10));

        $this->assertEquals($expected, $actual['outbound_emails']);
    }
}
