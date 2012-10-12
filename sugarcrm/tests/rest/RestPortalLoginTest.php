<?php
//FILE SUGARCRM flav=ent ONLY
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
require_once('tests/rest/RestTestPortalBase.php');

class RestPortalLoginTest extends RestTestPortalBase
{
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
    public function testRestLoginSupportPortal()
    {
        $args = array(
            'grant_type' => 'password',
            'username' => $this->contact->portal_name,
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
            'platform' => 'portal',
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
            'platform' => 'portal',
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
}