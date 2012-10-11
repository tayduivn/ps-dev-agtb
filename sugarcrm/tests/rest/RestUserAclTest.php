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

require_once('tests/rest/RestTestBase.php');

class RestUserAclTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        global $db;

        if ( !empty($this->aclRole) ) {
            $db->query("DELETE FROM acl_roles_actions WHERE role_id = '{$this->aclRole->id}'");
            $db->query("DELETE FROM acl_roles_users WHERE role_id = '{$this->aclRole->id}'");
            $db->query("DELETE FROM acl_fields WHERE role_id = '{$this->aclRole->id}'");
            $db->query("DELETE FROM acl_roles WHERE id = '{$this->aclRole->id}'");
            $db->commit();
        }
        
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testUserAclBasic() {
        $restReply = $this->_restCall("me");
        $this->assertTrue(isset($restReply['reply']['current_user']['acl']['Accounts']['_hash']),'Accounts module is missing.');
    }

    /**
     * @group rest
     */
    public function testUserAclMultiUser() {
        $restReply = $this->_restCall("me");
        $this->assertTrue(isset($restReply['reply']['current_user']['acl']['Accounts']['_hash']),'Accounts module is missing in the first run');
        $oldMd5 = md5(serialize($restReply['reply']['current_user']['acl']));
        
        // Remove users and then create a brand new user w/brand new auth token, etc.
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->commit();
        $GLOBALS['current_user'] = $this->_user = SugarTestUserUtilities::createAnonymousUser();
        // Mark a user as an admin so that the ACL's change
        $this->_user->is_admin = true;
        $this->_user->save();
        unset($this->authToken);
        $this->commit();
        // Login to re-establish required $this->authToken, etc.
        $this->_restLogin($this->_user->user_name,$this->_user->user_name);

        // Call end point and ensure that the md5's are different between old/new user's acls
        $restReply = $this->_restCall("me");
        $this->assertTrue(isset($restReply['reply']['current_user']['acl']['Accounts']['_hash']),'Accounts module is missing in the second run');
        $newMd5 = md5(serialize($restReply['reply']['current_user']['acl']));
        $this->assertNotEquals($oldMd5,$newMd5,"The md5's of the old and new ACL's are the same.");
        $this->assertEquals('yes',$restReply['reply']['current_user']['acl']['Accounts']['admin'],"User is an admin, but doesn't have admin ACL access.");
    }

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * @group rest
     */
    public function testUserAclField() {
        $this->aclRole = new ACLRole();
        $this->aclRole->name = "Unit Test";
        $this->aclRole->save();
        $this->commit(); //We'll be commiting to DB between each step below

        // Test ACL_ALLOW_NONE rule is working properly
        $this->aclRole->set_relationship('acl_roles_users', array('role_id'=>$this->aclRole->id ,'user_id'=> $this->_user->id), false);
        $this->commit();
        $this->aclField = new ACLField();
        $this->aclField->setAccessControl('Accounts', $this->aclRole->id, 'website', ACL_ALLOW_NONE);
        $this->commit();
        $this->aclField->loadUserFields('Accounts', 'Account', $this->_user->id, true );
        $this->commit();
        // Need to re-login so it fetches a new set of ACL's
        $this->_restLogin($this->_user->user_name,$this->_user->user_name);
        $restReply = $this->_restCall("me");
        $this->assertTrue(isset($restReply['reply']['current_user']['acl']['Accounts']['_hash']),'Accounts module is missing.');
        $this->assertEquals('no',$restReply['reply']['current_user']['acl']['Accounts']['fields']['website']['read']);
        $this->assertEquals('no',$restReply['reply']['current_user']['acl']['Accounts']['fields']['website']['write']);

        // Test ACL_OWNER_READ_WRITE rule is working properly
        $this->aclField->setAccessControl('Accounts', $this->aclRole->id, 'website', ACL_OWNER_READ_WRITE);
        $this->commit();
        $this->aclField->loadUserFields('Accounts', 'Account', $this->_user->id, true );
        $this->commit();
        $this->_restLogin($this->_user->user_name,$this->_user->user_name);
        $restReply = $this->_restCall("me");
        $this->assertTrue(isset($restReply['reply']['current_user']['acl']['Accounts']['_hash']),'Accounts module is missing.');
        $this->assertEquals('owner',$restReply['reply']['current_user']['acl']['Accounts']['fields']['website']['read']);
        $this->assertEquals('owner',$restReply['reply']['current_user']['acl']['Accounts']['fields']['website']['write']);
    }

    //END SUGARCRM flav=pro ONLY
 
    /**
     * @group rest
     */
    public function testUserAclModule() {
        $this->aclRole = new ACLRole();
        $this->aclRole->name = "Unit Test";
        $this->aclRole->save();
        $this->commit(); //We'll be commiting to DB between each step below

        // Test set action to ACL_ALLOW_OWNER for a particular module results in reflected acls for our user 
        $this->aclRole->set_relationship('acl_roles_users', array('role_id'=>$this->aclRole->id ,'user_id'=> $this->_user->id), false);
        $this->commit(); 
        // Find action id for Accounts edit
        $ret = $GLOBALS['db']->query("SELECT id FROM acl_actions WHERE category = 'Cases' AND name = 'edit'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->aclRole->setAction($this->aclRole->id,$row['id'],ACL_ALLOW_OWNER);
        $this->commit();

        // Directly access the user acls and ensure that user can edit on Cases as we've allowed owner edit perms.
        $ownerCanEditCases = (ACLAction::getUserAccessLevel($this->_user->id, 'Cases', 'edit') == ACL_ALLOW_OWNER );
        $this->assertTrue($ownerCanEditCases, 'User/Owner should have edit on Cases');

        // Verify proper perms via API endpoint .. re-login so it fetches a new set of ACL's
        $this->_restLogin($this->_user->user_name,$this->_user->user_name);
        $restReply = $this->_restCall("me");
        $this->assertTrue(isset($restReply['reply']['current_user']['acl']['Cases']['_hash']),'Cases module is missing.');
        $this->assertEquals('owner',$restReply['reply']['current_user']['acl']['Cases']['edit']);
    }   

    private function commit() {
        $GLOBALS['db']->commit(); 
    }
    
}
