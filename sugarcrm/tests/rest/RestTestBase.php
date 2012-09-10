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

class RestTestBase extends Sugar_PHPUnit_Framework_TestCase
{
    protected $authToken;
    protected $refreshToken;
    protected $_user;
    protected $consumerId = "sugar";

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
        $GLOBALS['db']->query("DELETE FROM oauth_tokens WHERE consumer LIKE 'UNIT%'");
    }

    protected function _restLogin($username = '', $password = '')
    {
        if ( empty($username) && empty($password) ) {
            $username = $this->_user->user_name;
            // Let's assume test users have a password the same as their username
            $password = $this->_user->user_name;
        }

        $args = array(
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
            'client_id' => $this->consumerId,
            'client_secret' => '',
        );
        
        // Prevent an infinite loop, put a fake authtoken in here.
        $this->authToken = 'LOGGING_IN';

        $reply = $this->_restCall('oauth2/token',json_encode($args));
        if ( empty($reply['reply']['access_token']) ) {
            throw new Exception("Rest authentication failed, message looked like: ".$reply['replyRaw']);
        }
        $this->authToken = $reply['reply']['access_token'];
        $this->refreshToken = $reply['reply']['refresh_token'];
    }

    protected function _restCall($urlPart,$postBody='',$httpAction='', $addedOpts = array(), $addedHeaders = array())
    {
        $urlBase = $GLOBALS['sugar_config']['site_url'].'/rest/v9/';
        if ( empty($this->authToken) ) {
            $this->_restLogin();
        }

        $ch = curl_init($urlBase.$urlPart);
        if (!empty($postBody)) {
            if (empty($httpAction)) {
                $httpAction = 'POST';
                curl_setopt($ch, CURLOPT_POST, 1); // This sets the POST array
                $requestMethodSet = true;
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
        } else {
            if (empty($httpAction)) {
                $httpAction = 'GET';
            }
        }
        
        if ( !empty($this->authToken) && $this->authToken != 'LOGGING_IN' ) {
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('oauth_token: '.$this->authToken));
            $addedHeaders[] = 'oauth_token: '.$this->authToken;
        }

        // Only set a custom request for not POST with a body
        // This affects the server and how it sets its superglobals
        if (empty($requestMethodSet)) {
            if ($httpAction == 'PUT' && empty($postBody) ) {
                curl_setopt($ch, CURLOPT_PUT, 1);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpAction);
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $addedHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        if (is_array($addedOpts) && !empty($addedOpts)) {
            // I know curl_setopt_array() exists, just wasn't sure if it was hurting stuff
            foreach ($addedOpts as $opt => $val) {
                curl_setopt($ch, $opt, $val);
            }
        }

        $httpReply = curl_exec($ch);
        $httpInfo = curl_getinfo($ch);
        $httpError = $httpReply === false ? curl_error($ch) : null;

        return array('info' => $httpInfo, 'reply' => json_decode($httpReply,true), 'replyRaw' => $httpReply, 'error' => $httpError);
    }

    public function testNothing()
    {
    }
}