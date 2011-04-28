<?php


class Bug43653Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    
    public function setUp() 
    {
		$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		if(file_exists($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php'))
		{
			copy($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php', $GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php.bak');
			unlink($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules.php');
		}
		
    	if(file_exists($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules_display.php'))
		{
			copy($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules_display.php', $GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules_display.php.bak');
			unlink($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules_display.php');
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
		
    	if(file_exists($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules_display.php.bak'))
		{
			copy($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules_display.php.bak', $GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules_display.php');
			unlink($GLOBALS['sugar_config']['cache_dir']. 'modules/unified_search_modules_display.php.bak');
		}	        
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
    	 
    	 ob_flush();
		 require_once('include/MVC/View/views/view.spot.php');
		 $spotView = new ViewSpot();
		 ob_start();
		 $spotView->display();
    	 $results = ob_get_contents();
	     
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
	}	
    
}

?>