<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/connectors/utils/ConnectorUtils.php');
require_once('include/connectors/ConnectorsTestUtility.php');

class ConnectorsWebexPropertiesTest extends Sugar_PHPUnit_Framework_TestCase {

	function setUp() {
        if(!file_exists(CONNECTOR_DISPLAY_CONFIG_FILE)) {
    	   ConnectorUtils::getDisplayConfig();
    	}

    	if(file_exists('custom/modules/Connectors/connectors/sources/ext/eapm/webex/config.php')) {
    	   mkdir_recursive('custom/modules/Connectors/backup/connectors/sources/ext/eapm/webex');
    	   copy_recursive('custom/modules/Connectors/connectors/sources/ext/eapm/webex', 'custom/modules/Connectors/backup/connectors/sources/ext/eapm/webex');
    	} else {
    	   mkdir_recursive('custom/modules/Connectors/connectors/sources/ext/eapm/webex');
    	}
    }

    function tearDown() {
        if(file_exists('custom/modules/Connectors/backup/connectors/sources/ext/eapm/webex')) {
    	   copy_recursive('custom/modules/Connectors/backup/connectors/sources/ext/eapm/webex', 'custom/modules/Connectors/connectors/sources/ext/eapm/webex');
           ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/connectors/sources/ext/eapm/webex');
        }
    }

    function testWebexProperty() {

        require_once('modules/Connectors/controller.php');
    	require_once('include/MVC/Controller/SugarController.php');
    	$controller = new ConnectorsController();
    	$_REQUEST['action'] = 'SaveModifyProperties';
    	$_REQUEST['module'] = 'Connectors';
    	$url = 'http://test/'.create_guid();
    	$_REQUEST['source0'] = 'ext_eapm_webex';
    	$_REQUEST['ext_eapm_webex_url'] = $url;
    	$_REQUEST['from_unit_test'] = true;
    	$controller->action_SaveModifyProperties();

    	require('custom/modules/Connectors/connectors/sources/ext/eapm/webex/config.php');
    	$webex = SourceFactory::getSource('ext_eapm_webex', false);
    	$this->assertEquals($url, $webex->getProperty('url'));
    }

}
