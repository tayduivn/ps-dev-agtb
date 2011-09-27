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
 
require_once('modules/UpgradeWizard/uw_utils.php');

class Bug44414Test extends Sugar_PHPUnit_Framework_TestCase 
{
	var $has_save_file = false;
	var $save_file = 'modules/DocumentRevisions/Save.php';
	var $backup_file;
	/*
	public function setUp() 
	{
		
		if(file_exists($this->save_file))
		{
		   //This really shouldn't be happening, but just in case...
		   $this->has_save_file = true;
		   $this->backup_file = $this->save_file . '.' . gmmktime() . '.bak';
		   copy($this->save_file, $this->backup_file);
		} else {
		   if(!file_exists('modules/DocumentRevisions'))
		   {
		   	  mkdir_recursive('modules/DocumentRevisions');
		   }
		   //Create the test file
		   write_array_to_file("test", array(), $this->save_file);
		}
	}
	
	public function tearDown() 
	{
		if($this->has_save_file)
		{
		   copy($this->backup_file, $this->save_file);
		   unlink($this->backup_file);
		} else {
		   if(file_exists($this->save_file))
		   {
		   		unlink($this->save_file);
		   }
		}
		
		if(file_exists($this->save_file . '.suback.bak'))
		{
			unlink($this->save_file . '.suback.bak');
		}
	}
	*/

	public function testUnlinkUpgradeFiles600()
	{
        $this->markTestSkipped('No upgrade path from 600->640');
        return;
		$this->assertTrue(file_exists($this->save_file), 'Assert the ' . $this->save_file . ' exists');
		unlinkUpgradeFiles('600');
		$this->assertFalse(file_exists($this->save_file), 'Assert the ' . $this->save_file . ' no longer exists');
		$this->assertTrue(file_exists($this->save_file . '.suback.bak'), 'Assert the ' . $this->save_file . '.suback.bak file exists');		
	}	
	
	public function testUnlinkUpgradeFiles610()
	{
        $this->markTestSkipped('No upgrade path from 610->640');
        return;
		$this->assertTrue(file_exists($this->save_file), 'Assert the ' . $this->save_file . ' exists');
		unlinkUpgradeFiles('610');
		$this->assertFalse(file_exists($this->save_file), 'Assert the ' . $this->save_file . ' no longer exists');
		$this->assertTrue(file_exists($this->save_file . '.suback.bak'), 'Assert the ' . $this->save_file . '.suback.bak file exists');		
	}
}