<?php 
//FILE SUGARCRM flav=pro ONLY
require_once('include/EditView/SubpanelQuickCreate.php');

class Bug44836Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
	public function setUp()
	{
		include('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;
	    $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h.iA");	    		
	}
	
	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}
	
	public function testContractsSubpanelQuickCreate()
	{
		 $subpanelQuickCreate = new SubpanelQuickCreate('Contracts', 'QuickCreate');
		 $this->expectOutputRegex('/check_form\s*?\(\s*?\'form_SubpanelQuickCreate_Contracts\'\s*?\)/');
	}
	
}

?>