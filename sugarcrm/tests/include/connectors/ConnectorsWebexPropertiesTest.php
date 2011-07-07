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
require_once('include/connectors/ConnectorsTestCase.php');

class ConnectorsWebexPropertiesTest extends Sugar_Connectors_TestCase {

	function setUp() {
        parent::setUp();
    	if(file_exists('custom/modules/Connectors/connectors/sources/ext/eapm/webex/config.php')) {
    	   mkdir_recursive('custom/modules/Connectors/backup/connectors/sources/ext/eapm/webex');
    	   copy_recursive('custom/modules/Connectors/connectors/sources/ext/eapm/webex', 'custom/modules/Connectors/backup/connectors/sources/ext/eapm/webex');
    	} else {
    	   mkdir_recursive('custom/modules/Connectors/connectors/sources/ext/eapm/webex');
    	}
    }

    function tearDown() {
        parent::tearDown();
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
