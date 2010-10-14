<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/formatters/FormatterFactory.php');
require_once('include/connectors/utils/ConnectorUtils.php');
require_once('modules/Connectors/controller.php');
require_once('include/MVC/Controller/SugarController.php');
    	
class ConnectorsFormatterTest extends Sugar_PHPUnit_Framework_TestCase 
{	
	protected $parentFieldArray;
	protected $vardef;
	protected $displayParams;
	protected $tabindex;
	protected $ss;
	protected $original_modules_sources;
	protected $original_searchdefs;
    
    public function setUp() 
    {
    	$this->markTestSkipped("Marked as skipped until we can resolve Hoovers nusoapclient issues.");
  	    return;

		//Store original files
    	require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->original_modules_sources = $modules_sources;
    	$this->original_searchdefs = ConnectorUtils::getSearchDefs();        	  	
    	
   		if(file_exists('custom/modules/Connectors/connectors/sources/ext/rest/linkedin/linkedin.php')) {
   		   copy_recursive('custom/modules/Connectors/connectors/sources/ext/rest/linkedin', 'custom/modules/Connectors/backup/connectors/sources/ext/rest/linkedin_backup');
    	   ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/linkedin');
   		} 

   		if(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers.php')) {
    	   copy_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers', 'custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers_backup');
   		   ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/soap/hoovers');
   		} 

    	
    	//Setup the neccessary Smarty configurations
    	$this->parentFieldArray = 'fields';
    	require_once('include/SugarObjects/VardefManager.php');
        VardefManager::loadVardef('Accounts', 'Account');
        require_once('cache/modules/Accounts/Accountvardefs.php');
        $this->vardef = $GLOBALS['dictionary']['Account']['fields']['name'];
    	$this->displayParams = array('sources'=>array('ext_rest_linkedin'));
    	$this->tabindex = 0;
    	require_once('include/Sugar_Smarty.php');
    	$this->ss = new Sugar_Smarty();
    	$this->ss->assign('parentFieldArray', $this->parentFieldArray);
    	$this->ss->assign('vardef', $this->vardef);
    	$this->ss->assign('displayParams', $this->displayParams);
    	
    	//Setup the mapping to guarantee that we have hover fields for the Accounts module
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$_REQUEST['modify'] = true;
    	$_REQUEST['action'] = 'SaveModifyMapping';
		$_REQUEST['mapping_values'] = 'ext_soap_hoovers:Accounts:addrcountry=billing_address_country,ext_soap_hoovers:Accounts:id=id,ext_soap_hoovers:Accounts:addrcity=billing_address_city,ext_soap_hoovers:Accounts:addrzip=billing_address_postalcode,ext_soap_hoovers:Accounts:recname=name,ext_soap_hoovers:Accounts:addrstateprov=billing_address_state,ext_rest_linkedin:Accounts:name=name';
    	$_REQUEST['mapping_sources'] = 'ext_soap_hoovers,ext_rest_linkedin';
    	
    	$controller = new ConnectorsController();
    	$controller->action_SaveModifyMapping();  

    	FormatterFactory::$formatter_map = array();
    	ConnectorFactory::$source_map = array();
    }
    
    public function tearDown() 
    {   		
        if(file_exists('custom/modules/Connectors/connectors/sources/ext/rest/linkedin_backup/linkedin.php')) {
    	   copy_recursive('custom/modules/Connectors/backup/connectors/sources/ext/rest/linkedin_backup', 'custom/modules/Connectors/connectors/sources/ext/rest/linkedin');
    	   ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/linkedin_backup');
        }  

        if(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers_backup/hoovers.php')) {
    	   copy_recursive('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers_backup', 'custom/modules/Connectors/connectors/sources/ext/rest/hoovers');
    	   ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers_backup');
        }  

    	write_array_to_file('modules_sources', $this->original_modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE);
        write_array_to_file('searchdefs', $this->original_searchdefs, 'custom/modules/Connectors/metadata/searchdefs.php');
    }
    
    public function testHoverLinkForAccounts() 
    { 	
    	$this->markTestSkipped("Skipping... cannot run this test in framework.");
    	
    	$enabled_sources = ConnectorUtils::getModuleConnectors('Accounts');
    	$hover_sources = array();
    	$displayParams = array();
    	$displayParams['module'] = 'Accounts';
    	$displayParams['enableConnectors'] = true;
    	
    	foreach($enabled_sources as $id=>$mapping) {
    		$source = SourceFactory::getSource($id);
    		if($source->isEnabledInHover()) {
    		   $parts = preg_split('/_/', $id);
    		   $hover_sources[$parts[count($parts) - 1]] = $id;
    		   $displayParams['connectors'][] = $id;
    		}
    	}
    	
    	if(!empty($hover_sources)) {
    	   $output = ConnectorUtils::getConnectorButtonScript($displayParams, $this->ss);
    	   preg_match_all('/<div[^\>]*?>/', $output, $matches);
    	   $this->assertTrue(isset($matches[0]));
    	}
    }
    
    
    public function testHoverLinkForLinkedinOnly() 
    {
        $this->markTestSkipped("Skipping... cannot run this test in framework.");
        
    	require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$modules_sources['Accounts'] = array('ext_rest_linkedin'=>'ext_rest_linkedin');
    	$displayParams = array();
    	$displayParams['module'] = 'Accounts';
    	$displayParams['enableConnectors'] = true;
    	$displayParams['connectors'][] = 'ext_rest_linkedin';    	
    	
    	if(write_array_to_file('modules_sources', $modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE)) {  		
           $output = ConnectorUtils::getConnectorButtonScript($displayParams, $this->ss);
           preg_match_all('/<div.*?id=[\'\"](.*?)[\'\"][^\>]*?>/', $output, $matches);
    	   $this->assertTrue(!empty($matches[0][0]) && preg_match('/linkedin/', $matches[0][0]));
        }	    	
    }
    
    public function testRemoveHoverLinksInViewdefs() 
    {
    	$module = 'Accounts';
    	
    	if(file_exists("custom/modules/{$module}/metadata/detailviewdefs.php")) {
    	  require("custom/modules/{$module}/metadata/detailviewdefs.php");
    	} else if(file_exists("modules/{$module}/metadata/detailviewdefs.php")) {
    	  require("modules/{$module}/metadata/detailviewdefs.php");
    	}
    	
    	$this->assertTrue(!empty($viewdefs));
        
    	//Remove hover fields
    	ConnectorUtils::removeHoverField($viewdefs, $module);
    	$foundHover = false;
		foreach($viewdefs[$module]['DetailView']['panels'] as $panel_id=>$panel) {
	  	   foreach($panel as $row_id=>$row) { 
	  	  	  foreach($row as $field_id=>$field) {
		  	  	  if(is_array($field) && !empty($field['displayParams']['enableConnectors'])) {
                     $foundHover = true;
		  	  	  }
	  	  	  } //foreach
	  	   } //foreach
		} //foreach
		
		//There should have been no hover fields found
		$this->assertTrue(!$foundHover);	  	
    }
    
    public function testModifyDisplayChanges() 
    {
    	$this->markTestSkipped("Skipping... cannot run this test in framework.");
		
    	$module = 'Accounts';
    	    	
    	//Now call the code that will add the mapping fields
    	$_REQUEST['display_values'] = "ext_soap_hoovers:Accounts,ext_rest_linkedin:Accounts";
    	$_REQUEST['display_sources'] = "ext_soap_hoovers,ext_rest_linkedin";
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	
       	$controller = new ConnectorsController();
    	$controller->action_SaveModifyDisplay();

		if(file_exists("custom/modules/{$module}/metadata/detailviewdefs.php")) {
    	  require("custom/modules/{$module}/metadata/detailviewdefs.php");
		  foreach($viewdefs[$module]['DetailView']['panels'] as $panel_id=>$panel) {
		  	   foreach($panel as $row_id=>$row) { 
		  	  	  foreach($row as $field_id=>$field) {
		  	  	  	  $name = is_array($field) ? $field['name'] : $field;
		  	  	  	  switch(strtolower($name)) {
		  	  	  	  	case "account_name": 
							 $this->assertTrue(!empty($field['displayParams']['enableConnectors']));
							 $this->assertTrue($field['displayParams']['connectors'][0] == 'ext_rest_linkedin');		  	  	  	  		
		  	  	  	  	break;
		  	  	  	  }
		  	  	  } //foreach
		  	   } //foreach
		  } //foreach  

		  $this->test_remove_hover_links_in_viewdefs(); //Call remove again b/c we know for sure there are now fields
    	} else {
    	  $this->assertTrue(false); //Failed because we couldn't create the custom file   	
    	}
    }
}  
?>