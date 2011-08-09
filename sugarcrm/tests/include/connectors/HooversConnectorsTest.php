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

require_once('include/connectors/ConnectorsTestCase.php');
require_once('tests/include/connectors/HooversHelper.php');

class HooversConnectorsTest extends Sugar_Connectors_TestCase
{
	var $qual_module;
	var $listArgs;
	var $company_id;
	protected static $mock;

	function setUp()
	{
        parent::setUp();
    	ConnectorFactory::$source_map = array();
		//Skip if we do not have an internet connection

    	if(empty(self::$mock)) {
    		self::$mock = $this->getMockFromWsdl(
          		dirname(__FILE__).'/hooversAPI.wsdl', 'HooversAPIMock'
        	);
    	}

    	//Enable the Hoovers Connector
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$_REQUEST['modify'] = true;
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['source1'] = 'ext_soap_hoovers';
    	$_REQUEST['display_values'] = 'ext_soap_hoovers:Accounts';
    	$_REQUEST['display_sources'] = 'ext_soap_hoovers';
    	$_REQUEST['reset_to_default'] = '';
    	$controller = new ConnectorsController();
    	$controller->action_SaveModifyDisplay();

    	//Create mapping entry for Accounts
    	$_REQUEST['action'] = 'SaveModifyMapping';
    	$_REQUEST['source1'] = 'ext_soap_hoovers';
    	$_REQUEST['mapping_values'] = 'ext_soap_hoovers:Accounts:country=billing_address_country,ext_soap_hoovers:Accounts:id=id,ext_soap_hoovers:Accounts:city=billing_address_city,ext_soap_hoovers:Accounts:addrzip=billing_address_postalcode,ext_soap_hoovers:Accounts:recname=name,ext_soap_hoovers:Accounts:stateorprovince=billing_address_state';
    	$_REQUEST['mapping_sources'] = 'ext_soap_hoovers';
    	$_REQUEST['reset_to_default'] = '';
    	$controller->action_SaveModifyMapping();
    	//Test parameters
    	$this->qual_module = 'Accounts';
    	$this->company_id = '2205698';
    	$this->listArgs = array('name' => 'Gannett');
    	$this->mock = new HooversConnectorsMockClient(self::$mock);
    }

    function tearDown() {
        parent::tearDown();
        ConnectorFactory::$source_map = array();
    }

    private function getResultData($filename)
    {
    	$result = '';
    	require(dirname(__FILE__)."/$filename");
    	return $result;
    }

    function test_hoovers_fillBean() {
    	$source_instance = ConnectorFactory::getInstance('ext_soap_hoovers');
//BEGIN SUGARCRM flav!=int ONLY
    	$source_instance->getSource()->setClient($this->mock);
    	$this->mock->expects($this->once())
    		->method('GetCompanyDetail')
    		->will($this->returnValue($this->getResultData('gannett.php')));
//END SUGARCRM flav!=int ONLY
    	$account = new Account();
    	$account = $source_instance->fillBean(array('id'=>$this->company_id), $this->qual_module, $account);
    	$this->assertRegExp('/^Gannett/i', $account->name, "Assert that account name is like Gannett");
}

    function test_hoovers_fillBeans() {
    	$source_instance = ConnectorFactory::getInstance('ext_soap_hoovers');
//BEGIN SUGARCRM flav!=int ONLY
    	$source_instance->getSource()->setClient($this->mock);
    	$this->mock->expects($this->once())
    		->method('AdvancedCompanySearch')
    		->will($this->returnValue($this->getResultData('gannett_search.php')));
//END SUGARCRM flav!=int ONLY
    	$accounts = array();
    	$accounts = $source_instance->fillBeans($this->listArgs, $this->qual_module, $accounts);
    	if(empty($accounts))
    	{
    	   $this->markTestSkipped('No accounts returned.  API Service may be down.  Skip test');
    	   return;
    	}
    	
        foreach($accounts as $count=>$account) {
    		$this->assertRegExp('/Gannett/i', $account->name, "Assert that account name is like Gannett");
    		break;
    	}
    }

}
?>