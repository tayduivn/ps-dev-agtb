<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/ConnectorsTestUtility.php');
require_once('include/connectors/utils/ConnectorUtils.php');
require_once('modules/Connectors/controller.php');

class Sugar_Connectors_TestCase extends Sugar_PHPUnit_Framework_TestCase {
    public $original_modules_sources;
	public $original_searchdefs;
	public $original_connectors;

	static $drop_lookup_mapping = false;

    public static function setUpBeforeClass()
	{
    	SourceFactory::getSource('ext_soap_hoovers', false);
	    // this is so that Hoovers connector won't SOAP for the huge lookup file
        if(!file_exists(HOOVERS_LOOKUP_MAPPING_FILE)) {
	         mkdir_recursive(dirname(HOOVERS_LOOKUP_MAPPING_FILE));
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
	    ConnectorUtils::getDisplayConfig();
    	require(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->original_modules_sources = $modules_sources;

    	//Remove the current file and rebuild with default
    	unlink(CONNECTOR_DISPLAY_CONFIG_FILE);
    	$this->original_searchdefs = ConnectorUtils::getSearchDefs(true);

    	$this->original_connectors = ConnectorUtils::getConnectors(true);
    }

    function tearDown() {
        if($this->original_modules_sources != null) {
    	    write_array_to_file('modules_sources', $this->original_modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE);
        }
        if($this->original_searchdefs != null) {
            write_array_to_file('searchdefs', $this->original_searchdefs, 'custom/modules/Connectors/metadata/searchdefs.php');
        }
        if($this->original_connectors != null) {
            ConnectorUtils::saveConnectors($this->original_connectors);
        }
    }
}