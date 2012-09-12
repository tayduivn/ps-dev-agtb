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

class RestMetadataAclTest extends RestTestBase {
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
    public function testMetadataAclBasic() {
        $restReply = $this->_restCall('metadata?type_filter=acl');

        $this->assertTrue(isset($restReply['reply']['_hash']),'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['acl']['Accounts']['_hash']),'Accounts module is missing.');
    }

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * @group rest
     */
    public function testMetadataAclField() {

        //Disable access to the website field.
        $this->aclRole = new ACLRole();
        $this->aclRole->name = "Unit Test";
        $this->aclRole->save();
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        $this->aclRole->set_relationship('acl_roles_users', array('role_id'=>$this->aclRole->id ,'user_id'=> $this->_user->id), false);
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing
        $this->aclField = new ACLField();
        $this->aclField->setAccessControl('Accounts', $this->aclRole->id, 'website', ACL_ALLOW_NONE);
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing
        $this->aclField->loadUserFields('Accounts', 'Account', $this->_user->id, true );
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing
        
        // Need to re-login so it fetches a new set of ACL's
        $this->_restLogin($this->_user->user_name,$this->_user->user_name);
        $restReply = $this->_restCall('metadata?type_filter=acl');

        $this->assertTrue(isset($restReply['reply']['_hash']),'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['acl']['Accounts']['_hash']),'Accounts module is missing.');
        $this->assertEquals('no',$restReply['reply']['acl']['Accounts']['fields']['website']['read']);
        $this->assertEquals('no',$restReply['reply']['acl']['Accounts']['fields']['website']['write']);

        $this->aclField->setAccessControl('Accounts', $this->aclRole->id, 'website', ACL_OWNER_READ_WRITE);
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing
        $this->aclField->loadUserFields('Accounts', 'Account', $this->_user->id, true );
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        // Need to re-login so it fetches a new set of ACL's
        $this->_restLogin($this->_user->user_name,$this->_user->user_name);
        $restReply = $this->_restCall('metadata?type_filter=acl');

        $this->assertTrue(isset($restReply['reply']['_hash']),'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['acl']['Accounts']['_hash']),'Accounts module is missing.');
        $this->assertEquals('owner',$restReply['reply']['acl']['Accounts']['fields']['website']['read']);
        $this->assertEquals('owner',$restReply['reply']['acl']['Accounts']['fields']['website']['write']);


    }
    //END SUGARCRM flav=pro ONLY
 
    /**
     * @group rest
     */
    public function testMetadataAclModule() {

        //Disable access to the website field.
        $this->aclRole = new ACLRole();
        $this->aclRole->name = "Unit Test";
        $this->aclRole->save();
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        $this->aclRole->set_relationship('acl_roles_users', array('role_id'=>$this->aclRole->id ,'user_id'=> $this->_user->id), false);
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        // Find action id for Accounts edit
        $ret = $GLOBALS['db']->query("SELECT id FROM acl_actions WHERE category = 'Cases' AND name = 'edit'",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->aclRole->setAction($this->aclRole->id,$row['id'],ACL_ALLOW_OWNER);
        $GLOBALS['db']->commit();
        $this->aclAction = new ACLAction();
        $this->aclAction->getUserActions($this->_user->id,true);
        $GLOBALS['db']->commit();

        // Need to re-login so it fetches a new set of ACL's
        $this->_restLogin($this->_user->user_name,$this->_user->user_name);
        $restReply = $this->_restCall('metadata?type_filter=acl');

        $this->assertTrue(isset($restReply['reply']['_hash']),'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['acl']['Cases']['_hash']),'Cases module is missing.');
        $this->assertEquals('owner',$restReply['reply']['acl']['Cases']['edit']);
        
        
    }   
}
