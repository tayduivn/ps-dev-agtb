<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/utils/ConnectorUtils.php');

class ConnectorsEnableDisableTest extends Sugar_PHPUnit_Framework_TestCase {

    var $original_modules_sources;
	var $original_searchdefs;
	
    function setUp() {
    	$this->markTestSkipped("Marked as skipped until we can resolve Hoovers nusoapclient issues.");
  	    return;
  	    
        if(!file_exists(CONNECTOR_DISPLAY_CONFIG_FILE)) {
    	   ConnectorUtils::getDisplayConfig();
    	}
    	require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->original_modules_sources = $modules_sources;
    	
    	//Remove the current file and rebuild with default
    	unlink(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->original_searchdefs = ConnectorUtils::getSearchDefs();
    }
    
    function tearDown() {
    	write_array_to_file('modules_sources', $this->original_modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE);
        write_array_to_file('searchdefs', $this->original_searchdefs, 'custom/modules/Connectors/metadata/searchdefs.php');
    }
    
    function test_enable_all() {
    	require_once('modules/Connectors/controller.php');
    	require_once('include/MVC/Controller/SugarController.php');
    	
    	$_REQUEST['display_values'] = "ext_soap_hoovers:Accounts,ext_soap_hoovers:Contacts,ext_soap_hoovers:Leads,ext_rest_linkedin:Accounts,ext_rest_linkedin:Contacts,ext_rest_linkedin:Leads";
    	$_REQUEST['display_sources'] = 'ext_soap_hoovers,ext_rest_linkedin';
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	
    	$controller = new ConnectorsController();
    	$controller->action_SaveModifyDisplay();
    	if(file_exists(CONNECTOR_DISPLAY_CONFIG_FILE)) {
    	   require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	   $this->assertTrue(count($modules_sources) == 3);
    	}
    }
    
    function test_disable_all() {
        require_once('modules/Connectors/controller.php');
    	require_once('include/MVC/Controller/SugarController.php');
    	$controller = new ConnectorsController();
    	
    	$_REQUEST['display_values'] = '';
    	$_REQUEST['display_sources'] = 'ext_soap_hoovers,ext_rest_linkedin';
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	
    	$controller->action_SaveModifyDisplay();
	
    	if(file_exists(CONNECTOR_DISPLAY_CONFIG_FILE)) {
    	   require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	   $this->assertTrue(empty($modules_sources['ext_soap_hoovers']));
    	   $this->assertTrue(empty($modules_sources['ext_rest_linkedin']));  	   
    	}    	
    }


}  
?>