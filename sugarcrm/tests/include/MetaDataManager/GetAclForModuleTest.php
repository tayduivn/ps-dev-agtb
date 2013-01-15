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

require_once 'include/MetaDataManager/MetaDataManager.php';

/**
 * ACL's
 */
class GetAclForModuleTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $this->accounts = array();
        SugarACL::$acls = array();
    }

    public function tearDown()
    {
        foreach($this->accounts AS $account_id) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$account_id}'");
        }
        $GLOBALS['db']->query("DELETE FROM roles WHERE name LIKE 'Unit Test%'");
        $GLOBALS['db']->query("DELETE FROM acl_roles WHERE name LIKE 'Unit Test%'");
        SugarTestHelper::tearDown();
    }

    // test view only

    public function testViewOnly()
    {
        $modules = array('Accounts', );
        // user can view, list, delete, and export
        $expected_result = array(
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'no',
                                'view' => 'yes',
                                'list' => 'no',
                                'edit' => 'no',
                                'delete' => 'no',
                                'import' => 'no',
                                'export' => 'no',
                                'massupdate' => 'no',
                            );

        $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', ));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);



        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }


    // test list only

    public function testListOnly()
    {
        $modules = array('Accounts', );
        // user can view, list, delete, and export
        $expected_result = array(
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'no',
                                'view' => 'no',
                                'list' => 'yes',
                                'edit' => 'no',
                                'delete' => 'no',
                                'import' => 'no',
                                'export' => 'no',
                                'massupdate' => 'no',
                            );

        $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'list', ));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);



        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }

    // test view + list owner
    public function testViewListOwner()
    {
        $modules = array('Accounts', );
        // user can view, list, delete, and export
        $expected_result = array(
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'no',
                                'view' => 'yes',
                                'list' => 'yes',
                                'edit' => 'no',
                                'delete' => 'no',
                                'import' => 'no',
                                'export' => 'no',
                                'massupdate' => 'no',
                            );

        $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'list', 'view'), array('list', 'view'));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);



        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }


    // test create only
    public function testCreateOnly()
    {
        $modules = array('Accounts', );
        // user can view, list, delete, and export
        $expected_result = array(
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'yes',
                                'view' => 'no',
                                'list' => 'no',
                                'edit' => 'no',
                                'delete' => 'no',
                                'import' => 'no',
                                'export' => 'no',
                                'massupdate' => 'no',
                            );

        $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'create', ));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);



        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }
    // test view owner + edit owner + create
    public function testViewEditOwnerCreate()
    {
        $modules = array('Accounts', );
        // user can view, list, delete, and export
        $expected_result = array(
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'yes',
                                'view' => 'yes',
                                'list' => 'no',
                                'edit' => 'yes',
                                'delete' => 'no',
                                'import' => 'no',
                                'export' => 'no',
                                'massupdate' => 'no',
                            );

        $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'create', 'edit', 'view'), array('edit', 'view'));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);



        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }

    // test all access, but admin
    public function testAllButAdmin()
    {
        $modules = array('Accounts', );
        // user can view, list, delete, and export
        $expected_result = array(
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'yes',
                                'view' => 'yes',
                                'list' => 'yes',
                                'edit' => 'yes',
                                'delete' => 'yes',
                                'import' => 'yes',
                                'export' => 'yes',
                                'massupdate' => 'yes',
                            );

        $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'create', 'view', 'list', 'edit', 'delete', 'import', 'export', 'massupdate', ));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);



        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }

    // test field level
    // test read only all fields
    // test read only 1 field
    public function testReadOnlyOneField()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
                                'fields' =>
                                    array(
                                            'website' => array(
                                                        'write' => 'no',
                                                        'create' => 'no',
                                                ),
                                        ),
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'yes',
                                'view' => 'yes',
                                'list' => 'yes',
                                'edit' => 'yes',
                                'delete' => 'yes',
                                'import' => 'yes',
                                'export' => 'yes',
                                'massupdate' => 'yes',
                            );

        $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'create', 'view', 'list', 'edit','delete','import', 'export', 'massupdate'));

        $aclField = new ACLField();
        $aclField->setAccessControl('Accounts', $role->id, 'website', 50);
        ACLField::loadUserFields('Accounts', 'Account', $GLOBALS['current_user']->id, true );

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);


        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }    
    // test create only 1 field
    // test read only 1 field
    public function testCreateOnlyOneField()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
                                'fields' =>
                                    array(
                                            'website' => array(
                                                        'write' => 'no',
                                                        'read' => 'no',
                                                ),
                                        ),
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'yes',
                                'view' => 'yes',
                                'list' => 'yes',
                                'edit' => 'yes',
                                'delete' => 'yes',
                                'import' => 'yes',
                                'export' => 'yes',
                                'massupdate' => 'yes',
                            );

        $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'create', 'view', 'list', 'edit','delete','import', 'export', 'massupdate'));

        $aclField = new ACLField();
        $aclField->setAccessControl('Accounts', $role->id, 'website', 10);
        ACLField::loadUserFields('Accounts', 'Account', $GLOBALS['current_user']->id, true );

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);


        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }  

    // test owner write 1 field
    public function testReadOwnerWriteOneField()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
                                'fields' =>
                                    array(
                                        ),
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'yes',
                                'view' => 'yes',
                                'list' => 'yes',
                                'edit' => 'yes',
                                'delete' => 'yes',
                                'import' => 'yes',
                                'export' => 'yes',
                                'massupdate' => 'yes',
                            );

        $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'create', 'view', 'list', 'edit','delete','import', 'export', 'massupdate'));

        $aclField = new ACLField();
        $aclField->setAccessControl('Accounts', $role->id, 'website', 60);
        ACLField::loadUserFields('Accounts', 'Account', $GLOBALS['current_user']->id, true );

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);


        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }       

    // test owner read/owner write 1 field
    public function testOwnerReadOwnerWriteOneField()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
                                'fields' =>
                                    array(
                                        ),
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'yes',
                                'view' => 'yes',
                                'list' => 'yes',
                                'edit' => 'yes',
                                'delete' => 'yes',
                                'import' => 'yes',
                                'export' => 'yes',
                                'massupdate' => 'yes',
                            );

        $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'create', 'view', 'list', 'edit','delete','import', 'export', 'massupdate'));

        $aclField = new ACLField();
        $aclField->setAccessControl('Accounts', $role->id, 'website', 40);
        ACLField::loadUserFields('Accounts', 'Account', $GLOBALS['current_user']->id, true );

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);


        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }    

    protected function createRole($name, $allowedModules, $allowedActions, $ownerActions = array()) {
        $role = new ACLRole();
        $role->name = $name;
        $role->description = $name;
        $role->save();
        $GLOBALS['db']->commit();

        $roleActions = $role->getRoleActions($role->id);
        foreach ($roleActions as $moduleName => $actions) {
            // enable allowed modules
            if (isset($actions['module']['access']['id']) && !in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_DISABLED);
            } elseif (isset($actions['module']['access']['id']) && in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
            } else {
                foreach ($actions as $action => $actionName) {
                    if (isset($actions[$action]['access']['id'])) {
                        $role->setAction($role->id, $actions[$action]['access']['id'], ACL_ALLOW_DISABLED);
                    }
                }
            }

            if (in_array($moduleName, $allowedModules)) {
                foreach ($actions['module'] as $actionName => $action) {
                    if(in_array($actionName, $allowedActions) && in_array($actionName, $ownerActions)) {
                        $aclAllow = ACL_ALLOW_OWNER;
                    }
                    elseif (in_array($actionName, $allowedActions)) {
                        $aclAllow = ACL_ALLOW_ALL;
                    } else {
                        $aclAllow = ACL_ALLOW_NONE;
                    }

                    $role->setAction($role->id, $action['id'], $aclAllow);
                }
            }

        }
        return $role;
    }


}
