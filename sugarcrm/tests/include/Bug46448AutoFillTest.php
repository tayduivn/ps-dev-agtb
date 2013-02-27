<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


class Bug46448AutoFillTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $user;
    private $aclRolesIds = array();
    private $aclRoles2Users = array();

    public function testAutoFill()
    {
        $Account = new Account();
        populateFromPost('', $Account);
        $this->assertEquals($Account->assigned_user_id, $this->user->id);
        $this->assertEquals($Account->team_id, $this->user->default_team);
    }

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        require_once 'include/formbase.php';
        SugarTestHelper::setUp('current_user', array(true));
        $user = $GLOBALS['current_user'];
        $this->user = $user;

        $aclFields = array(
            array('module' => 'Accounts', 'name' => 'assigned_user_name', 'access' => ACL_READ_ONLY),
            array('module' => 'Accounts', 'name' => 'team_name', 'access' => ACL_READ_ONLY),
        );
        $role = $this->createAclRole($aclFields);
        $this->connectAclRoles2Users($role, $user);
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        $this->removeAllCreatedAclRoles();
        $this->removeAllConnectAclRoles2Users();
    }

    private function createAclRole($fields = array())
    {
        $AclRole = new ACLRole();

        $time = mt_rand();
        $roleId = 'SugarACLRole';

        $AclRole->name = $roleId . $time;
        $AclRole->description = $roleId . $time;
        $AclRole->modified_user_id = 1;
        $AclRole->created_by = 1;
        $AclRole->date_entered = $time;
        $AclRole->date_modified = $time;
        $AclRole->save();

        $this->aclRolesIds[] = $AclRole->id;

        foreach ($fields AS $fld) {
            ACLField::setAccessControl($fld['module'], $AclRole->id, $fld['name'], $fld['access']);
        }

        return $AclRole;
    }

    private function removeAllCreatedAclRoles()
    {
        if (is_array($this->aclRolesIds) && count($this->aclRolesIds)) {
            $AclRole = new ACLRole();
            $qr = 'DELETE FROM ' . $AclRole->table_name
                . ' WHERE id IN (\'' . implode("', '", $this->aclRolesIds) . '\')';
            $GLOBALS['db']->query($qr);

            $ACLField = new ACLField();
            $qr = 'DELETE FROM ' . $ACLField->table_name
                . ' WHERE role_id IN (\'' . implode("', '", $this->aclRolesIds) . '\')';
            $GLOBALS['db']->query($qr);
        }
    }

    private function connectAclRoles2Users($AclRole, $User = null)
    {
        $userId = null;
        if (is_null($User)) {
            $userId = $GLOBALS['current_user'];
        } elseif ($User instanceof User) {
            $userId = $User->id;
        } elseif (is_scalar($User)) {
            $userId = $User;
        } else {
            throw new Exception('Unsupported User');
        }

        $aclRoleId = null;
        if ($AclRole instanceof ACLRole) {
            $aclRoleId = $AclRole->id;
        } elseif (is_scalar($AclRole)) {
            $aclRoleId = $User;
        } else {
            throw new Exception('Unsupported AclRole');
        }

        $id = create_guid();
        $insQR = "INSERT into acl_roles_users(id,user_id,role_id, date_modified) values('" . $id . "','" . $userId . "','" . $aclRoleId . "', " . $GLOBALS['db']->convert("'" . $GLOBALS['timedate']->nowDb() . "'", 'datetime') . ")";
        $GLOBALS['db']->query($insQR);
        $this->aclRoles2Users[] = $id;

        return $id;
    }

    private function removeAllConnectAclRoles2Users()
    {
        if (is_array($this->aclRoles2Users) && count($this->aclRoles2Users)) {
            $qr = 'DELETE FROM acl_roles_users WHERE id IN (\'' . implode("', '", $this->aclRoles2Users) . '\')';
            $GLOBALS['db']->query($qr);
            // var_dump($qr);
        }
    }

}

?>