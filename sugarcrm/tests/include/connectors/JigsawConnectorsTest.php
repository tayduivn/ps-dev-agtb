<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/connectors/utils/ConnectorUtils.php');
require_once('include/connectors/ConnectorsTestUtility.php');
require_once('modules/Connectors/controller.php');

class JigsawConnectorsTest extends Sugar_PHPUnit_Framework_TestCase {

	var $company_id;
	var $company_name;
	var $original_modules_sources;
	var $original_searchdefs;

    
    function setUp() {
		$this->markTestSkipped("Marked as skipped until we can resolve Hoovers nusoapclient issues.");
  	    return;
  	        	
		if(!file_exists('modules/Connectors/connectors/sources/ext/soap/jigsaw/config.php')) {
		    $this->markTestSkipped("Skipping... Jigsaw service is unavailable.");
		    return;
		}    	
    	
		//Skip if we do not have an internet connection
		require('modules/Connectors/connectors/sources/ext/soap/jigsaw/config.php');
		$url = $config['properties']['jigsaw_wsdl'];
		$contents = @file_get_contents($url);

		if(!file_exists('custom/modules/Connectors/connectors/ext/soap/jigsaw/jigsaw.php') || empty($contents)) {
		    $this->markTestSkipped("Skipping... Jigsaw service is unavailable.");
		    return;
		} else {    	
    	
    	ConnectorFactory::$source_map = array();  
    	//Enable mapping for Accounts
    	ConnectorUtils::getDisplayConfig();
    	require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->original_modules_sources = $modules_sources;    
    	    	
    	//Remove the current file and rebuild with default
    	unlink(CONNECTOR_DISPLAY_CONFIG_FILE);    	
    	$this->original_searchdefs = ConnectorUtils::getSearchDefs();
    	ConnectorUtils::getSearchDefs(true);
    	
	    	ConnectorFactory::$source_map = array();  
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
	    	$_REQUEST['display_values'] = 'ext_soap_jigsaw:Accounts';
	    	$_REQUEST['display_sources'] = 'ext_soap_jigsaw';
	    	$controller = new ConnectorsController();
	    	$controller->action_SaveModifyDisplay();
	
	    	//Create mapping entry for Accounts
	    	$_REQUEST['action'] = 'SaveModifyMapping';
	    	$_REQUEST['mapping_values'] = 'ext_soap_jigsaw:Accounts:city=billing_address_city,ext_soap_jigsaw:Accounts:country=billing_address_country,ext_soap_jigsaw:Accounts:employeeRange=employees,ext_soap_jigsaw:Accounts:industry1=industry,ext_soap_jigsaw:Accounts:name=name,ext_soap_jigsaw:Accounts:phone=phone_office,ext_soap_jigsaw:Accounts:revenueRange=annual_revenue,ext_soap_jigsaw:Accounts:sicCode=sic_code,ext_soap_jigsaw:Accounts:state=billing_address_state,ext_soap_jigsaw:Accounts:website=website,ext_soap_jigsaw:Accounts:id=id';
	    	$_REQUEST['mapping_sources'] = 'ext_soap_jigsaw'; 	
	    	$controller->action_SaveModifyMapping();
	    		
	    	$this->company_id = '29530';
	    	$this->company_name = 'SugarCRM';
		}
    }

    public function tearDown() {
   	    write_array_to_file('modules_sources', $this->original_modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE);
        write_array_to_file('searchdefs', $this->original_searchdefs, 'custom/modules/Connectors/metadata/searchdefs.php');
    
        if(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/jigsaw/config.php')) {
           require('custom/modules/Connectors/connectors/sources/ext/soap/jigsaw/config.php');
           if(empty($config['properties']['jigsaw_api_key'])) {
				$config = array (
				  'name' => 'Jigsaw&#169;',
				  'properties' => 
				  array (
				    'jigsaw_wsdl' => 'http://www.jigsaw.com/api/services/CompanyService?wsdl',
				    'jigsaw_api_key' => '10072008_LoyalShape3',
				    'range_end' => 20, //Maximum number of results
				  ),
				);   
				write_array_to_file('config', $config, 'custom/modules/Connectors/connectors/sources/ext/soap/jigsaw/config.php');           	
           }
        }   
        
        ConnectorFactory::$source_map = array();
    }
    
    function test_jigsaw_fillBean() {
    	$source_instance = ConnectorFactory::getInstance('ext_soap_jigsaw');
    	$source_instance->getSource()->loadMapping();
    	require_once('modules/Accounts/Account.php');
    	$account = new Account();
    	$account = $source_instance->fillBean(array('id'=>$this->company_id), 'Accounts', $account);
    	$this->assertTrue($account->name == 'SugarCRM Inc.');
    }
    
    function test_jigsaw_fillBeans() {
    	require_once('modules/Accounts/Account.php');
    	$source_instance = ConnectorFactory::getInstance('ext_soap_jigsaw');
    	$source_instance->getSource()->loadMapping();
    	$accounts = array();
    	$accounts = $source_instance->fillBeans(array('name' => $this->company_name), 'Accounts', $accounts);
        foreach($accounts as $count=>$account) {
    		$this->assertTrue($account->name == 'SugarCRM Inc.');
    		break;
    	}
    } 

    function test_jigsaw_fillBean2() {
    	$source_instance = ConnectorFactory::getInstance('ext_soap_jigsaw');
    	$source_instance->getSource()->loadMapping();
    	require_once('modules/Accounts/Account.php');
    	$account= new Account();
    	$account = $source_instance->fillBean(array('id'=>$this->company_id), 'Accounts', $account);
    	$this->assertTrue($account->name == 'SugarCRM Inc.');
    	if(!empty($account->employees)) {
    		$this->assertTrue(preg_match('/[\d]+[\s\-]+?[\d]+/', $account->employees));
    	}
    }
    
    function test_jigsaw_fillBeans2() {
    	require_once('modules/Accounts/Account.php');
    	$accounts = array();
    	$source_instance = ConnectorFactory::getInstance('ext_soap_jigsaw');
    	$source_instance->getSource()->loadMapping();
    	$accounts = $source_instance->fillBeans(array('name' => $this->company_name), 'Accounts', $accounts);
        foreach($accounts as $count=>$account) {
    		$this->assertTrue($account->name == 'SugarCRM Inc.');
    		break;
    	}
    }     
    
}  
?>