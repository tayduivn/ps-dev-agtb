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

class RestCurrentUserTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testRetrieve() {
        $restReply = $this->_restCall("me");
        $this->assertNotEmpty($restReply['reply']['current_user']['id']);
        $this->assertNotEmpty($restReply['reply']['current_user']['currency_id']);
        $this->assertNotEmpty($restReply['reply']['current_user']['decimal_precision']);
        //BEGIN SUGARCRM flav=pro ONLY
        $this->assertNotEmpty($restReply['reply']['current_user']['primary_team']);
        $this->assertNotEmpty($restReply['reply']['current_user']['default_teams']);
        $this->assertNotEmpty($restReply['reply']['current_user']['my_teams']);
        //END SUGARCRM flav=pro ONLY
    }

    /**
     * @group rest
     */
    public function testRetrieveDefaults()  {
        global $current_user,$sugar_config;
        $real_current_user = $current_user;
        // The reset preferences call will fail because it's trying to mess with a session
        // unless the "current user" isn't the user we are changing the preferences on.
        $current_user = new User();
        $current_user->id = 'NOT-THE-REAL-THING';
        $real_current_user->resetPreferences();
        $current_user = $real_current_user;

        $restReply = $this->_restCall('me');
        $this->assertEquals($sugar_config['datef'],$restReply['reply']['current_user']['datepref'],"trd: Date pref is not the default");
        $this->assertEquals($sugar_config['default_time_format'],$restReply['reply']['current_user']['timepref'],"trd: Time pref is not the default");

        $current_user->setPreference('datef','m/d/Y');
        $current_user->setPreference('timef','H:i a');
        $current_user->savePreferencesToDB();
        
        // Need to logout and log back in, preferences are cached in the session.
        $this->_restLogin();
        $restReply = $this->_restCall('me');
        $this->assertEquals('m/d/Y',$restReply['reply']['current_user']['datepref'],"trd: Date pref is not the configured value");
        $this->assertEquals('H:i a',$restReply['reply']['current_user']['timepref'],"trd: Time pref is not the configured value");
    }
    
    /**
     * @group rest
     */
    public function testAclUsers() {
      $restReply = $this->_restCall("me");
      // verify the user is not the admin of the users module
      $userAcl = $restReply['reply']['current_user']['acl']['Users'];
      $this->assertEquals('no', $userAcl['admin'], "This user is the admin and should not be");
      // log in as an admin
      $GLOBALS['current_user']->is_admin = 1;
      $GLOBALS['current_user']->save();
      $restReply = $this->_restCall("me");
      // verify the user is the admin of the users module
      $userAcl = $restReply['reply']['current_user']['acl']['Users'];
      $this->assertEquals('yes', $userAcl['admin'], "This user is not the admin and they should be");
    } 

    /**
     * @group rest
     */
    public function testUpdate() {
        $restReply = $this->_restCall("me", json_encode(array('first_name' => 'UNIT TEST - AFTER')), "PUT");
        $this->assertNotEquals(stripos($restReply['reply']['current_user']['full_name'], 'UNIT TEST - AFTER'), false);
    }

    /**
     * @group rest
     */
    public function testPasswordUpdate() {
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'W0nkY123', 'old_password' => $GLOBALS['current_user']->user_name)),
            'PUT');
        $this->assertEquals($reply['reply']['valid'], true);
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'Y3s1tWorks', 'old_password' => 'W0nkY123')),
            'PUT');
        $this->assertEquals($reply['reply']['valid'], true);

        // Incorrect old password returns valid:false
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'Y@ky1234', 'old_password' => 'justwrong!')),
            'PUT');
        $this->assertEquals($reply['reply']['valid'], false);
    }
        
    /**
     * @group rest
     */
    public function testPasswordVerification() {
        $reply = $this->_restCall("me/password",
            json_encode(array('password_to_verify' => $GLOBALS['current_user']->user_name)),
            'POST');
        $this->assertEquals($reply['reply']['valid'], true);
        $reply = $this->_restCall("me/password",
            json_encode(array('password_to_verify' => 'noway')),
            'POST');
        $this->assertEquals($reply['reply']['valid'], false);
    }
    
}
