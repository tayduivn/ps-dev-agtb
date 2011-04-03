<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/connectors/ConnectorsTestCase.php');

class HooversConnectorsTest extends Sugar_Connectors_TestCase
{
	var $qual_module;
	var $listArgs;
	var $company_id;

    function setUp() {
        parent::setUp();
    	ConnectorFactory::$source_map = array();
		//Skip if we do not have an internet connection
		require('modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
		$url = $config['properties']['hoovers_wsdl'];
		$contents = @file_get_contents($url);
		if(empty($contents)) {
		   $this->markTestSkipped("Unable to retrieve Hoovers wsdl.  Skipping.");
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
    }

    function tearDown() {
        parent::tearDown();
        ConnectorFactory::$source_map = array();
    }

    function test_hoovers_fillBean() {
    	$source_instance = ConnectorFactory::getInstance('ext_soap_hoovers');
    	$account = new Account();
    	$account = $source_instance->fillBean(array('id'=>$this->company_id), $this->qual_module, $account);
    	$this->assertEquals(preg_match('/^Gannett/i', $account->name), 1, "Assert that account name is like Gannett");
    }

    function test_hoovers_fillBeans() {
    	$source_instance = ConnectorFactory::getInstance('ext_soap_hoovers');
    	$accounts = array();
    	$accounts = $source_instance->fillBeans($this->listArgs, $this->qual_module, $accounts);
        foreach($accounts as $count=>$account) {
    		$this->assertEquals(preg_match('/^Gannett/i', $account->name), 1, "Assert that a bean has been filled with account name like Gannett");
    		break;
    	}
    }

}
?>