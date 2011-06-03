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
require_once 'install/install_utils.php';

class MultiLevelAdminTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_role_id;
    
    public function setup()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $this->_role_id = null;
        $beanList = $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }
    
    public function tearDown()
    {
        if ( !empty($this->_role_id) ) {
            $GLOBALS['db']->query('DELETE FROM acl_roles_users WHERE role_id =\''.$this->_role_id.'\'');
            $GLOBALS['db']->query('DELETE FROM acl_roles WHERE id =\''.$this->_role_id.'\'');
            $GLOBALS['db']->query('DELETE FROM acl_roles_actions WHERE role_id =\''.$this->_role_id.'\'');
        }
        
        if ( isset($GLOBALS['current_user']) )
            unset($GLOBALS['current_user']);
        
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }
    
    public function testAdminUserIsAdminForTheGivenModule()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = 1;
        $module = 'Accounts';
        
        $this->assertTrue(is_admin_for_module($user, $module, array('Accounts')));  
    }
    
    public function testCurrentUserIsAdminForTheGivenModuleIfTheyAreAdminAndDev()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = 0;
        $mlaRoles = array(
            'test_for_module'=>array(
                'Accounts'=>array('admin'=>100),
                )
            );
        addDefaultRoles($mlaRoles); 
        
        $user->role_id = $GLOBALS['db']->getOne("SELECT id FROM acl_roles WHERE name='test_for_module'");
        $GLOBALS['db']->query("INSERT into acl_roles_users(id,user_id,role_id) values('".create_guid()."','".$user->id."','".$user->role_id."')");
        $this->_role_id = $user->role_id;
        
        $module = 'Accounts';
        $actions = array();
        $actions[$module]['module']['admin']['aclaccess'] = 96;
        
        unset($_SESSION['MLA_'.$user->user_name]);
        
        $this->assertTrue(is_admin_for_module($user, $module, $actions));
    }
    
    /**
     * @ticket 33494
     */
    public function testCurrentUserIsAdminForTheGivenModuleIfTheyAreOnlyAdmin()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = 0;
        $mlaRoles = array(
            'test_for_module'=>array(
                'Accounts'=>array('admin'=>99),
                )
            );
        addDefaultRoles($mlaRoles); 
        
        $user->role_id = $GLOBALS['db']->getOne("SELECT id FROM acl_roles WHERE name='test_for_module'");
        $GLOBALS['db']->query("INSERT into acl_roles_users(id,user_id,role_id) values('".create_guid()."','".$user->id."','".$user->role_id."')");
        $this->_role_id = $user->role_id;
        
        $module = 'Accounts';
        $actions = array();
        $actions[$module]['module']['admin']['aclaccess'] = 96;
        
        unset($_SESSION['MLA_'.$user->user_name]);
        
        $this->assertTrue(is_admin_for_module($user, $module, $actions));
    }
    
    public function testCurrentUserIsAdminForTheGivenModuleIfTheyAreOnlyDev()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = 0;
        $mlaRoles = array(
            'test_for_module'=>array(
                'Accounts'=>array('admin'=>95),
                )
            );
        addDefaultRoles($mlaRoles); 
        
        $user->role_id = $GLOBALS['db']->getOne("SELECT id FROM acl_roles WHERE name='test_for_module'");
        $GLOBALS['db']->query("INSERT into acl_roles_users(id,user_id,role_id) values('".create_guid()."','".$user->id."','".$user->role_id."')");
        $this->_role_id = $user->role_id;
        
        $module = 'Accounts';
        $actions = array();
        $actions[$module]['module']['admin']['aclaccess'] = 96;
        
        unset($_SESSION['MLA_'.$user->user_name]);
        
        $this->assertTrue(is_admin_for_module($user, $module, $actions));
    }
    
    public function testCurrentUserIsAdminForAnyModule()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = 0;      
        $mlaRoles = array(
             'Sales Administrator'=>array(
                 'Accounts'=>array('admin'=>95),
                 'Contacts'=>array('admin'=>95),
                 'Forecasts'=>array('admin'=>95),
                 'ForecastSchedule'=>array('admin'=>95),
                 'Leads'=>array('admin'=>95),
                 'Opportunities'=>array('admin'=>95),
                 'Quotes'=>array('admin'=>95),
                 'TrackerPerfs'=>array('admin'=>1),
                 'TrackerQueries'=>array('admin'=>1),
                 'Trackers'=>array('admin'=>1),
                 'TrackerSessions'=>array('admin'=>1),
                 )
            );
        
        addDefaultRoles($mlaRoles); 
                 
        $user->role_id = $GLOBALS['db']->getOne("SELECT id FROM acl_roles WHERE name='Sales Administrator'");
        $GLOBALS['db']->query("INSERT into acl_roles_users(id,user_id,role_id) values('".create_guid()."','".$user->id."','".$user->role_id."')");
        $this->_role_id = $user->role_id;
        
        unset($_SESSION['is_admin_for_module']);
        
        $this->assertTrue(is_admin_for_any_module($user));
    }
    
    public function testCurrentUserIsAdminForAnyModuleWhenSessionVarIsSet()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $_SESSION['is_admin_for_module'] = true;
        
        $check = is_admin_for_any_module($user);
        
        unset($_SESSION['is_admin_for_module']);
        
        $this->assertTrue($check);
    }
    
    public function testCurrentUserIsNotAdminForAnyModule()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = 0;       
        $mlaRoles = array(
             'test1'=>array(
                 'Accounts'=>array('admin'=>1),
                 'Contacts'=>array('admin'=>1),
                 'Campaigns'=>array('admin'=>1),
                 'ProspectLists'=>array('admin'=>1),
                 'Leads'=>array('admin'=>1),
                 'Prospects'=>array('admin'=>1),
                 'TrackerPerfs'=>array('admin'=>1),
                 'TrackerQueries'=>array('admin'=>1),
                 'Trackers'=>array('admin'=>1),
                 'TrackerSessions'=>array('admin'=>1),
             )
        );
        
        addDefaultRoles($mlaRoles); 
                 
        $user->role_id = $GLOBALS['db']->getOne("SELECT id FROM acl_roles WHERE name='test1'");
        $GLOBALS['db']->query("INSERT into acl_roles_users(id,user_id,role_id) values('".create_guid()."','".$user->id."','".$user->role_id."')");
        $this->_role_id = $user->role_id;
        
        unset($_SESSION['is_admin_for_module']);
        
        $this->assertFalse(is_admin_for_any_module($user));
    }
    
    public function testGetAdminModulesForCurrentUserIfTheyAreAdminOfAModule()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = 0;       
        $mlaRoles = array(
             'test4'=>array(
                 'Accounts'=>array('admin'=>1),
                 'Contacts'=>array('admin'=>95),
                 'Campaigns'=>array('admin'=>1),
                 'ProspectLists'=>array('admin'=>1),
                 'Leads'=>array('admin'=>1),
                 'Prospects'=>array('admin'=>1),
                 'TrackerPerfs'=>array('admin'=>1),
                 'TrackerQueries'=>array('admin'=>1),
                 'Trackers'=>array('admin'=>1),
                 'TrackerSessions'=>array('admin'=>1),
             )
        );
        
        addDefaultRoles($mlaRoles); 
                 
        $user->role_id = $GLOBALS['db']->getOne("SELECT id FROM acl_roles WHERE name='test4'");
        $GLOBALS['db']->query("INSERT into acl_roles_users(id,user_id,role_id) values('".create_guid()."','".$user->id."','".$user->role_id."')");
        $this->_role_id = $user->role_id;
        
        unset($_SESSION['get_admin_modules_for_user']);
        
        $this->assertEquals(count(get_admin_modules_for_user($user)),1);
    }
    
    public function testGetAdminModulesForCurrentUserIfTheyAreNotAdminOfAnyModules()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = 0;       
        $mlaRoles = array(
             'test5'=>array(
                 'Accounts'=>array('admin'=>1),
                 'Contacts'=>array('admin'=>1),
                 'Campaigns'=>array('admin'=>1),
                 'ProspectLists'=>array('admin'=>1),
                 'Leads'=>array('admin'=>1),
                 'Prospects'=>array('admin'=>1),
                 'TrackerPerfs'=>array('admin'=>1),
                 'TrackerQueries'=>array('admin'=>1),
                 'Trackers'=>array('admin'=>1),
                 'TrackerSessions'=>array('admin'=>1),
             )
        );
        addDefaultRoles($mlaRoles); 
                 
        $user->role_id = $GLOBALS['db']->getOne("SELECT id FROM acl_roles WHERE name='test5'");
        $GLOBALS['db']->query("INSERT into acl_roles_users(id,user_id,role_id) values('".create_guid()."','".$user->id."','".$user->role_id."')");
        $this->_role_id = $user->role_id;
        
        unset($_SESSION['get_admin_modules_for_user']);
        
        $this->assertEquals(count(get_admin_modules_for_user($user)),0);
    }
    
    public function testGetAdminModulesWhenNoUserIsPassed()
    {
        $this->assertEquals(array(),get_admin_modules_for_user(false));
    }
    
    public function testGetAdminModulesForCurrentUserIfSessionVarIsSet()
    {
        $_SESSION['get_admin_modules_for_user'] = array('dog','cat');
        $user = SugarTestUserUtilities::createAnonymousUser();
        
        $modules = get_admin_modules_for_user($user);
        
        $this->assertEquals(array('dog','cat'),$modules);
    }
    
    public function testCanDisplayStudioForCurrentUserThatDoesNotHaveDeveloperAccessToAStudioModule()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 0;       
        $mlaRoles = array(
             'test6'=>array(
                 'Accounts'=>array('admin'=>1),
                 'Contacts'=>array('admin'=>1),
                 'Campaigns'=>array('admin'=>1),
                 'Forecasts'=>array('admin'=>1),
                 'ForecastSchedule'=>array('admin'=>95),        
                 'ProspectLists'=>array('admin'=>1),
                 'Leads'=>array('admin'=>1),
                 'Prospects'=>array('admin'=>1),
                 'TrackerPerfs'=>array('admin'=>1),
                 'TrackerQueries'=>array('admin'=>1),
                 'Trackers'=>array('admin'=>1),
                 'TrackerSessions'=>array('admin'=>1),
             )
        );
        addDefaultRoles($mlaRoles); 
                 
        $GLOBALS['current_user']->role_id = $GLOBALS['db']->getOne("SELECT id FROM acl_roles WHERE name='test6'");
        $GLOBALS['db']->query("INSERT into acl_roles_users(id,user_id,role_id) values('".create_guid()."','".$GLOBALS['current_user']->id."','".$GLOBALS['current_user']->role_id."')");
        $this->_role_id = $GLOBALS['current_user']->role_id;
        
        unset($_SESSION['display_studio_for_user']);
        unset($_SESSION['get_admin_modules_for_user']);

        $this->assertFalse(displayStudioForCurrentUser());
    }
    
    public function testCanDisplayStudioForCurrentUserThatDoesHaveDeveloperAccessToAStudioModule()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 0;       
        $mlaRoles = array(
             'test7'=>array(
                 'Accounts'=>array('admin'=>1),
                 'Contacts'=>array('admin'=>95),
                 'Campaigns'=>array('admin'=>95),
                 'Forecasts'=>array('admin'=>1),
                 'ForecastSchedule'=>array('admin'=>95),        
                 'ProspectLists'=>array('admin'=>1),
                 'Leads'=>array('admin'=>1),
                 'Prospects'=>array('admin'=>1),
                 'TrackerPerfs'=>array('admin'=>1),
                 'TrackerQueries'=>array('admin'=>1),
                 'Trackers'=>array('admin'=>1),
                 'TrackerSessions'=>array('admin'=>1),
             )
        );
        addDefaultRoles($mlaRoles); 
                 
        $GLOBALS['current_user']->role_id = $GLOBALS['db']->getOne("SELECT id FROM acl_roles WHERE name='test7'");
        $GLOBALS['db']->query("INSERT into acl_roles_users(id,user_id,role_id) values('".create_guid()."','".$GLOBALS['current_user']->id."','".$GLOBALS['current_user']->role_id."')");
        $this->_role_id = $GLOBALS['current_user']->role_id;
        
        unset($_SESSION['display_studio_for_user']);
        unset($_SESSION['get_admin_modules_for_user']);

        $this->assertTrue(displayStudioForCurrentUser());
    }
    
    public function testCanDisplayStudioForCurrentUserIfTheyAreAnAdminUser()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;
        
        $this->assertTrue(displayStudioForCurrentUser());
    }
    
    public function testCanDisplayStudioForIfSessionVarIsSet()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 0;
        
        $_SESSION['display_studio_for_user'] = true;
        
        $check = displayStudioForCurrentUser();
        
        unset($_SESSION['display_studio_for_user']);
        
        $this->assertTrue($check);
    }
}
