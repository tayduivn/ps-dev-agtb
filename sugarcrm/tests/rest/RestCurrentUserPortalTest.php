<?php
//FILE SUGARCRM flav=ent ONLY
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

require_once('tests/rest/RestTestPortalBase.php');

class RestCurrentUserPortalTest extends RestTestPortalBase {
    /**
     * @group rest
     */
    public function testRetrieve() {
        $restReply = $this->_restCall("me");
        $this->assertNotEmpty($restReply['reply']['current_user']['id']);
        $this->assertEquals($this->portalGuy->id,$restReply['reply']['current_user']['id']);
        $this->assertEquals($this->_user->id,$restReply['reply']['current_user']['user_id']);
        $this->assertEquals('support_portal',$restReply['reply']['current_user']['type']);
    }

    /**
     * @group rest
     */
    public function testAcls() {
        $allowedModules = array(
                                'Accounts' => array( 'edit' => 'no', 'create' => 'no'),
                                'Bugs' => array('edit' => 'no', 'create' => 'yes'), 
                                'Cases' => array('edit' => 'no', 'create' => 'yes'), 
                                'Notes' => array('edit' => 'no', 'create' => 'yes'), 
                                'KBDocuments' => array('edit' => 'no', 'create' => 'no'), 
                                // edit is yes because they can edit themselves
                                'Contacts' => array('edit' => 'yes', 'create' => 'yes'),
                            );

        $restReply = $this->_restCall("me");
        $user_acls = $restReply['reply']['current_user']['acl'];
        foreach($allowedModules AS $module => $acls) {
            foreach($acls AS $action => $access) {
                $this->assertEquals($user_acls[$module][$action], $access, "{$module} - {$action} Did not have the correct access");
            }
        }

    }

    /**
     * @group rest
     */
    public function testUpdate() {
        $restReply = $this->_restCall("me", json_encode(array('first_name' => 'UNIT TEST - AFTER')), "PUT");
        $this->assertNotEquals(stripos($restReply['reply']['current_user']['full_name'], 'UNIT TEST - AFTER'), false);
    }

    /**
     * @group rest
     */
    public function testPasswordUpdate() {
        $this->_restLogin();
        // Change password twice to be sure working as expected
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'fubar', 'old_password' => 'unittest')),
            'PUT');
        
        $this->assertEquals($reply['reply']['valid'], true, "Part One");
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'newernew', 'old_password' => 'fubar')),
            'PUT');
        $this->assertEquals($reply['reply']['valid'], true, "Part Deux");
        // Now use an incorrect old_password .. this should return valid:false
        $reply = $this->_restCall("me/password",
            json_encode(array('new_password' => 'hello', 'old_password' => 'nope')),
            'PUT');
        $this->assertEquals($reply['reply']['valid'], false, "Part Three - With a Vengence");
    }

    /**
     * @group rest
     */
    public function testPasswordVerification() {
        $reply = $this->_restCall("me/password",
            json_encode(array('password_to_verify' => 'unittest')),
            'POST');
        $this->assertEquals($reply['reply']['valid'], true);
        $reply = $this->_restCall("me/password",
            json_encode(array('password_to_verify' => 'noway')),
            'POST');
        $this->assertEquals($reply['reply']['valid'], false);
    }

}
