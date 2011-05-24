<?php
//FILE SUGARCRM flav=pro ONLY

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
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