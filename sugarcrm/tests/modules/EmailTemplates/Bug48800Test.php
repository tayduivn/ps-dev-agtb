<?php
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

require_once('modules/EmailTemplates/EmailTemplate.php');

class Bug48800Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $emailTemplate;
    var $user;

    public function setUp()
    {
        global $current_user, $app_list_strings;
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $current_user = $this->user;
        $app_list_strings = return_app_list_strings_language('en_us');
        $this->user->setPreference('default_locale_name_format', 's f l');
        $this->user->savePreferencesToDB();
        $this->user->save();

        $this->emailTemplate = new EmailTemplate();
        $this->emailTemplate->name = 'Bug48800Test';
        $this->emailTemplate->assigned_user_id = $this->user->id;
        //BEGIN SUGARCRM flav=pro ONLY
        $this->emailTemplate->team_id = $this->user->team_id;
        $this->emailTemplate->team_set_id = $this->user->team_id;
        //END SUGARCRM flav=pro ONLY
        $this->emailTemplate->save();
        //$this->useOutputBuffering = false;
    }

    public function tearDown()
    {
        global $sugar_config;
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query("DELETE FROM email_templates WHERE id = '{$this->emailTemplate->id}'");
    }

    public function testAssignedUserName()
    {
        global $locale;
        require_once('include/Localization/Localization.php');
        $locale = new Localization();
        $testName = $locale->getLocaleFormattedName($this->user->first_name, $this->user->last_name);
        $testTemplate = new EmailTemplate();
        $testTemplate->retrieve($this->emailTemplate->id);
        $this->assertEquals(
            $testName,
            $testTemplate->assigned_user_name,
            'Assert that the assigned_user_name is the locale formatted name value'
        );
    }
}
?>
