<?php
//FILE SUGARCRM flav=pro ONLY
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
 
require_once('modules/Teams/Team.php');
require_once('include/nusoap/nusoap.php');

class RestApiTest extends Sugar_PHPUnit_Framework_TestCase
{
	var $_user = null;
	var $_soapClient = null;

    public function setUp()
    {
		$this->_user = SugarTestUserUtilities::createAnonymousUser();
		$GLOBALS['current_user'] = $this->_user;
		if(!function_exists('curl_init')) {
           $this->markTestSkipped("Skipping test because there is no CURL support");
		}
    }

    public function tearDown()
    {
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function test_relationship_calls() {
    	$soap_url = $GLOBALS['sugar_config']['site_url'].'/service/v2/rest.php';
    	//echo $soap_url;
		$result = $this->doRESTCALL($soap_url, 'login', array('user_auth'=>array('user_name'=>$this->_user->user_name,'password'=>$this->_user->user_hash, 'version'=>'.01'), 'application_name'=>'SoapTest', 'name_value_list' => array(array('name' => 'notifyonsave', 'value' => 'false'))));
        $resp='';
		if (!empty($result) and is_array($result) and isset($result['name_value_list']['user_id']['value'])) {
			$resp=$result['name_value_list']['user_id']['value'];
        }
        $this->assertEquals($resp,$this->_user->id,'Unable to find the user id');

    }

	private function doRESTCALL($url, $method, $data) {
		$ch = curl_init();
		$headers = (function_exists('getallheaders'))?getallheaders(): array();
		$_headers  = array();
		foreach($headers as $k=>$v){
			$_headers[strtolower($k)] = $v;
		}
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0  );
		$post_data = 'method=' . $method . '&input_type=JSON&response_type=JSON';
	  	$json = getJSONobj();
	  	$jsonEncodedData = $json->encode($data, false);
		$post_data = $post_data . "&rest_data=" . $jsonEncodedData;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$result = curl_exec($ch);
		curl_close($ch);
		$result = explode("\r\n\r\n", $result, 2);
//		print_r($result[1]);
		$response_data = $json->decode($result[1]);
		return $response_data;
	}

}

?>
