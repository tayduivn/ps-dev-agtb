<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/connectors/ConnectorsTestCase.php');

class ConnectorsPropertiesTest extends Sugar_Connectors_TestCase
{
	function setUp() {
        parent::setUp();
    	if(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php')) {
    	   mkdir_recursive('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers');
    	   copy_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers', 'custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers');
    	} else {
    	   mkdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
    	}
    }

    function tearDown() {
        parent::tearDown();
        if(file_exists('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers')) {
    	   copy_recursive('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers', 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
           ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/connectors/sources/ext/soap/hoovers');
        }

        if(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php')) {
           require('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
           if(empty($config['properties']['hoovers_api_key'])) {
				$config = array (
				  'name' => 'Hoovers&#169;',
				  'properties' =>
				  array (
				    'hoovers_endpoint' => 'http://hapi.hoovers.com/HooversAPI-33',
    				'hoovers_wsdl' => 'http://hapi.hoovers.com/HooversAPI-33/hooversAPI/hooversAPI.wsdl',
				  ),
				);
				write_array_to_file('config', $config, 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
           }
        }

    }

    function test_get_data_button_without_api_key() {

		$config = array (
		  'name' => 'Hoovers&#169;',
		  'properties' =>
		  array (
		    'hoovers_endpoint' => 'http://hapi.hoovers.com/HooversAPI-33',
   			'hoovers_wsdl' => 'http://hapi.hoovers.com/HooversAPI-33/hooversAPI/hooversAPI.wsdl',
		    'hoovers_api_key' => '',
		  ),
		);

		write_array_to_file('config', $config, "custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php");

        require_once('modules/Connectors/controller.php');
    	require_once('include/MVC/Controller/SugarController.php');
    	$controller = new ConnectorsController();
    	$_REQUEST['display_values'] = "ext_soap_hoovers:Leads";
    	$_REQUEST['display_sources'] =  'ext_soap_hoovers,ext_soap_hoovers,ext_rest_linkedin';
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$controller->action_SaveModifyDisplay();

    	require('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
    	require('custom/modules/Leads/metadata/detailviewdefs.php');
    	$hasConnectorButton = false;
    	//_pp($viewdefs['Leads']['DetailView']['templateMeta']['form']['buttons']);
    	foreach($viewdefs['Leads']['DetailView']['templateMeta']['form']['buttons'] as $button) {
    	        if(!is_array($button) && $button == 'CONNECTOR') {
                   $hasConnectorButton = true;
                }
    	}
    	$this->assertTrue($hasConnectorButton);
    }

    function test_get_data_button_with_api_key() {

		$config = array (
		  'name' => 'Hoovers&#169;',
		  'properties' =>
		  array (
   			'hoovers_endpoint' => 'http://hapi.hoovers.com/HooversAPI-33',
    		'hoovers_wsdl' => 'http://hapi.hoovers.com/HooversAPI-33/hooversAPI/hooversAPI.wsdl',
		    'hoovers_api_key' => '',
		  ),
		);

		write_array_to_file('config', $config, "custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php");

        require_once('modules/Connectors/controller.php');
    	require_once('include/MVC/Controller/SugarController.php');
    	$controller = new ConnectorsController();
    	$_REQUEST['display_values'] = "ext_soap_hoovers:Leads";
    	$_REQUEST['display_sources'] =  'ext_soap_hoovers,ext_soap_hoovers,ext_rest_linkedin';
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;
    	$controller->action_SaveModifyDisplay();

    	require('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
    	require('custom/modules/Leads/metadata/detailviewdefs.php');
    	$hasConnectorButton = false;
    	foreach($viewdefs['Leads']['DetailView']['templateMeta']['form']['buttons'] as $button) {
    	        if(!is_array($button) && $button == 'CONNECTOR') {
                   $hasConnectorButton = true;
                }
    	}
    	$this->assertTrue($hasConnectorButton);
    }

}
?>