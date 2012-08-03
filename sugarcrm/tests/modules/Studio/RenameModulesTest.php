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

require_once('modules/Studio/wizards/RenameModules.php');


class RenameModulesTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $language;
    private $language_contents;

    public function setup()
    {
        $mods = array('Accounts', 'Contacts', 'Campaigns');
        foreach($mods as $mod)
        {
            if(file_exists("custom/modules/{$mod}/language/en_us.lang.php"))
            {
                $this->language_contents[$mod] = file_get_contents("custom/modules/{$mod}/language/en_us.lang.php");
                unlink("custom/modules/{$mod}/language/en_us.lang.php");
            }
        }

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->language = 'en_us';

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }

    public function tearDown()
    {
        $this->removeCustomAppStrings();
        $this->removeModuleStrings(array('Accounts'));
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        SugarCache::$isCacheReset = false;

        if(!empty($this->language_contents))
        {
            foreach($this->language_contents as $key=>$contents)
            {
                sugar_file_put_contents("custom/modules/{$key}/language/en_us.lang.php", $contents);
            }
        }
    }


    public function testGetRenamedModules()
    {
        $rm = new RenameModules();
        $this->assertEquals(0, count($rm->getRenamedModules()) );
    }


    public function testRenameContactsModule()
    {
        $module = 'Accounts';
        $newSingular = 'Company';
        $newPlural = 'Companies';

        $rm = new RenameModules();

        $_REQUEST['slot_0'] = 0;
        $_REQUEST['key_0'] = $module;
        $_REQUEST['svalue_0'] = $newSingular;
        $_REQUEST['value_0'] = $newPlural;
        $_REQUEST['delete_0'] = '';
        $_REQUEST['dropdown_lang'] = $this->language;
        $_REQUEST['dropdown_name'] = 'moduleList';

        global $app_list_strings;
        if (!isset($app_list_strings['parent_type_display'][$module])) {
            $app_list_strings['parent_type_display'][$module] = 'Account';
        }
        $rm->save(FALSE);

        //Test app list strings
        $app_list_string = return_app_list_strings_language('en_us');
        $this->assertEquals($newSingular, $app_list_string['moduleListSingular'][$module] );
        $this->assertEquals($newPlural, $app_list_string['moduleList'][$module] );
        $this->assertEquals($newSingular, $app_list_string['parent_type_display'][$module] );

        //Test module strings for account
        $accountStrings = return_module_language('en_us','Accounts', TRUE);
        $this->assertEquals('Create Company', $accountStrings['LNK_NEW_ACCOUNT'], "Rename module failed for modules modStrings.");
        $this->assertEquals('View Companies', $accountStrings['LNK_ACCOUNT_LIST'], "Rename module failed for modules modStrings.");
        $this->assertEquals('Import Companies', $accountStrings['LNK_IMPORT_ACCOUNTS'], "Rename module failed for modules modStrings.");
        $this->assertEquals('Company Search', $accountStrings['LBL_SEARCH_FORM_TITLE'], "Rename module failed for modules modStrings.");

        //Test related link renames
        $contactStrings = return_module_language('en_us','Contacts', TRUE);
        $this->assertEquals('Company Name:', $contactStrings['LBL_ACCOUNT_NAME'], "Rename related links failed for module.");
        $this->assertEquals('Company ID:', $contactStrings['LBL_ACCOUNT_ID'], "Rename related links failed for module.");

        //Test subpanel renames
        $campaignStrings = return_module_language('en_us','Campaigns', TRUE);
        $this->assertEquals('Companies', $campaignStrings['LBL_CAMPAIGN_ACCOUNTS_SUBPANEL_TITLE'], "Renaming subpanels failed for module.");
        // bug 45554: ensure labels are changed
        $this->assertEquals('Companies', $campaignStrings['LBL_ACCOUNTS'], 'Renaming labels failed for module.');

        //Ensure we recorded which modules were modified.
        $renamedModules = $rm->getRenamedModules();
        $this->assertTrue( count($renamedModules) > 0 );

        $this->removeCustomAppStrings();
        $this->removeModuleStrings( $renamedModules );
    }

    public function testRenameNonExistantModule()
    {
        $module = 'UnitTestDNEModule';
        $newSingular = 'UnitTest';
        $newPlural = 'UnitTests';

        $rm = new RenameModules();

        $_REQUEST['slot_0'] = 0;
        $_REQUEST['key_0'] = $module;
        $_REQUEST['svalue_0'] = $newSingular;
        $_REQUEST['value_0'] = $newPlural;
        $_REQUEST['delete_0'] = '';
        $_REQUEST['dropdown_lang'] = $this->language;
        $_REQUEST['dropdown_name'] = 'moduleList';
        $_REQUEST['use_push'] = TRUE;

        $rm->save(FALSE);

        //Ensure no modules were modified
        $renamedModules = $rm->getRenamedModules();
        $this->assertTrue( count($renamedModules) == 0 );

        //Ensure none of the app list strings were modified.
        $app_list_string = return_app_list_strings_language('en_us');
        if(isset( $app_list_string['moduleListSingular'][$module])) {
            $this->assertNotEquals($newSingular, $app_list_string['moduleListSingular'][$module] );
        }
        if(isset($app_list_string['moduleList'][$module])) {
            $this->assertNotEquals($newPlural, $app_list_string['moduleList'][$module] );
        }

    }


    private function removeCustomAppStrings()
    {
        $fileName = 'custom'. DIRECTORY_SEPARATOR . 'include'. DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $this->language . '.lang.php';
        if( file_exists($fileName) )
        {
            @unlink($fileName);
        }
    }

    private function removeModuleStrings($modules)
    {
        foreach($modules as $module => $v)
        {
            $fileName = 'custom'. DIRECTORY_SEPARATOR . 'modules'. DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $this->language . '.lang.php';
            if( file_exists($fileName) )
            {
                @unlink($fileName);
            }

        }

    }

    /**
     * @group bug46880
     * making sure subpanel is not renamed twice by both plural name and singular name
     */
    public function testSubpanelRenaming()
    {
        $this->markTestIncomplete('Because of bug 47239,  Skipping test.');

        $module = 'Accounts';
        $newSingular = 'Account1';
        $newPlural = 'Accounts2';

        $rm = new RenameModules();

        $_REQUEST['slot_0'] = 0;
        $_REQUEST['key_0'] = $module;
        $_REQUEST['svalue_0'] = $newSingular;
        $_REQUEST['value_0'] = $newPlural;
        $_REQUEST['delete_0'] = '';
        $_REQUEST['dropdown_lang'] = $this->language;
        $_REQUEST['dropdown_name'] = 'moduleList';

        global $app_list_strings;
        if (!isset($app_list_strings['parent_type_display'][$module])) {
            $app_list_strings['parent_type_display'][$module] = 'Account';
        }
        $rm->save(FALSE);

        //Test subpanel renames
        $bugStrings = return_module_language('en_us','Bugs', TRUE);
        $this->assertEquals('Accounts2', $bugStrings['LBL_ACCOUNTS_SUBPANEL_TITLE'], "Renaming subpanels failed for module.");

        //Ensure we recorded which modules were modified.
        $renamedModules = $rm->getRenamedModules();
        $this->assertTrue( count($renamedModules) > 0 );

        //cleanup
        $this->removeCustomAppStrings();
        $this->removeModuleStrings( $renamedModules );
    }

    /**
     * @group bug45804
     */
    public function testDashletsRenaming()
    {
        $this->markTestSkipped('Because of bug 47239,  Skipping test.');

        $module = 'Accounts';
        $newSingular = 'Account1';
        $newPlural = 'Accounts2';

        $rm = new RenameModules();

        $_REQUEST['slot_0'] = 0;
        $_REQUEST['key_0'] = $module;
        $_REQUEST['svalue_0'] = $newSingular;
        $_REQUEST['value_0'] = $newPlural;
        $_REQUEST['delete_0'] = '';
        $_REQUEST['dropdown_lang'] = $this->language;
        $_REQUEST['dropdown_name'] = 'moduleList';

        global $app_list_strings;
        if (!isset($app_list_strings['parent_type_display'][$module])) {
            $app_list_strings['parent_type_display'][$module] = 'Account';
        }
        $rm->save(FALSE);

        //Test dashlets renames
        $callStrings = return_module_language('en_us', 'Accounts', TRUE);
        $this->assertEquals('My Accounts2', $callStrings['LBL_HOMEPAGE_TITLE'], "Renaming dashlets failed for module.");

        //Ensure we recorded which modules were modified.
        $renamedModules = $rm->getRenamedModules();
        $this->assertTrue( count($renamedModules) > 0 );

        //cleanup
        $this->removeCustomAppStrings();
        $this->removeModuleStrings( $renamedModules );
    }
}
