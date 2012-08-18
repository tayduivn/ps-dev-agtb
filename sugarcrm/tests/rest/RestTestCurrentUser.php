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

class RestTestCurrentUser extends RestTestBase {
    public function tearDown()
    {
        parent::tearDown();
    }

    public function testRetrieve() {
        $restReply = $this->_restCall("me");
        $this->assertNotEmpty($restReply['reply']['current_user']['id']);
    }

    public function testUpdate() {
        $restReply = $this->_restCall("me", json_encode(array('first_name' => 'UNIT TEST - AFTER')), "PUT");
        $this->assertNotEquals(stripos($restReply['reply']['current_user']['full_name'], 'UNIT TEST - AFTER'), false);
    }

    public function testPasswordUpdate() {
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'W0nkY123', 'old_password' => $GLOBALS['current_user']->user_name)),
            'PUT');
        $this->assertEquals($reply['reply']['current_user']['valid'], true);
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'Y3s1tWorks', 'old_password' => 'W0nkY123')),
            'PUT');
        $this->assertEquals($reply['reply']['current_user']['valid'], true);

        // Incorrect old password returns valid:false
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'Y@ky1234', 'old_password' => 'justwrong!')),
            'PUT');
        $this->assertEquals($reply['reply']['current_user']['valid'], false);
    }
        
}
