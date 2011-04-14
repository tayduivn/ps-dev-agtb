<?php
//FILE SUGARCRM flav=pro ONLY
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
		ob_start();
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
		ob_end_flush();
		return $response_data;
	}    
    
}

?>
