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

class RestTestLogin extends RestTestBase
{
    public function setUp()
    {
        // Start out with a fake auth token to prevent _restCall from auto logging in
        $this->authToken = 'LOGGING_IN';
        
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();

        $GLOBALS['db']->query("DELETE FROM oauth_consumer WHERE id LIKE 'UNIT%'");
        $GLOBALS['db']->query("DELETE FROM oauth_tokens WHERE consumer LIKE '_unit_%'");
        if ( isset($this->contact->id) ) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '".$this->contact->id."'");
            $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id = '".$this->contact->id."'");
        }
        if ( isset($this->apiuser->id) ) {
            $GLOBALS['db']->query("DELETE FROM users WHERE id = '".$this->apiuser->id."'");
            $GLOBALS['db']->query("DELETE FROM users_cstm WHERE id = '".$this->apiuser->id."'");
        }
    }

    public function testRestLoginUser()
    {
        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => $this->_user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
        );

        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);
        
        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);
    }

    public function testRestLoginUserAutocreateKey()
    {
        $GLOBALS['db']->query("DELETE FROM oauth_keys WHERE c_key = 'sugar'");

        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => $this->_user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
        );
        
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);
        
        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);
    }


    public function testRestLoginCustomIdUser()
    {
        // Create a unit test login ID
        $consumer = BeanFactory::newBean('OAuthKeys');
        $consumer->id = 'UNIT-TEST-regularlogin';
        $consumer->new_with_id = true;
        $consumer->c_key = '_unit_regularlogin';
        $consumer->c_secret = '';
        $consumer->oauth_type = 'oauth2';
        $consumer->client_type = 'user';
        $consumer->save();
        
        $GLOBALS['db']->commit();
        
        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => $this->_user->user_name,
            'client_id' => $consumer->c_key,
            'client_secret' => '',
        );
        
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);
        
        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);
    }

    public function testRestLoginRefresh()
    {
        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => $this->_user->user_name,
            'client_id' => 'sugar',
            'client_secret' => '',
        );
        
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);
        
        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);

        $refreshToken = $reply['reply']['refresh_token'];

        
        $args = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => 'sugar',
            'client_secret' => '',
        );
        
        // Prevents _restCall from automatically logging in
        $this->authToken = 'LOGGING_IN';
        $reply2 = $this->_restCall('oauth2/token',json_encode($args));
        // if ( empty($reply2['reply']['access_token']) ) { print_r($reply2); }
        $this->assertNotEmpty($reply2['reply']['access_token']);
        $this->assertNotEmpty($reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply2['reply']['access_token'],$reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply2['reply']['access_token']);
        $this->assertNotEquals($reply['reply']['refresh_token'],$reply2['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply2['reply']['token_type']);
        
        $this->authToken = $reply2['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);
    }

    public function testRestLoginSupportPortal()
    {
        // Create a portal API user
        $this->apiuser = BeanFactory::newBean('Users');
        $this->apiuser->id = "UNIT-TEST-apiuser";
        $this->apiuser->new_with_id = true;
        $this->apiuser->first_name = "Portal";
        $this->apiuser->last_name = "Apiuserson";
        $this->apiuser->username = "_unittest_apiuser";
        $this->apiuser->portal_only = true;
        $this->apiuser->status = 'Active';
        $this->apiuser->save();

        // Create a contact to log in as
        $this->contact = BeanFactory::newBean('Contacts');
        $this->contact->id = "UNIT-TEST-littleunittest";
        $this->contact->new_with_id = true;
        $this->contact->first_name = "Little";
        $this->contact->last_name = "Unittest";
        $this->contact->description = "Little Unittest";
        $this->contact->portal_name = "liltest@unit.com";
        $this->contact->portal_active = '1';
        $this->contact->portal_password = User::getPasswordHash("unittest");
        $this->contact->save();

        $GLOBALS['db']->commit();
        
        $args = array(
            'grant_type' => 'password',
            'username' => $this->contact->portal_name,
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
        );
        
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        // if ( empty($reply['reply']['access_token']) ) { print_r($reply); }
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);

        $this->authToken = $reply['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);

        $refreshToken = $reply['reply']['refresh_token'];

        
        $args = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => 'support_portal',
            'client_secret' => '',
        );
        
        // Prevents _restCall from automatically logging in
        $this->authToken = 'LOGGING_IN';
        $reply2 = $this->_restCall('oauth2/token',json_encode($args));
        // if ( empty($reply2['reply']['access_token']) ) { print_r($reply2); }
        $this->assertNotEmpty($reply2['reply']['access_token']);
        $this->assertNotEmpty($reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply2['reply']['access_token'],$reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply2['reply']['access_token']);
        $this->assertNotEquals($reply['reply']['refresh_token'],$reply2['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply2['reply']['token_type']);
        
        $this->authToken = $reply2['reply']['access_token'];
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);
                                                          
    }

    function testLoginFromRegularSession() {
        // Kill the session
        session_regenerate_id();
        session_start();

        // We have the technology, we can rebuild it
        $_SESSION = array();
        $_SESSION['is_valid_session'] = true;
        $_SESSION['ip_address'] = '127.0.0.1';
        $_SESSION['user_id'] = $this->_user->id;
        $_SESSION['type'] = 'user';
        $_SESSION['authenticated_user_id'] = $this->_user->id;
        $_SESSION['unique_key'] = $GLOBALS['sugar_config']['unique_key'];
        
        $generatedSession = session_id();
        session_write_close();

        // Try using a normal session as the oauth_token
        $this->authToken = $generatedSession;
        
        $replyPing = $this->_restCall('ping');
        $this->assertEquals('pong',$replyPing['reply']);

        // Now try passing the oauth_token in as a GET variable
        $this->authToken = 'LOGGING_IN';
        $replyPing = $this->_restCall('ping?oauth_token='.$generatedSession);
        $this->assertEquals('pong',$replyPing['reply']);
        
    }

    function testBadLogin() {
        $args = array(
            'grant_type' => 'password',
            'username' => $this->_user->user_name,
            'password' => 'this is not the correct password',
            'client_id' => 'sugar',
            'client_secret' => '',
        );

        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['error']);
        $this->assertEquals('need_login',$reply['reply']['error']);
    }
}
