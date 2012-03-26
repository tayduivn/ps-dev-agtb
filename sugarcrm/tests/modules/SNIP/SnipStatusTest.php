<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once ('modules/SNIP/SugarSNIP.php');

/*
 * Tests the getStatus() function of SugarSNIP to ensure that the return value is correct for various server responses.
 */
class SnipStatusTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $snip;

    public function setUp(){
    	$this->snip = SugarSNIP::getInstance();
    }

    public function testStatusPurchased() { $this->statusTest(json_encode(array('result'=>'ok','status'=>'success')),'purchased'); }

    public function testStatusNotPurchased1() { $this->statusTest(json_encode(array('result'=>'instance not found')),'notpurchased'); }
    public function testStatusNotPurchased2() { $this->statusTest(json_encode(array('result'=>'instance not found','status'=>'fasdfkuaseyrkajsdfh udd')),'notpurchased'); }

    public function testStatusDown1() { $this->statusTest(json_encode(array('result'=>'asdofi7aso8fdus','status'=>'dafso8dfuds')),'down'); }
    public function testStatusDown2() { $this->statusTest(json_encode(array('result'=>'asdofi7aso8fdus')),'down'); }
    public function testStatusDown3() { $this->statusTest('This is not valid','down'); }
    public function testStatusDown4() { $this->statusTest('','down'); }
    public function testStatusDown5() { $this->statusTest(NULL,'down'); }

    public function testStatusDownShowEnableScreen() { $this->statusTest(json_encode(array('result'=>'asdofi7aso8fdus','status'=>'dafso8dfuds')),'notpurchased',null,false); }

    public function testStatusPurchasedError1() { $this->statusTest(json_encode(array('result'=>'ok')),'purchased_error',null); }
    public function testStatusPurchasedError2() { $this->statusTest(json_encode(array('result'=>'ok', 'status'=>'this is a test error status')),'purchased_error','this is a test error status'); }



    protected function statusTest($serverResponse,$expectedStatus,$expectedMessage=null,$snipEmailExists=true)
    {
    	//give snip our mock client
    	$this->snip->setClient(new MockClient($this->snip,$this,$serverResponse));
        $oldemail = $this->snip->getSnipEmail();
        if ($snipEmailExists){
            $this->snip->setSnipEmail("snip-test-182391820@sugarcrm.com");
        }else{
            $this->snip->setSnipEmail("");
        }

    	//call getStatus on snip
    	$status = $this->snip->getStatus();

        $this->snip->setSnipEmail($oldemail);

    	//check to make sure the status is an array with the correct values
    	$this->assertTrue(is_array($status),"getStatus() should always return an associative array of the form array('status'=>string,'message'=>string|null). But it did not return an array.");
    	$this->assertEquals($expectedStatus,$status['status'],"Expected status: '$expectedStatus'. Returned status: '{$status['status']}'");
    	$this->assertEquals($expectedMessage,$status['message'],"Expected message: ".(is_null($expectedMessage)?"null":"'$expectedMessage'").". Returned message: '{$status['message']}'");
    }
}

class MockClient extends SugarHttpClient
{
	private $snip;
	private $hasfailed=false;
	private $testcase;
	private $status;

	/**
	* Construct the mock snip client. Example:
	* $mc = new MockClient(SugarSNIP::getInstance(),new Sugar_PHPUnit_Framework_TestCase,json_encode(array('result'=>'ok','status'=>'success')));
	*  - this example would cause the mock client to return the {'result' : 'ok', 'status' : 'success'}, which is the result returned when the Sugar instance has a SNIP license.
	* @param SugarSNIP $snip The SugarSNIP object
	* @param Sugar_PHPUnit_Framework_TestCase $testcase The testcase that is currently running (used to trigger exceptions/assertions).
	* @param string $status The status message that should be returned from the mock server (should be a string that is a json-encoded object).
	*/
	public function __construct($snip,$testcase,$status)
	{
		$this->snip=$snip;
		$this->testcase = $testcase;
		$this->status = $status;
	}

	//overrides callRest to provide a status message based on the prameter
	public function callRest($url, $postArgs)
    {
    	if (preg_match('`^'.$this->snip->getSnipURL().'status/?`',$url))
    	{
    		return $this->status;
    	}
    	$this->testcase->throwException(new Exception("The MockClient can only handle callRest calls that query the status."));
    }
}
