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

class RestCallHelperTest extends RestTestBase {

    public function tearDown()
    {
        parent::tearDown();
        $GLOBALS['db']->query("DELETE FROM calls WHERE id = '{$this->call_id}'");
    }

    public function testCall() {

        // create a call linked to yourself, a contact, and a lead, verify the call is linked to each and on your calendar
        $call = array(
            'name' => 'Test call',
            'duration' => 1,
            'date_start' => '2012-12-13T10:00:00-07:00',
            'date_end' => '2012-12-13T10:15:00-07:00',
            'assigned_user_id' => 1,
        );

        $restReply = $this->_restCall('Calls/', json_encode($call), 'POST');

        $this->assertTrue(isset($restReply['reply']['id']), 'call was not created, reply was: ' . print_r($restReply['reply'], true));

        $call_id = $restReply['reply']['id'];
        $this->call_id = $call_id;


        // verify the user has the Call, which will validate on calendar
        $restReplyUsers = $this->_restCall("Calls/{$call_id}/link/users");
        $users_linked = array();
        foreach($restReplyUsers['reply']['records'] AS $record) {
            $users_linked[] = $record['id'];
        }

        $this->assertTrue(in_array($GLOBALS['current_user']->id, $users_linked), "Current User was not successfully linked");
        $this->assertTrue(in_array(1, $users_linked), "Assigned User was not successfully linked");


    }

    public function testCallHeld() {

        // create a call linked to yourself, a contact, and a lead, verify the call is linked to each and on your calendar
        $call = array(
            'name' => 'Test call',
            'duration' => 1,
            'date_start' => '2012-12-13T10:00:00-07:00',
            'date_end' => '2012-12-13T10:15:00-07:00',
            'assigned_user_id' => 1,
            'status' => 'Held',
        );

        $restReply = $this->_restCall('Calls/', json_encode($call), 'POST');

        $this->assertTrue(isset($restReply['reply']['id']), 'call was not created, reply was: ' . print_r($restReply['reply'], true));

        $call_id = $restReply['reply']['id'];
        $this->call_id = $call_id;


        // verify the user has the Call, which will validate on calendar
        $restReplyUsers = $this->_restCall("Calls/{$call_id}/link/users");
        $users_linked = array();
        foreach($restReplyUsers['reply']['records'] AS $record) {
            $users_linked[] = $record['id'];
        }

        $this->assertTrue(in_array($GLOBALS['current_user']->id, $users_linked), "Current User was not successfully linked");
        $this->assertTrue(in_array(1, $users_linked), "Assigned User was not successfully linked");


    }    
}
