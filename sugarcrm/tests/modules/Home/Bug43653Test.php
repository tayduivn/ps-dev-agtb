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

class Bug43653Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    public function setUp()
    {
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
		$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		if(file_exists($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php'))
		{
			copy($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php', $GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php.bak');
			unlink($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php');
		}

    	if(file_exists('custom/modules/unified_search_modules_display.php'))
		{
			copy('custom/modules/unified_search_modules_display.php', 'custom/modules/unified_search_modules_display.php.bak');
			unlink('custom/modules/unified_search_modules_display.php');
		}
    }

    public function tearDown()
    {
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

		if(file_exists($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php.bak'))
		{
			copy($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php.bak', $GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php');
			unlink($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php.bak');
		}

    	if(file_exists('custom/modules/unified_search_modules_display.php.bak'))
		{
			copy('custom/modules/unified_search_modules_display.php.bak', 'custom/modules/unified_search_modules_display.php');
			unlink('custom/modules/unified_search_modules_display.php.bak');
		}

		SugarTestTaskUtilities::removeAllCreatedTasks();
		SugarTestAccountUtilities::removeAllCreatedAccounts();
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

	public function testFisrtUnifiedSearchWithoutUserPreferences()
	{
		 //Enable the Tasks, Accounts and Contacts modules
    	 require_once('modules/Home/UnifiedSearchAdvanced.php');
    	 $_REQUEST = array();
    	 $_REQUEST['enabled_modules'] = 'Tasks,Accounts,Contacts';
    	 $unifiedSearchAdvanced = new UnifiedSearchAdvanced();
    	 $unifiedSearchAdvanced->saveGlobalSearchSettings();

    	 $_REQUEST = array();
    	 $_REQUEST['advanced'] = 'false';
    	 $unifiedSearchAdvanced->query_stirng = 'blah';

         $unifiedSearchAdvanced->search();
    	 global $current_user;
    	 $users_modules = $current_user->getPreference('globalSearch', 'search');
    	 $this->assertTrue(!empty($users_modules), 'Assert we have set the user preferences properly');
    	 $this->assertTrue(isset($users_modules['Tasks']), 'Assert that we have added the Tasks module');
    	 $this->assertEquals(count($users_modules), 3, 'Assert that we have 3 modules in user preferences for global search');
	}

	//BEGIN SUGARCRM flav=pro ONLY
	public function checkOutputFisrtGlobalSearchWithoutUserPreferences($results)
	{
    	 $matcher = array(
	        'tag'        => 'a',
	        'content' => 'Bug43653Test_Task'
	     );
	     $this->assertTag($matcher, $results, 'Assert that <a> link for Bug43653Test_Task was found');

    	 $matcher = array(
	        'tag'        => 'a',
	        'content' => 'Bug43653Test_Account'
	     );
	     $this->assertTag($matcher, $results, 'Assert that <a> link for Bug43653Test_Account was found');
	     return true;
	}

	public function testFisrtGlobalSearchWithoutUserPreferences()
	{
		 //Enable the Tasks, Accounts and Contacts modules
    	 require_once('modules/Home/UnifiedSearchAdvanced.php');
    	 $_REQUEST = array();
    	 $_REQUEST['enabled_modules'] = 'Tasks,Accounts,Contacts';
    	 $unifiedSearchAdvanced = new UnifiedSearchAdvanced();
    	 $unifiedSearchAdvanced->saveGlobalSearchSettings();

    	 $testAccount = SugarTestAccountUtilities::createAccount();
    	 $testAccount->name = 'Bug43653Test_Account';
    	 $testAccount->save();

    	 $testTask = SugarTestTaskUtilities::createTask();
    	 $testTask->name = 'Bug43653Test_Task';
    	 $testTask->save();

    	 $_REQUEST = array();
    	 $_REQUEST['q'] = 'Bug43653Test';
		 require_once('include/MVC/View/views/view.spot.php');
		 $spotView = new ViewSpot();
		 $spotView->display();

		 $this->setOutputCheck(array($this, "checkOutputFisrtGlobalSearchWithoutUserPreferences"));

	}
    //END SUGARCRM flav=pro ONLY
}
