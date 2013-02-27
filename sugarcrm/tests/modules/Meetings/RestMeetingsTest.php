<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once('tests/rest/RestTestBase.php');

class RestMeetingsTest extends RestTestBase {
    public function tearDown()
    {
        if ( isset($this->meeting_id) ) {
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting_id}'");
            if ($GLOBALS['db']->tableExists('meetings_cstm')) {
                $GLOBALS['db']->query("DELETE FROM meetings_cstm WHERE id_c = '{$this->meeting_id}'");
            }
        }

        if ( isset($this->contact_id) ) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact_id}'");
            if ($GLOBALS['db']->tableExists('contacts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c = '{$this->contact_id}'");
            }
        }

        //BEGIN SUGARCRM flav=pro ONLY
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '".$GLOBALS['current_user']->id."'");
        //END SUGARCRM flav=pro ONLY
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCreateNoMinutes() {
        $restReply = $this->_restCall("Meetings/",
                                      json_encode(array(
                                                      'name'=>'UNIT TEST - Meeting with no minutes', 
                                                      "deleted" => "0",
                                                      "status" => "Planned",
                                                      "reminder_time" => -1,
                                                      "email_reminder_time" => -1,
                                                      "email_reminder_sent" => 0,
                                                      "repeat_interval" => 1,
                                                      "assigned_user_id" => $GLOBALS['current_user']->id,
                                                      "team_name" => array(array("id" => 1,"name" => "Global", "primary" => true)),
                                                      "date_start" => "2012-12-06T00:00:00.000Z",
                                                      "direction" => "Inbound",
                                                      "duration_hours" => "23"
                                                      )),
                                      'POST');
        $this->assertTrue(isset($restReply['reply']['id']),
                          "An Meeting was not created (or if it was, the ID was not returned)");
        $this->meeting_id = $restReply['reply']['id'];

        $this->assertEquals('2012-12-06T23:00:00+00:00',$restReply['reply']['date_end'],
                            'The end date was not calculated correctly');
    }

    /**
     * @group rest
     */
    public function testCreateNoContact() {
        $restReply = $this->_restCall("Meetings/",
                                      json_encode(array(
                                                      'name'=>'UNIT TEST - Meeting with no minutes', 
                                                      "deleted" => "0",
                                                      "status" => "Planned",
                                                      "reminder_time" => -1,
                                                      "email_reminder_time" => -1,
                                                      "email_reminder_sent" => 0,
                                                      "repeat_interval" => 1,
                                                      "assigned_user_id" => $GLOBALS['current_user']->id,
                                                      "team_name" => array(array("id" => 1,"name" => "Global", "primary" => true)),
                                                      "date_start" => "2012-12-06T00:00:00.000Z",
                                                      "direction" => "Inbound",
                                                      "duration_hours" => "23"
                                                      )),
                                      'POST');
        $this->assertTrue(isset($restReply['reply']['id']),
                          "An Meeting was not created (or if it was, the ID was not returned)");

        $this->meeting_id = $restReply['reply']['id'];

        $this->assertEquals('',$restReply['reply']['contact_name'],
                            'The contact name was not correct');
    }    

    /**
     * @group rest
     */
    public function testCreateWithParentContact() {
        $contact = BeanFactory::newBean('Contacts');
        $contact->first_name = "UNIT";
        $contact->last_name = "TEST";
        $contact->save();
        $this->contact_id = $contact->id;

        $restReply = $this->_restCall("Meetings/",
                                      json_encode(array(
                                                      'name'=>'UNIT TEST - Meeting with parent contact', 
                                                      "deleted" => "0",
                                                      "status" => "Planned",
                                                      "reminder_time" => -1,
                                                      "email_reminder_time" => -1,
                                                      "email_reminder_sent" => 0,
                                                      "repeat_interval" => 1,
                                                      "assigned_user_id" => $GLOBALS['current_user']->id,
                                                      "team_name" => array(array("id" => 1,"name" => "Global", "primary" => true)),
                                                      "date_start" => "2012-12-06T00:00:00.000Z",
                                                      "direction" => "Inbound",
                                                      "duration_hours" => "0",
                                                      "duration_minutes" => "30",
                                                      "parent_type" => "Contacts",
                                                      "send_invites" => "1",
                                                      "parent_id" => $this->contact_id,
                                                      )),
                                      'POST');
        $this->assertTrue(isset($restReply['reply']['id']),
                          "An Meeting was not created (or if it was, the ID was not returned)");
        $this->meeting_id = $restReply['reply']['id'];

        $this->assertEquals($this->contact_id,$restReply['reply']['parent_id'],
                            'The parent id was not set correctly');

        $restReply = $this->_restCall("Meetings/".$this->meeting_id."/link/contacts");
        $this->assertEquals($this->contact_id,$restReply['reply']['records'][0]['id'],"The contact was not linked to the meeting.");

        $restReply = $this->_restCall("Meetings/{$this->meeting_id}/link/users");

        $this->assertNotEmpty($restReply['reply']['records'], "No users linked anymore");
        $this->assertEquals($GLOBALS['current_user']->id, $restReply['reply']['records'][0]['id'], "The users don't match");
    }

}