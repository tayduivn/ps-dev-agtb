<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/utils/ConnectorUtils.php');

class ConnectorsModifySearchTest extends Sugar_PHPUnit_Framework_TestCase {

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
    	ConnectorUtils::getSearchDefs(true);
    }
    
    function tearDown() {
    	write_array_to_file('modules_sources', $this->original_modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE);
        write_array_to_file('searchdefs', $this->original_searchdefs, 'custom/modules/Connectors/metadata/searchdefs.php');
    }
    

    /*
     * test_hoovers_search
     * This test defines the mapping for connectors ( hoovers).
     * 
     */
    function test_hoovers_search() {
    	require_once('modules/Connectors/controller.php');
    	require_once('include/MVC/Controller/SugarController.php');
    	
    	//First enable all the connectors
    	$_REQUEST['reset_to_default'] = '';
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$_REQUEST['source1'] = 'ext_soap_hoovers';
        $_REQUEST['search_values'] = 'ext_soap_hoovers:Leads:recname,ext_soap_hoovers:Leads:addrcity,ext_soap_hoovers:Accounts:recname,ext_soap_hoovers:Accounts:addrcity,ext_soap_hoovers:Contacts:recname,ext_soap_hoovers:Contacts:addrcity';
        $_REQUEST['search_sources'] = 'ext_soap_hoovers';
    	$_REQUEST['action'] = 'SaveModifySearch';
    	
    	$controller = new ConnectorsController();
    	$controller->action_SaveModifySearch();
	    $searchdefs = ConnectorUtils::getSearchDefs();

	    $this->assertTrue(count($searchdefs['ext_soap_hoovers']) == 3);

	    foreach($searchdefs['ext_soap_hoovers'] as $module=>$mapping) {
	    	$this->assertTrue($mapping[0] == 'recname' && $mapping[1] == 'addrcity');
	    }
 
    }      
    
    
  
}  
?>