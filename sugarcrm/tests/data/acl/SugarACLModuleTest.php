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


/* This unit test class covers the ACLs added for extra modules, this does not cover the Users/Employees modules, those are more intense. */
class SugarACLModuleTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $old_current_user = $GLOBALS['current_user'];
        $new_current_user = new SugarBeanAclModuleUserMock();
        $new_current_user->retrieve($old_current_user->id);
        $GLOBALS['current_user'] = $new_current_user;
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function tearDown()
    {
        $this->getAclAction()->clearSessionCache();
        $GLOBALS['current_user']->clearAdminForAllModules();
    }

    public function getAclAction()
    {
        static $aclAction;
        if ( !isset($aclAction) ) {
            $aclAction = new ACLAction();
        }
        return $aclAction;
    }

    public function moduleAccessTestSet()
    {
        return array(
            // Normal users will have full access to the Accounts module, so this is just checking we didn't mess that up.
            array('module'=>'Administration', 'view' => 'admin', 'edit' => 'admin', 'delete' => 'admin', 'acl_module' => 'Accounts'),
            array('module'=>'Accounts',         'view'=>'any'  ,'edit'=>'any'  ,'delete'=>'any'  ),
            array('module'=>'ACLActions',       'view'=>'admin','edit'=>'admin','delete'=>'admin','acl_module'=>'Users'),
            array('module'=>'ACLFields',        'view'=>'admin','edit'=>'admin','delete'=>'admin','acl_module'=>'Users'),
            array('module'=>'ACLRoles',         'view'=>'admin','edit'=>'admin','delete'=>'admin','acl_module'=>'Users'),
            array('module'=>'ContractTypes',    'view'=>'admin','edit'=>'admin','delete'=>'admin'),
            array('module'=>'Currencies',       'view'=>'any'  ,'edit'=>'admin','delete'=>'admin'),
            //BEGIN SUGARCRM flav=ent ONLY
            array('module'=>'CustomQueries',    'view'=>'any'  ,'edit'=>'admin','delete'=>'admin'),
            array('module'=>'DataSets',         'view'=>'any'  ,'edit'=>'admin','delete'=>'admin'),
            //END SUGARCRM flav=ent ONLY
            array('module'=>'Expressions',      'view'=>'admin','edit'=>'admin','delete'=>'admin'),
            array('module'=>'Holidays',         'view'=>'any'  ,'edit'=>'admin','delete'=>'admin','acl_module'=>'Users'),
            array('module'=>'Manufacturers',    'view'=>'any'  ,'edit'=>'admin','delete'=>'admin','acl_module'=>'Products'),
            array('module'=>'OAuthKeys',        'view'=>'admin','edit'=>'admin','delete'=>'admin'),
            array('module'=>'ProductCategories','view'=>'any'  ,'edit'=>'admin','delete'=>'admin','acl_module'=>'Products'),
            array('module'=>'ProductTemplates', 'view'=>'any'  ,'edit'=>'admin','delete'=>'admin','acl_module'=>'Products'),
            array('module'=>'ProductTypes',     'view'=>'any'  ,'edit'=>'admin','delete'=>'admin','acl_module'=>'Products'),
            array('module'=>'Releases',         'view'=>'any'  ,'edit'=>'admin','delete'=>'admin','acl_module'=>'Bugs'),
            array('module'=>'Roles',            'view'=>'any'  ,'edit'=>'admin','delete'=>'admin','acl_module'=>'Users'),
            array('module'=>'Schedulers',       'view'=>'admin','edit'=>'admin','delete'=>'admin'),
            array('module'=>'SchedulersJobs',   'view'=>'admin','edit'=>'admin','delete'=>'admin'),
            array('module'=>'Shippers',         'view'=>'any'  ,'edit'=>'admin','delete'=>'admin','acl_module'=>'Products'),
            array('module'=>'TaxRates',         'view'=>'any'  ,'edit'=>'admin','delete'=>'admin','acl_module'=>'Quotes'),
            array('module'=>'Teams',            'view'=>'any'  ,'edit'=>'admin','delete'=>'admin','acl_module'=>'Users'),
            array('module'=>'TimePeriods',      'view'=>'any'  ,'edit'=>'admin','delete'=>'admin','acl_module'=>'Forecasts'),
        );
    }

    /**
     * Tests a specific setup of ACL's
     * @dataProvider moduleAccessTestSet
     */
    public function testAcl($module, $view, $edit, $delete, $acl_module = '')
    {
        if ( empty($acl_module) ) {
            $acl_module = $module;
        }
        $testBean = BeanFactory::newBean($module);

        // First, no admin, no module admin
        $canView = $testBean->ACLAccess('view');
        if ( $view == 'any' ) {
            $this->assertTrue($canView,"Any user should be able to view.");
        } else {
            $this->assertFalse($canView,"Only admins should be able to view.");
        }

        $canEdit = $testBean->ACLAccess('edit');
        if ( $edit == 'any' ) {
            $this->assertTrue($canEdit,"Any user should be able to edit.");
        } else {
            $this->assertFalse($canEdit,"Only admins should be able to edit.");
        }

        $canDelete = $testBean->ACLAccess('delete');
        if ( $delete == 'any' ) {
            $this->assertTrue($canDelete,"Any user should be able to delete.");
        } else {
            $this->assertFalse($canDelete,"Only admins should be able to delete.");
        }
        
        // Second, is admin, not module admin specifically
        $GLOBALS['current_user']->is_admin = 1;
        $this->getAclAction()->clearSessionCache();
        $canView = $testBean->ACLAccess('view');
        if ( $view == 'any' || $view == 'admin' ) {
            $this->assertTrue($canView,"I am a system admin and I should be able to view.");
        } else {
            $this->assertFalse($canView,"A system admin was denied the abilitiy to view.");
        }

        $canEdit = $testBean->ACLAccess('edit');
        if ( $edit == 'any' || $edit == 'admin' ) {
            $this->assertTrue($canEdit,"I am a system admin and I should be able to edit.");
        } else {
            $this->assertFalse($canEdit,"A system admin was denied the abilitiy to edit.");
        }

        $canDelete = $testBean->ACLAccess('delete');
        if ( $delete == 'any' || $delete == 'admin' ) {
            $this->assertTrue($canDelete,"I am a system admin and I should be able to delete.");
        } else {
            $this->assertFalse($canDelete,"A system admin was denied the abilitiy to delete.");
        }

        // Third, not system admin, but module admin
        $GLOBALS['current_user']->is_admin = 0;
        $GLOBALS['current_user']->setAdminForModule($acl_module);
        $this->getAclAction()->clearSessionCache();

        $canView = $testBean->ACLAccess('view');
        if ( $view == 'any' || $view == 'admin' ) {
            $this->assertTrue($canView,"I am a module admin and I should be able to view.");
        } else {
            $this->assertFalse($canView,"A module admin was denied the abilitiy to view.");
        }

        $canEdit = $testBean->ACLAccess('edit');
        if ( $edit == 'any' || $edit == 'admin' ) {
            $this->assertTrue($canEdit,"I am a module admin and I should be able to edit.");
        } else {
            $this->assertFalse($canEdit,"A module admin was denied the abilitiy to edit.");
        }

        $canDelete = $testBean->ACLAccess('delete');
        if ( $delete == 'any' || $delete == 'admin' ) {
            $this->assertTrue($canDelete,"I am a module admin and I should be able to delete.");
        } else {
            $this->assertFalse($canDelete,"A module admin was denied the abilitiy to delete.");
        }
    }
}

/*
 * Testing ACL's is annoying, it does all these checks on the current user
 * It's tricky to get the current user to cooperate, so we're going to build
 * a mock that will let us set the properties directly.
 */

class SugarBeanAclModuleUserMock extends User
{
    protected $adminForModules = array();

    public function clearAdminForAllModules()
    {
        $this->adminForModules = array();
    }

    public function setAdminForModule($module)
    {
        $this->adminForModules[$module] = true;
    }

    public function isAdminForModule($module)
    {
        if ( $this->isAdmin() ) {
            return true;
        }

        if ( isset($this->adminForModules[$module]) && $this->adminForModules[$module] ) {
            return true;
        } else {
            return false;
        }
    }

    public function getAdminModules() {
        return array_keys($this->adminForModules);
    }

}
