<?php

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'modules/Leads/views/view.convertlead.php';
//BEGIN SUGARCRM flav=pro ONLY
require_once 'modules/Leads/views/view.editconvert.php';
//END SUGARCRM flav=pro ONLY

class Bug45187Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    public function setUp()
    {
        global $mod_strings;
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Leads');

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);
    }
    
    /**
    * @group bug45187
    */
    public function testActivityModuleLabel()
    {
        $this->markTestIncomplete('Opportunities amount is now a calculated field and we have notice on the amount field - To be fixed by MAR/SFA team');
        global $sugar_config;
        global $app_list_strings;

        // init
        $lead = SugarTestLeadUtilities::createLead();
        $account = SugarTestAccountUtilities::createAccount();

        // simulate module renaming
        $org_name = $app_list_strings['moduleListSingular']['Contacts'];
        $app_list_strings['moduleListSingular']['Contacts'] = 'People';

        // set the request/post parameters before converting the lead
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'ConvertLead';
        $_REQUEST['record'] = $lead->id;
        unset($_REQUEST['handle']);
        $_REQUEST['selectedAccount'] = $account->id;
        $sugar_config['lead_conv_activity_opt'] = 'move';

        // call display to trigger conversion
        $vc = new ViewConvertLead();
        $vc->init($lead);
        $vc->display();

        // the activity options dropdown should use the renamed module label
        $this->expectOutputRegex('/People<\/OPTION>/');

        // cleanup
        $app_list_strings['moduleListSingular']['Contacts'] = $org_name;
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
        unset($_REQUEST['record']);
        unset($_REQUEST['selectedAccount']);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }

//BEGIN SUGARCRM flav=pro ONLY
    /**
    * @group bug45187
    */
    public function testStudioModuleLabel()
    {
        global $app_list_strings;

        // simulate module renaming
        $org_name = $app_list_strings['moduleList']['Accounts'];
        $app_list_strings['moduleList']['Contacts'] = 'PeopleXYZ';

        // set the request/post parameters
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'Editconvert';

        // call display to generate output
        $vc = new ViewEditConvert();
        $vc->display();

        // ensure the new module name is used
        $this->expectOutputRegex('/.*PeopleXYZ.*/');

        // cleanup
        $app_list_strings['moduleList']['Contacts'] = $org_name;
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
    }
//END SUGARCRM flav=pro ONLY
}
