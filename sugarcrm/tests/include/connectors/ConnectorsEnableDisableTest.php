<?php
//FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA") which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'include/connectors/ConnectorsTestCase.php';

class ConnectorsEnableDisableTest extends Sugar_Connectors_TestCase
{
    public function setUp()
    {
        global $current_user;
        global $app_list_strings;
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        $user = new User();
        $current_user = $user->retrieve('1');
    }

    public function tearDown()
    {

    }

    public function testEnableAll()
    {
        require_once 'modules/Connectors/controller.php';
        require_once 'include/MVC/Controller/SugarController.php';

        $_REQUEST['display_values'] = "ext_rest_linkedin:Accounts,ext_rest_linkedin:Contacts,ext_rest_linkedin:Leads";
        $_REQUEST['display_sources'] = 'ext_rest_linkedin';
        $_REQUEST['action'] = 'SaveModifyDisplay';
        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;

        $controller = new ConnectorsController();
        $controller->action_SaveModifyDisplay();

        require(CONNECTOR_DISPLAY_CONFIG_FILE);

        foreach ($modules_sources as $module => $entries) {
            if ($module == 'Accounts' || $module == 'Contacts') {
                $this->assertTrue(in_array('ext_rest_linkedin', $entries));
            }
        }
    }

    public function testDisableAll()
    {
        require_once 'modules/Connectors/controller.php';
        require_once 'include/MVC/Controller/SugarController.php';
        $controller = new ConnectorsController();

        $_REQUEST['display_values'] = '';
        $_REQUEST['display_sources'] = 'ext_rest_linkedin';
        $_REQUEST['action'] = 'SaveModifyDisplay';
        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;

        $controller->action_SaveModifyDisplay();

        require(CONNECTOR_DISPLAY_CONFIG_FILE);
        $this->assertTrue(empty($modules_sources['ext_rest_linkedin']));
    }

    public function testDisableEnableEAPM()
    {
        require_once 'modules/Connectors/controller.php';
        require_once 'include/MVC/Controller/SugarController.php';
        $controller = new ConnectorsController();

        $_REQUEST['display_values'] = '';
        $_REQUEST['display_sources'] = 'ext_rest_linkedin,ext_rest_twitter,ext_eapm_webex,ext_eapm_facebook';
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
        $_REQUEST['display_sources'] = 'ext_rest_linkedin,ext_rest_twitter,ext_eapm_webex';
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
