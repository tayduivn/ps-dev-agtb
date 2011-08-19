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

require_once('include/dir_inc.php');
require_once('modules/UpgradeWizard/UpgradeRemoval.php');

class Bug46027Test extends Sugar_PHPUnit_Framework_TestCase 
{

	public function setUp()
	{		
		if(file_exists('custom/backup/include/utils/external_cache'))
		{
			rmdir_recursive('custom/backup/include/utils/external_cache');
			rmdir_recursive('custom/backup/include/utils');
			rmdir_recursive('custom/backup/include');	
		}
		
		if(file_exists('include/JSON.js'))
		{
			unlink('include/JSON.js');
		}		
		
		//Simulate file and directory that should be removed by UpgradeRemove62x.php
		copy('include/JSON.php', 'include/JSON.js');
		mkdir_recursive('include/utils/external_cache');		
	}
	
	/**
	 * ensure that the test directory and file are removed at the end of the test
	 */
	public function tearDown()
	{
		if(file_exists('include/utils/external_cache'))
		{
		   rmdir_recursive('include/utils/external_cache');
		}
		
		if(file_exists('include/JSON.js'))
		{
		   unlink('include/JSON.js');	
		}
		
		if(file_exists('custom/backup/include/utils/external_cache'))
		{
			rmdir_recursive('custom/backup/include/utils/external_cache');
			rmdir_recursive('custom/backup/include/utils');
			rmdir_recursive('custom/backup/include');
		}		
	}
	
	public function testUpgradeRemoval()
	{
		$instance = new UpgradeRemoval62xMock();
		$instance->processFilesToRemove($instance->getFilesToRemove(622));
		$this->assertTrue(!file_exists('include/utils/external_cache'), 'Assert that include/utils/external_cache was removed');
		$this->assertTrue(file_exists('custom/backup/include/utils/external_cache'), 'Assert that the custom/backup/include/utils/external_cache directory was created');		
		$this->assertTrue(!file_exists('include/JSON.js'), 'Assert that include/JSON.js file is removed');
		$this->assertTrue(file_exists('custom/backup/include/JSON.js'), 'Assert that include/JSON.js was moved to custom/backup/include/JSON.js');
	}
	
}

class UpgradeRemoval62xMock extends UpgradeRemoval
{
	
public function getFilesToRemove($version)
{
	$files = array();
	$files[] = 'include/utils/external_cache';
	$files[] = 'include/JSON.js';
	return $files;
}

}
?>