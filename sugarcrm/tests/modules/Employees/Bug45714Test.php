<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/

require_once('modules/Users/User.php');

class Bug45714Test extends Sugar_PHPUnit_Framework_TestCase 
{
	public function setUp()
	{
		 $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		 //$this->useOutputBuffering = true;
	}	
	
	public function tearDown()
	{
		 SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}
	
	public function testViewAsAdminUser()
	{
		$GLOBALS['current_user']->is_admin = true;
		$output = $this->getEmployeeListViewOutput();
		$output = $this->getEmployeeListViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for admin user');		
		$output = $this->getEmployeeListViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for module admin user');
	}
	
	public function testViewAsNonAdminUser()
	{
		$output = $this->getEmployeeListViewOutput();
		$this->assertNotRegExp('/utilsLink/', $output, 'Assert that the links are not shown for normal user');
		$output = $this->getEmployeeDetailViewOutput();
		$this->assertNotRegExp('/utilsLink/', $output, 'Assert that the links are not shown for normal user');
	}
	
	//BEGIN SUGARCRM flav=pro ONLY
	public function testViewAsModuleAdmin()
	{
		$GLOBALS['current_user'] = new Bug45714UserMock();
		$output = $this->getEmployeeListViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for module admin user');
		$output = $this->getEmployeeDetailViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for module admin user');	
	}
	//END SUGARCRM flav=pro ONLY
	
	private function getEmployeeListViewOutput()
	{
		require_once('modules/Employees/views/view.list.php');
		$employeeViewList = new EmployeesViewList();
		$employeeViewList->module = 'Employees';
		return $employeeViewList->getModuleTitle(true);
	}
	
	private function getEmployeeDetailViewOutput()
	{
		require_once('modules/Employees/views/view.detail.php');
		$employeeViewDetail = new EmployeesViewDetail();
		$employeeViewDetail->module = 'Employees';
		return $employeeViewDetail->getModuleTitle(true);
	}	
}

class Bug45714UserMock extends User
{
    public function isDeveloperForModule($module) {
		return true;
    }
}

?>