<?php
//FILE SUGARCRM flav=pro ONLY
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
require_once('tests/modules/Trackers/TrackerTestUtility.php');

class TrackerCreateDefaultUserTest extends Sugar_PHPUnit_Framework_TestCase {

	var $skipTest = true;
	var $nonAdminUser;
	var $nonAdminUserId;
	var $adminUser;
	var $adminUserId;
    
    function setUp() {
    	if($this->skipTest) {
    	   $this->markTestSkipped("Skipping unless otherwise specified");
    	}
    	
		$user = new User();
		$user->retrieve('1');
		$GLOBALS['current_user'] = $user;    	
    	
    	TrackerTestUtility::setUp(); 
    	$_SESSION['reports_getACLAllowedModules'] = null;
        $this->nonAdminUser = new User();
        $this->nonAdminUser->first_name = 'non';
        $this->nonAdminUser->last_name = 'admin';
        $this->nonAdminUser->user_name = 'nonadmin';
        $this->nonAdminUserId = $this->nonAdminUser->save();	
        
        $this->adminUser = new User();
        $this->adminUser->is_admin = true;
        $this->adminUser->first_name = 'admin';
        $this->adminUser->last_name = 'test';
        $this->adminUser->user_name = 'admintest';
        $this->adminUserId = $this->adminUser->save();
        
        global $beanFiles, $beanList, $moduleList, $modListHeader, $sugar_config;
        require('config.php');
        require('include/modules.php');
        $modListHeader = $moduleList;
    }
    
    function tearDown() {
    	TrackerTestUtility::tearDown(); 
    	$GLOBALS['db']->query("DELETE FROM users WHERE id IN ('{$this->adminUser->id}', '{$this->nonAdminUser->id}')");    	
    	$GLOBALS['db']->query("DELETE FROM team_memberships WHERE user_id IN ('{$this->adminUser->id}', '{$this->nonAdminUser->id}')");
        $GLOBALS['db']->query("DELETE FROM acl_roles_users WHERE user_id IN ('{$this->adminUser->id}', '{$this->nonAdminUser->id}')");;  

		$user = new User();
		$user->retrieve('1');
		$GLOBALS['current_user'] = $user;    
    }
   
    function test_disabled_create_non_admin_user() {
    	global $current_user;
    	$current_user = $this->nonAdminUser;
        require_once('modules/Reports/SavedReport.php');
        $allowedModules = getACLAllowedModules();
        $this->assertTrue(empty($allowedModules['Trackers']));
        $this->assertTrue(empty($allowedModules['TrackerSessions']));
        $this->assertTrue(empty($allowedModules['TrackerPerfs']));
        $this->assertTrue(empty($allowedModules['TrackerQueries']));        
    }
    
    function test_disabled_create_admin_user() {
    	global $current_user;
    	$current_user = $this->adminUser;
    	
        require_once('modules/Reports/SavedReport.php');
        $allowedModules = getACLAllowedModules();
        $this->assertTrue(!empty($allowedModules['Trackers']));
        $this->assertTrue(!empty($allowedModules['TrackerSessions']));
        $this->assertTrue(!empty($allowedModules['TrackerPerfs']));
        $this->assertTrue(!empty($allowedModules['TrackerQueries']));        
    }    
    
    
    function test_disabled_non_admin_user_given_tracker_role() {
    	global $current_user;
    	$current_user = $this->nonAdminUser;
		$result = $GLOBALS['db']->query("SELECT id FROM acl_roles where name='Tracker'");
		$trackerRoleId = $GLOBALS['db']->fetchByAssoc($result);
		if(!empty($trackerRoleId['id'])) {
		   require_once('modules/ACLRoles/ACLRole.php');
		   $role1= new ACLRole();
		   $role1->retrieve($trackerRoleId['id']);
		   $role1->set_relationship('acl_roles_users', array('role_id'=>$role1->id ,'user_id'=>$this->nonAdminUserId), false);
		}

        require_once('modules/Reports/SavedReport.php');
        $allowedModules = getACLAllowedModules();
        $this->assertTrue(!empty($allowedModules['Trackers']));
        $this->assertTrue(!empty($allowedModules['TrackerSessions']));
        $this->assertTrue(!empty($allowedModules['TrackerPerfs']));
        $this->assertTrue(!empty($allowedModules['TrackerQueries']));  		
    }
    
}
	
?>