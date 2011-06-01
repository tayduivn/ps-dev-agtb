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

class ConnectorsEnableDisableTest extends Sugar_Connectors_TestCase
{
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
