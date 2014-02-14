<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/
/**
 * @ticket 67201
 */
class Bug67201Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $role = null;

    public function setup() {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->role = new ACLRole();
        $this->role->name = 'newrole';
        $this->role->save();

        $aclActions = $this->role->getRoleActions($this->role->id);
        $this->role->setAction($this->role->id, $aclActions['Accounts']['module']['edit']['id'], ACL_ALLOW_NONE);

        $this->role->load_relationship('users');
        $this->role->users->add($GLOBALS['current_user']);
    }

    public function tearDown() {
        $GLOBALS['db']->query("delete from acl_roles_users where role_id = '{$this->role->id}'");
        $GLOBALS['db']->query("delete from acl_roles_actions where role_id = '{$this->role->id}'");
        $GLOBALS['db']->query("delete from acl_roles where id = '{$this->role->id}'");
        SugarTestHelper::tearDown();
    }

    public function testGetUserActions() {
        $actions = ACLAction::getUserActions($GLOBALS['current_user']->id, true);
        $this->assertEquals(ACL_ALLOW_NONE, $actions['Accounts']['module']['edit']['aclaccess'], 'aclaccess should be: '. ACL_ALLOW_NONE);
        $this->assertEquals(false, $actions['Accounts']['module']['edit']['isDefault'], 'aclaccess should be overridden.');
    }
}
