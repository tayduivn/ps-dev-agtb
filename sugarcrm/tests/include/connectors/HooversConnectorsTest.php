<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/connectors/utils/ConnectorUtils.php');
require_once('modules/Connectors/controller.php');

class HooversConnectorsTest extends Sugar_PHPUnit_Framework_TestCase {

	var $original_modules_sources;
	var $original_searchdefs;
	var $qual_module;
	var $listArgs;
	var $company_id;
    
    function setUp() {
		$this->markTestSkipped("Marked as skipped until we can resolve Hoovers nusoapclient issues.");
  	    return;
  	    	
    	ConnectorFactory::$source_map = array();
		//Skip if we do not have an internet connection
		require('modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
		$url = $config['properties']['hoovers_wsdl'];
		$contents = @file_get_contents($url);
		if(empty($contents)) {
		   $this->markTestSkipped("Unable to retrieve Hoovers wsdl.  Skipping.");
		} 	
    	
		try {
		  $source_instance = ConnectorFactory::getInstance('ext_soap_hoovers');
		} catch(Exception $ex) {
		  $this->markTestSkipped("Unable to retrieve Hoovers wsdl.  Skipping.");
		}
		
    	//Enable mapping for Accounts
    	ConnectorUtils::getDisplayConfig();
    	require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->original_modules_sources = $modules_sources;    
    	    	
    	//Remove the current file and rebuild with default
    	unlink(CONNECTOR_DISPLAY_CONFIG_FILE);    	
    	$this->original_searchdefs = ConnectorUtils::getSearchDefs();
    	ConnectorUtils::getSearchDefs(true);
    	
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
    	$_REQUEST['mapping_values'] = 'ext_soap_hoovers:Accounts:addrcountry=billing_address_country,ext_soap_hoovers:Accounts:id=id,ext_soap_hoovers:Accounts:addrcity=billing_address_city,ext_soap_hoovers:Accounts:addrzip=billing_address_postalcode,ext_soap_hoovers:Accounts:recname=name,ext_soap_hoovers:Accounts:addrstateprov=billing_address_state';
    	$_REQUEST['mapping_sources'] = 'ext_soap_hoovers';
    	$_REQUEST['reset_to_default'] = '';    	
    	$controller->action_SaveModifyMapping();
    	//Test parameters
    	$this->qual_module = 'Accounts';
    	$this->company_id = '168338536';
    	$this->listArgs = array('name' => 'SugarCRM');
    	
    	//Pre-Test to make sure we can access service
        $source_instance = ConnectorFactory::getInstance('ext_soap_hoovers');
		$account = new Account();
    	try {
			$account = @$source_instance->fillBean(array('id'=>$this->company_id), $this->qual_module, $account);    	
    	} catch (Exception $ex) {
    		
    	}
    	
		if(empty($account->name)) {
           $this->markTestSkipped("Hoovers service may be unavailable at this time.");	
        }    	
    }
    
    function tearDown() {
   	    write_array_to_file('modules_sources', $this->original_modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE);
        write_array_to_file('searchdefs', $this->original_searchdefs, 'custom/modules/Connectors/metadata/searchdefs.php');
        ConnectorFactory::$source_map = array();
    }
    
    function test_hoovers_fillBean() {
    	$this->markTestSkipped('Mark test skipped.  Likely Key issue. Failing on 553/60/61.');
    	$source_instance = ConnectorFactory::getInstance('ext_soap_hoovers');
    	$account = new Account();
    	$account = $source_instance->fillBean(array('id'=>$this->company_id), $this->qual_module, $account);
    	$this->assertEquals(preg_match('/^SugarCRM/i', $account->name), 1, "Assert that account name is like SugarCRM");
    }

    function test_hoovers_fillBeans() {
    	$this->markTestSkipped('Mark test skipped.  Likely Key issue. Failing on 553/60/61.');
    	$source_instance = ConnectorFactory::getInstance('ext_soap_hoovers');
    	$accounts = array();
    	$accounts = $source_instance->fillBeans($this->listArgs, $this->qual_module, $accounts);
        foreach($accounts as $count=>$account) {
    		$this->assertEquals(preg_match('/^SugarCRM/i', $account->name), 1, "Assert that a bean has been filled with account name like SugarCRM");
    		break;
    	}
    } 
    
}  
?>