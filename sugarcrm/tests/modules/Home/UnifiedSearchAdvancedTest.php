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

require_once 'modules/Home/UnifiedSearchAdvanced.php';
require_once 'modules/Contacts/Contact.php';
require_once 'include/utils/layout_utils.php';

/**
 * @ticket 34125
 */
class UnifiedSearchAdvancedTest extends Sugar_PHPUnit_Framework_OutputTestCase
{
    protected $_contact = null;
    private $_hasUnifiedSearchModulesConfig = false;
    private $_hasUnifiedSearchModulesDisplay = false;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $unid = uniqid();
        $contact = new Contact();
        $contact->id = 'l_'.$unid;
        $contact->first_name = 'Greg';
        $contact->last_name = 'Brady';
        $contact->new_with_id = true;
        $contact->save();
        $this->_contact = $contact;

        if(file_exists(sugar_cached('modules/unified_search_modules.php')))
        {
        	$this->_hasUnifiedSearchModulesConfig = true;
        	copy(sugar_cached('modules/unified_search_modules.php'), sugar_cached('modules/unified_search_modules.php.bak'));
        	unlink(sugar_cached('modules/unified_search_modules.php'));
        }

        if(file_exists('custom/modules/unified_search_modules_display.php'))
        {
        	$this->_hasUnifiedSearchModulesDisplay = true;
        	copy('custom/modules/unified_search_modules_display.php', 'custom/modules/unified_search_modules_display.php.bak');
        	unlink('custom/modules/unified_search_modules_display.php');
        	SugarAutoLoader::delFromMap('custom/modules/unified_search_modules_display.php', false);
        }


	}

	public function tearDown()
	{
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->_contact->id}'");
        unset($this->_contact);

        if($this->_hasUnifiedSearchModulesConfig)
        {
        	copy(sugar_cached('modules/unified_search_modules.php.bak'), sugar_cached('modules/unified_search_modules.php'));
        	unlink(sugar_cached('modules/unified_search_modules.php.bak'));
        } else {
        	unlink(sugar_cached('modules/unified_search_modules.php'));
        }

        if($this->_hasUnifiedSearchModulesDisplay)
        {
        	copy('custom/modules/unified_search_modules_display.php.bak', 'custom/modules/unified_search_modules_display.php');
        	unlink('custom/modules/unified_search_modules_display.php.bak');
        } else {
        	SugarAutoLoader::unlink('custom/modules/unified_search_modules_display.php');
        }

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        if(isset($_REQUEST['module']))
        {
            unset($_REQUEST['module']);
        }

        if(isset($_REQUEST['query_string']))
        {
            unset($_REQUEST['query_string']);
        }

        if(isset($_REQUEST['enabled_modules']))
        {
            unset($_REQUEST['enabled_modules']);
        }
	}

	public function testSearchByFirstName()
	{
		global $mod_strings, $modListHeader, $app_strings, $beanList, $beanFiles;
		require('config.php');
		require('include/modules.php');
		$modListHeader = $moduleList;
    	$_REQUEST['query_string'] = $this->_contact->first_name;
    	$_REQUEST['module'] = 'Home';
		$usa = new UnifiedSearchAdvanced();
		$usa->search();
		$this->expectOutputRegex("/{$this->_contact->first_name}/");
    }

	public function testSearchByFirstAndLastName()
	{
		global $mod_strings, $modListHeader, $app_strings, $beanList, $beanFiles;
		require('config.php');
		require('include/modules.php');
		$_REQUEST['query_string'] = $this->_contact->first_name.' '.$this->_contact->last_name;
    	$_REQUEST['module'] = 'Home';
		$usa = new UnifiedSearchAdvanced();
		$usa->search();
		$this->expectOutputRegex("/{$this->_contact->first_name}/");
    }

    public function testUserPreferencesSearch()
    {
		global $mod_strings, $modListHeader, $app_strings, $beanList, $beanFiles, $current_user;
		require('config.php');
		require('include/modules.php');

    	$usa = new UnifiedSearchAdvanced();
    	$_REQUEST['enabled_modules'] = 'Accounts,Contacts';
    	$usa->saveGlobalSearchSettings();


        $current_user->setPreference('globalSearch', array('Accounts', 'Contacts'), 0, 'search');
        $current_user->savePreferencesToDB();

    	$_REQUEST = array();
		$_REQUEST['query_string'] = $this->_contact->first_name.' '.$this->_contact->last_name;
    	$_REQUEST['module'] = 'Home';
    	$usa->search();

    	$modules = $current_user->getPreference('globalSearch', 'search');
    	$this->assertEquals(count($modules), 2, 'Assert that there are two modules in the user preferences as defined from the global search');

        $this->assertEquals('Accounts', $modules[0], 'Assert that the Accounts module has been added');
        $this->assertEquals('Contacts', $modules[1], 'Assert that the Contacts module has been added');
        // this is to suppress output. Need to fix properly with a good unit test.
        $this->expectOutputRegex('//');
    }
}

