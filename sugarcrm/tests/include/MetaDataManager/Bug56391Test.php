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
 * Bug 56391 - ACL's used in the MetadataManager were the static ones.  Have switched to use the SugarACL methods
 */
class Bug56391Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $roles = array();
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

        foreach($this->roles AS $role) {
            $role->mark_deleted($role->id);
            $role->mark_relationships_deleted($role->id);
            $GLOBALS['db']->query("DELETE FROM acl_fields WHERE role_id = '{$role->id}'");
        }
        unset($_SESSION['ACL']);
        
        SugarTestHelper::tearDown();
    }

    /**
     * Test Users Module
     *
     * @group Bug56391
     */
    public function testUsersModule()
    {
        $mm = MetaDataManager::getManagerNew();
        // because the user is not an admin the user should only have view and list access
        unset($_SESSION['ACL']);
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
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_result, $acls);

    }


    /**
     * Test Users Module Fields
     *
     * @group Bug56391
     */
    public function testUsersModuleFields()
    {
        $mm = new MetaDataManager($GLOBALS['current_user']);
        // because the user is not an admin the user should only have view and list access
        unset($_SESSION['ACL']);
        $expected_result = array(
                                    'user_name' => array('write' => 'no', 'create' => 'no'),
                                    'user_hash' => array('read' => 'no', 'write' => 'no', 'create' => 'no', ),
                                    'system_generated_password' => array('read' => 'no', 'write' => 'no', 'create' => 'no',),
                                    'pwd_last_changed' => array('read' => 'no', 'write' => 'no', 'create' => 'no',),
                                    'authenticate_id' => array('read' => 'no', 'write' => 'no', 'create' => 'no', ),
                                    'sugar_login' => array('read' => 'no', 'write' => 'no', 'create' => 'no', ),
                                    'external_auth_only' => array('read' => 'no', 'write' => 'no', 'create' => 'no', ),
                                    'status' => array('write' => 'no', 'create' => 'no'),
                                    'show_on_employees' => array('read' => 'no', 'write' => 'no', 'create' => 'no'),
                                    'portal_only' => array('read' => 'no', 'write' => 'no', 'create' => 'no',),
                                    'employee_status' => array('write' => 'no', 'create' => 'no'),
                                    'is_group' => array('read' => 'no', 'write' => 'no', 'create' => 'no', ),
                                    'title' => array( 'write' => 'no', 'create' => 'no', ),
                                    'department' => array( 'write' => 'no', 'create' => 'no', ),
                                    'reports_to_id' => array( 'write' => 'no', 'create' => 'no', ),
                                    'reports_to_name' => array( 'write' => 'no', 'create' => 'no', ),
                                    'reports_to_link' => array( 'write' => 'no', 'create' => 'no', ),
                                    'is_admin' => array( 'read' => 'no', 'write' => 'no', 'create' => 'no',  ),
                                );
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        $acls = $acls['fields'];

        $this->assertEquals($expected_result, $acls);

    }    

    /**
     * Test Users Module as Admin
     *
     * @group Bug56391
     */
    public function testUsersAsAdminModule()
    {
        // set current user as an admin
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();
        unset($_SESSION['ACL']);
        $mm = MetaDataManager::getManagerNew();
        // because the user is not an admin the user should only have view and list access

        $expected_result = array(
                                    'access' => 'yes',
                                    'admin' => 'yes',
                                    'create' => 'yes',
                                    'view' => 'yes',
                                    'list' => 'yes',
                                    'edit' => 'yes',
                                    'delete' => 'yes',
                                    'import' => 'yes',
                                    'export' => 'yes',
                                    'massupdate' => 'yes',
                                );
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_result, $acls);

        // remove admin
        $GLOBALS['current_user']->is_admin = 0;
        $GLOBALS['current_user']->save();
    }

    /**
     * Test Users Module as Admin
     *
     * @group Bug56391
     */
    public function testUsersAsAdminModuleForSelf()
    {
        // set current user as an admin
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();
        unset($_SESSION['ACL']);
        $mm = MetaDataManager::getManagerNew();
        // because the user is not an admin the user should only have view and list access

        $expected_result = array(
                                    'access' => 'yes',
                                    'admin' => 'yes',
                                    'create' => 'yes',
                                    'view' => 'yes',
                                    'list' => 'yes',
                                    'edit' => 'yes',
                                    'delete' => 'no',
                                    'import' => 'yes',
                                    'export' => 'yes',
                                    'massupdate' => 'yes',
                                );
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user'], $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_result, $acls);

        // remove admin
        $GLOBALS['current_user']->is_admin = 0;
        $GLOBALS['current_user']->save();
    }

    /**
     * Test Module Access
     *
     * Set 5 modules to have specific actions and verify them
     *
     * @group Bug56391
     */
    public function testModuleAccess()
    {
        $modules = array('Accounts', 'Contacts', 'Contracts', 'Opportunities', 'Leads');
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
                                'export' => 'yes',
                                'massupdate' => 'no',
                            );

        $this->roles[] = $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', 'list', 'export'));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);



        $mm = MetaDataManager::getManagerNew();
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }


    /**
     * Test Field Access
     *
     * Set a field on accounts to be not readable, writeable, or editable
     *
     * @group Bug56391
     */
    public function testFieldAccess()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
                                'fields' =>
                                    array(
                                            'website' => array(
                                                        'read' => 'no',
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
                                'delete' => 'no',
                                'import' => 'yes',
                                'export' => 'yes',
                                'massupdate' => 'yes',
                            );

        $this->roles[] = $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'create', 'view', 'list', 'edit','import', 'export', 'massupdate'));

        $aclField = new ACLField();
        $aclField->setAccessControl('Accounts', $role->id, 'website', -99);

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);
        ACLField::loadUserFields('Accounts', 'Account', $GLOBALS['current_user']->id, true );


        $mm = MetaDataManager::getManagerNew();
        foreach($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }

   /**
     * Test Owner Access
     *
     * Test if Edit = Owner that we can not edit a bean that is not owned by the current user.
     *
     * @group Bug56391
     */
    public function testModuleOwnerAccess()
    {
        $modules = array('Accounts', );


        $expected_bean_result['access'] = array(
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'no',
                                'view' => 'yes',
                                'list' => 'yes',
                                'edit' => 'yes',
                                'delete' => 'yes',
                                'import' => 'no',
                                'export' => 'yes',
                                'massupdate' => 'no',
            );



        $this->roles[] = $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', 'list', 'edit', 'delete', 'export'), array('edit', 'delete'));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Unit Test ' . create_guid();
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->save();
        $this->accounts['access'] = $account->id;

        unset($account);

        $mm = MetaDataManager::getManagerNew();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::getBean('Accounts', $this->accounts['access']));
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['access'], $acls, 'Access Failed');
    }

  /**
     * Test Owner Create Access
     *
     * Test if Create = Owner that we can create a bean.
     *
     * @group Bug56391
     */
    public function testModuleOwnerCreateAccess()
    {
        $modules = array('Accounts', );


        $expected_bean_result['access'] = array(
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'no',
                                'view' => 'yes',
                                'list' => 'yes',
                                'edit' => 'yes',
                                'delete' => 'yes',
                                'import' => 'no',
                                'export' => 'yes',
                                'massupdate' => 'no',
            );



        $this->roles[] = $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', 'list', 'edit', 'delete', 'export'), array('create', 'edit'));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);

        $mm = MetaDataManager::getManagerNew();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::newBean('Accounts'));
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['access'], $acls, 'Access Failed');
    }

 /**
     * Test Owner Create Access 2
     *
     * Test if Create = Owner that we can create a bean.
     *
     * @group Bug56391
     */
    public function testModuleOwnerCreateNewWithIdAccess()
    {
        $modules = array('Accounts', );


        $expected_bean_result['access'] = array(
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'no',
                                'view' => 'yes',
                                'list' => 'yes',
                                'edit' => 'yes',
                                'delete' => 'yes',
                                'import' => 'no',
                                'export' => 'yes',
                                'massupdate' => 'no',
            );



        $this->roles[] = $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', 'list', 'edit', 'delete', 'export'), array('create', 'edit'));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);

        $account = BeanFactory::newBean('Accounts');
        $account->new_with_id = true;
        $account->id = create_guid();
        $account->name = "Tis Awesome";

        $mm = MetaDataManager::getManagerNew();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], $account);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['access'], $acls, 'Access Failed');
    }

    public function testModuleOwnerNoAccess() {
        $modules = array('Accounts', );

        $expected_bean_result['no_access'] = array(
                                'access' => 'yes',
                                'admin' => 'no',
                                'create' => 'no',
                                'view' => 'yes',
                                'list' => 'yes',
                                'edit' => 'no',
                                'delete' => 'no',
                                'import' => 'no',
                                'export' => 'yes',
                                'massupdate' => 'no',
            );

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Unit Test ' . create_guid();
        $account->assigned_user_id = 1;
        $account->save();
        $this->accounts['no_access'] = $account->id;

        unset($account);
        $this->roles[] = $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', 'list', 'edit', 'export', 'create'), array('edit','create'));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);

        $mm = MetaDataManager::getManagerNew();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::getBean('Accounts', $this->accounts['no_access']));

        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['no_access'], $acls, 'No Access Failed');

    }

    public function testModuleFieldOwnerAccess() {
        $modules = array('Accounts', );

        $expected_bean_result['field_access'] = array(
            );



        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Unit Test ' . create_guid();
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->save();
        $this->accounts['access'] = $account->id;

        unset($account);

        $this->roles[] = $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', 'list', 'edit', 'delete', 'export'), array('edit'));

        // set the name field as Read Only
        $aclField = new ACLField();
        $aclField->setAccessControl('Accounts', $role->id, 'name', ACL_READ_OWNER_WRITE);
        ACLField::loadUserFields('Accounts', 'Account', $GLOBALS['current_user']->id, true );

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);

        $mm = MetaDataManager::getManagerNew();


        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::getBean('Accounts', $this->accounts['access']));
        unset($acls['_hash']);

        $fields = $acls['fields'];
        unset($acls['fields']);
        $this->assertEquals($expected_bean_result['field_access'], $fields, 'Field Access Failed');

    }

    public function testModuleFieldOwnerNoAccess() {
        $modules = array('Accounts', );

        $expected_bean_result['field_no_access'] = array(
                'name' => array(
                        'write' => 'no',
                        'create' => 'no',
                    ),
            );
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Unit Test ' . create_guid();
        $account->assigned_user_id = 1;
        $account->save();
        $this->accounts['no_access'] = $account->id;

        unset($account);
        $this->roles[] = $role = $this->createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', 'list', 'edit', 'delete', 'export'), array('edit'));

        // set the name field as Read Only
        $aclField = new ACLField();
        $aclField->setAccessControl('Accounts', $role->id, 'name', 60);


        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }
        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);
        unset($_SESSION['ACL']);
        ACLField::loadUserFields('Accounts', 'Account', $GLOBALS['current_user']->id, true );


        $mm = MetaDataManager::getManagerNew();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::getBean('Accounts', $this->accounts['no_access']));
        unset($acls['_hash']);

        $fields = $acls['fields'];
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['field_no_access'], $fields, 'No Field Access Failed');

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
