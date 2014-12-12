<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


require_once('modules/Reports/views/view.buildreportmoduletree.php');
require_once('modules/Reports//SavedReport.php');

class Bug47271Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $orig_name;

    public function setUp()
    {
        global $current_user, $app_strings, $mod_strings, $app_list_strings;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $app_strings = return_application_language($GLOBALS['current_language']);
        $mod_strings  = return_module_language($GLOBALS['current_language'], 'Reports');
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        $this->orig_name = $app_list_strings['moduleList']['Contacts'];
    }

    public function tearDown()
    {
        global $app_list_strings;
        $app_list_strings['moduleList']['Contacts'] = $this->orig_name;

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($_REQUEST['report_module']);
        unset($_REQUEST['page']);
        unset($_REQUEST['module']);
        unset($GLOBALS['module']);
        unset($_REQUEST['action']);
    }

    public function testUserListView()
    {
        global $app_list_strings;
        $new_name = 'PeopleXYZ';

        // simulate module renaming
        $app_list_strings['moduleList']['Contacts'] = $new_name;

        // request settings
        $_REQUEST['action'] = 'BuildReportModuleTree';
        $_REQUEST['module'] = 'Reports';
        $_REQUEST['page'] = 'Report';
        $_REQUEST['report_module'] = 'Accounts';

        // module tree
        $view = new ReportsViewBuildreportmoduletree();
        $view->display();

        // ensure the module tree includes the new module name
        $pattern = '"text":"' . $new_name . '"'; //  the json string should include: "text":"PeopleXYZ"
        $this->expectOutputRegex('/.*'.$pattern.'.*/');
    }
}
?>
