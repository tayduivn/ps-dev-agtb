<?php
//FILE SUGARCRM flav=pro ONLY
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

/**
 * @ticket 46763
 */
class Bug46763Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Language used to perform the test
     *
     * @var string
     */
    protected $language;

    /**
     * Names of singular instances that will be used during testing
     *
     * @var array
     */
    protected $modules = array(
        'Accounts' => 'Test1Account',
        'Contacts' => 'Test2Contact',
        'Leads'    => 'Test3Lead',
        'Prospects'    => 'Test4Target',
    );

    /**
     * Temporary file path
     *
     * @var string
     */
    protected $file = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * Generates custom module localization file
     */
    public function setUp()
    {
        global $mod_strings;
        $mod_strings = return_module_language($GLOBALS['current_language'], 'EmailTemplates');

        global $sugar_config;
        $this->language = $sugar_config['default_language'];

        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser(true, 1);

        // generate module localization data
        $data = array('<?php');
        $template = '$app_list_strings["moduleListSingular"]["%s"] = "%s";';
        foreach ($this->modules as $moduleName => $singular)
        {
            $data[] = sprintf($template, $moduleName, $singular);
        }

        // create custom localization file
        $this->file = 'custom/include/language/' . $this->language . '.lang.php';
        $dirName = dirname($this->file);
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }

        file_put_contents($this->file, implode(PHP_EOL, $data));
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * Removes custom module localization file
     */
    public function tearDown()
    {
        unlink($this->file);

        unset($GLOBALS['mod_strings']);
    }

    /**
     * Tests that custom module localization data is used when combining
     * drop down list options
     *
     * @outputBuffering enabled
     */
    public function testCustomModuleLocalizationIsUsed()
    {
        // set global variables in order to create the needed environment
        $_REQUEST['module']         = '';
        $_REQUEST['return_module']  = '';
        $_REQUEST['return_id']      = '';
        $_REQUEST['request_string'] = '';
        $GLOBALS['request_string']  = '';

        // initialize needed local variables
        global $mod_strings, $app_strings, $sugar_config;
        $app_list_strings = return_app_list_strings_language($this->language);
        $xtpl = null;

        require 'modules/EmailTemplates/EditView.php';

        // clean up created global variables
        unset(
            $_REQUEST['module'],
            $_REQUEST['return_module'],
            $_REQUEST['return_id'],
            $_REQUEST['request_string'],
            $GLOBALS['request_string']
        );

        $this->assertInstanceOf('XTemplate', $xtpl);

        /** @var XTemplate $xtpl */
        $vars = $xtpl->VARS;

        // ensure that drop down list is assigned to the template
        $this->assertArrayHasKey('DROPDOWN', $vars);

        // ensure that all localized values are contained within drop down list
        foreach ($this->modules as $singular)
        {
            $this->assertContains($singular, $vars['DROPDOWN']);
        }
    }
}
