<?php
require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/utils/ConnectorUtils.php');

class ConnectorsEnableDisableTest extends Sugar_PHPUnit_Framework_TestCase {

    public $original_modules_sources;
	public $original_searchdefs;
	public $original_connectors;
	static $drop_lookup_mapping = false;

	public static function setUpBeforeClass() {
        // this is so that Hoovers connector won't SOAP for the huge lookup file
	    if(!file_exists(HOOVERS_LOOKUP_MAPPING_FILE)) {
	         copy(dirname(__FILE__)."/lookup_mapping_stub", HOOVERS_LOOKUP_MAPPING_FILE);
	         self::$drop_lookup_mapping = true;
	     }
	}

	public static function tearDownAfterClass()
	{
	    if(self::$drop_lookup_mapping) {
	        @unlink(HOOVERS_LOOKUP_MAPPING_FILE);
	    }
	}

    function setUp() {
        if(!file_exists(CONNECTOR_DISPLAY_CONFIG_FILE)) {
    	   ConnectorUtils::getDisplayConfig();
    	}
    	require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->original_modules_sources = $modules_sources;

    	//Remove the current file and rebuild with default
    	unlink(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->original_searchdefs = ConnectorUtils::getSearchDefs();

    	$this->original_connectors = ConnectorUtils::getConnectors();
    }

    function tearDown() {
    	write_array_to_file('modules_sources', $this->original_modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE);
        write_array_to_file('searchdefs', $this->original_searchdefs, 'custom/modules/Connectors/metadata/searchdefs.php');
        ConnectorUtils::saveConnectors($this->original_connectors);
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

    	require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->assertTrue(count($modules_sources) == 3);
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

    	require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->assertTrue(empty($modules_sources['ext_soap_hoovers']));
    	$this->assertTrue(empty($modules_sources['ext_rest_linkedin']));
    }

    function test_disable_enable_eapm()
    {
        require_once('modules/Connectors/controller.php');
    	require_once('include/MVC/Controller/SugarController.php');
    	$controller = new ConnectorsController();

    	$_REQUEST['display_values'] = '';
    	$_REQUEST['display_sources'] = 'ext_soap_hoovers,ext_rest_linkedin,ext_rest_twitter,ext_eapm_webex,ext_eapm_facebook';
    	$_REQUEST['ext_eapm_facebook_external'] = 1;
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;

    	$controller->action_SaveModifyDisplay();
    	ConnectorUtils::getConnectors(true);
        $this->assertFalse(ConnectorUtils::eapmEnabled('ext_rest_twitter'), "Failed to disable Twitter");
        $this->assertFalse(ConnectorUtils::eapmEnabled('ext_eapm_webex'), "Failed to disable WebEx");
        $this->assertTrue(ConnectorUtils::eapmEnabled('ext_eapm_facebook'), "Failed to enable Facebook");

        // now reenable them
    	$_REQUEST['display_values'] = '';
    	$_REQUEST['display_sources'] = 'ext_soap_hoovers,ext_rest_linkedin,ext_rest_twitter,ext_eapm_webex';
    	$_REQUEST['ext_rest_twitter_external'] = 1;
    	$_REQUEST['ext_eapm_webex_external'] = 1;
    	$_REQUEST['action'] = 'SaveModifyDisplay';
    	$_REQUEST['module'] = 'Connectors';
    	$_REQUEST['from_unit_test'] = true;

    	$controller->action_SaveModifyDisplay();
    	ConnectorUtils::getConnectors(true);
        $this->assertTrue(ConnectorUtils::eapmEnabled('ext_rest_twitter'), "Failed to enable Twitter");
        $this->assertTrue(ConnectorUtils::eapmEnabled('ext_eapm_webex'), "Failed to enable WebEx");
        $this->assertTrue(ConnectorUtils::eapmEnabled('ext_eapm_facebook'), "Failed to enable Facebook");
    }

}
