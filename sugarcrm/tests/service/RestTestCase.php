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

//Uses the latest version of SugarWebServiceUtil implementation
require_once('service/v4/SugarWebServiceUtilv4.php');

/**
 * RestTestCase
 *
 * Abstract class to separate out the common setup, tearDown and utility methods for testing REST API calls
 * @author Collin Lee
 * 
 */
abstract class RestTestCase extends Sugar_PHPUnit_Framework_TestCase
{

	protected $_soapClient = null;
    protected $_sessionId;
    protected $_lastRawResponse;

    public function setUp()
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");

        //Reload langauge strings
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Accounts');
    }

    /**
     * tearDown
     *
     * This function helps clean up global variables that may have been set and removes the anonymous user created
     */
    public function tearDown()
    {
	    if(isset($GLOBALS['listViewDefs'])) unset($GLOBALS['listViewDefs']);
	    if(isset($GLOBALS['viewdefs'])) unset($GLOBALS['viewdefs']);
        unset($GLOBALS['app_list_strings']);
	    unset($GLOBALS['app_strings']);
	    unset($GLOBALS['mod_strings']);
        SugarTestHelper::tearDown();
    }

    /**
     * _makeRestCall
     *
     * This function helps wrap the REST call using the CURL libraries
     *
     * @param $method String name of the method to call
     * @param $parameters Mixed array of arguments depending on the method call
     *
     * @return mixed JSON decoded response made from REST call
     */
    protected function _makeRESTCall($method,$parameters)
    {
        // specify the REST web service to interact with
        $url = $GLOBALS['sugar_config']['site_url'].'/service/v4/rest.php';
        // Open a curl session for making the call
        $curl = curl_init($url);
        // set URL and other appropriate options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        // build the request URL
        $json = json_encode($parameters);
        $postArgs = "method=$method&input_type=JSON&response_type=JSON&rest_data=$json";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
        // Make the REST call, returning the result
        $response = curl_exec($curl);
        // Close the connection
        curl_close($curl);

        $this->_lastRawResponse = $response;

        // Convert the result from JSON format to a PHP array
        return json_decode($response,true);
    }

    protected function _returnLastRawResponse()
    {
        return "Error in web services call. Response was: {$this->_lastRawResponse}";
    }


    /**
     * _login
     *
     * This function helps make the login call
     *
     * @param $user The SugarCRM User bean instance to login with
     * @return mixed The REST response from the login operation
     */
    protected function _login($user)
    {
        $GLOBALS['db']->commit(); // Making sure we commit any changes before logging in
        return $this->_makeRESTCall('login',
            array(
                'user_auth' =>
                    array(
                        'user_name' => $user->user_name,
                        'password' => $user->user_hash,
                        'version' => '.01',
                        ),
                'application_name' => 'mobile',
                'name_value_list' => array(),
                )
            );
    }

}
