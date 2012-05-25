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

class RestTestLogin extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
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

    protected function _restCall($urlPart,$postBody,$httpAction='')
    {
        $urlBase = $GLOBALS['sugar_config']['site_url'].'/rest/v9/';
        
        $ch = curl_init($urlBase.$urlPart);
        if (!empty($postBody)) {
            if (empty($httpAction)) {
                curl_setopt($ch, CURLOPT_POST, 1);
                $httpAction = 'POST';
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
        } else {
            if (empty($httpAction)) {
                $httpAction = 'GET';
            }
        }
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpAction);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $httpInfo = curl_getinfo($ch); 
        $httpReply = curl_exec($ch);

        return array('info' => $httpInfo, 'reply' => json_decode($httpReply,true), 'replyRaw' => $httpReply);
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
        
        $refreshToken = $reply['reply']['refresh_token'];

        
        $args = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => 'sugar',
            'client_secret' => '',
        );
        
        $reply2 = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply2['reply']['access_token']);
        $this->assertNotEmpty($reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply2['reply']['access_token'],$reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply2['reply']['access_token']);
        $this->assertNotEquals($reply['reply']['refresh_token'],$reply2['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply2['reply']['token_type']);
        
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

        $args = array(
            'grant_type' => 'password',
            'username' => $this->contact->portal_name,
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
        );
        
        $reply = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply['reply']['access_token']);
        $this->assertNotEmpty($reply['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply['reply']['token_type']);


        $refreshToken = $reply['reply']['refresh_token'];

        
        $args = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => 'support_portal',
            'client_secret' => '',
        );
        
        $reply2 = $this->_restCall('oauth2/token',json_encode($args));
        $this->assertNotEmpty($reply2['reply']['access_token']);
        $this->assertNotEmpty($reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply2['reply']['access_token'],$reply2['reply']['refresh_token']);
        $this->assertNotEquals($reply['reply']['access_token'],$reply2['reply']['access_token']);
        $this->assertNotEquals($reply['reply']['refresh_token'],$reply2['reply']['refresh_token']);
        $this->assertEquals('bearer',$reply2['reply']['token_type']);
        
                                                          
    }

}
