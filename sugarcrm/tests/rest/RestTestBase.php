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
        // call a commit for transactional dbs
        $GLOBALS['db']->commit();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query("DELETE FROM oauth_consumer WHERE id LIKE 'UNIT%'");
        $GLOBALS['db']->query("DELETE FROM oauth_tokens WHERE consumer LIKE 'UNIT%'");
        $GLOBALS['db']->commit();
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
        $urlBase = $GLOBALS['sugar_config']['site_url'].'/api/rest.php/v6/';
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
    
    /**
     * Use for FileApi call tests using a PUT method. This varies enough from 
     * the _restCall method to warrant it's own setup. It is also added to the
     * base class because more than one unit test is using it now.
     * 
     * @param string $urlPart The endpoint to hit in the api
     * @param array  $args Arguments to pass to this call (filename and type)
     * @param bool   $passInQueryString Whether to add the filename to the querystring
     * @return array
     */
    protected function _restCallFilePut($urlPart, $args, $passInQueryString = true) {
        // Set this to capture our own errors, which is needed in case of non-200
        // response codes from the file_get_contents call
        PHPUnit_Framework_Error_Warning::$enabled = FALSE;
        
        // Auth check early to prevent work when not needed
        if ( empty($this->authToken) ) {
            $this->_restLogin();
        }
        
        $urlBase = $GLOBALS['sugar_config']['site_url'].'/api/rest.php/v6/';
        $filename = basename($args['filename']);
        $url = $urlBase . $urlPart;
        if ($passInQueryString) {
            $conn = strpos('?', $url) === false ? '?' : '&';
            $url .= $conn . 'filename=' . urlencode($filename);
        }

        $filedata = file_get_contents($args['filename']);

        $auth = "oauth_token: $this->authToken\r\n";
        $options = array(
            'http' => array(
                'method' => 'PUT',
                'header' => "{$auth}Content-Type: $args[type]\r\nfilename: $filename\r\n",
                'content' => $filedata,
            ),
        );

        $context = stream_context_create($options);
        
        // Because non-200 HTTP responses causes PHP warnings and because PHPUnit
        // throws exceptions for those warnings, we use both error suppression
        // and turning off PHPUnit error warnings to allow the script to continue
        // to run when encountering a "error". 
        $response = @file_get_contents($url, false, $context);
        if (empty($response) && !empty($http_response_header)) {
            // There was a response that was NOT a 200. These are mapped to API
            // exception codes where possible
            $responses = array(
                400 => array('label' => 'unknown_exception', 'description' => "An unknown exception happened."),
                401 => array('label' => 'need_login', 'description' => "The user needs to be logged in to perform this action"),
                403 => array('label' => 'not_authorized', 'description' => "This action is not authorized for the current user."),
                404 => array('label' => 'no_method_or_not_found', 'description' => "Could not find a method or handler for this path."),
                412 => array('label' => 'missing_or_invalid_parameter', 'description' => "A required parameter for this request is missing or invalid."),
                413 => array('label' => 'request_too_large', 'description' => "The request is too large to process."),
                500 => array('label' => 'fatal_error', 'description' => "A fatal error happened."),
            );
            
            // Set a reasonable default response code
            $code = 400;
            
            // See if we can get the actual HTTP response code
            foreach ($http_response_header as $header) {
                if (substr($header, 0, 5) == 'HTTP/') {
                    preg_match('#HTTP/\d\.\d\s+(\d+)\s+.*#', $header, $m);
                    if (isset($m[1])) {
                        $code = intval($m[1]);
                        break;
                    }
                }
            }
            
            // Fallback to the default if we got something we didn't expect
            if (!isset($responses[$code])) {
                $code = 400;
            }
            
            // Mock an exception response from the API
            $reply = array('error' => $responses[$code]['label'], 'error_description' => $responses[$code]['description']);
        } else {
            $reply = json_decode($response, true);
        }
        
        // Set back the error handler setting
        PHPUnit_Framework_Error_Warning::$enabled = TRUE;

        return array('info' => array(), 'reply' => $reply, 'replyRaw' => $response, 'error' => null);
    }
    
    public function testNothing()
    {

    }
}