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

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->new_user_id}'");
        $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->admin_user_id}'");
        $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->deleted_user_id}'");
    }

    /**
     * @group rest
     */
    public function testAcls() {
        $restReply = $this->_restCall("Users/",
            json_encode(array('first_name'=>'UNIT TEST', 'last_name' => '- AFTER')),
            'POST');

        $this->assertTrue(!isset($restReply['reply']['id']),
            "An user was created");

        $this->assertEquals($restReply['reply']['error'], 'not_authorized',
            "An user was created");

        $restReply = $this->_restCall("/me", json_encode(array('first_name' => 'Awesome')), 'PUT');

        $this->assertEquals($restReply['reply']['current_user']['full_name'], 'Awesome ' . $GLOBALS['current_user']->last_name, 'Did not change my first name');

        // test create user with admin
        $old_user = $GLOBALS['current_user'];
        $user = new User();
        $user->user_name = 'captain';
        $user->user_hash = $user->getPasswordHash('awesome');
        $user->is_admin = 1;
        $user->first_name = 'captain';
        $user->last_name = 'awesome';
        $user->save();

        $this->_restLogin($user->user_name, 'awesome');

        // make sure he can't delete himself..that would be silly
        $restReply = $this->_restCall("Users/{$user->id}", array(), "DELETE");

        $this->assertEquals($restReply['reply']['error'], 'not_authorized',
            "You just deleted yourself..");

        $restReply = $this->_restCall("Users/",
            json_encode(array('first_name'=>'UNIT TEST', 'last_name' => '- AFTER', 'is_admin' => true)),
            'POST');

        $this->assertTrue(isset($restReply['reply']['id']),
            "An user was not created");

        $this->assertEquals($restReply['reply']['is_admin'], true, "Is admin was not set");

        $this->new_user_id = $restReply['reply']['id'];

        $restReply = $this->_restCall("Users/{$this->new_user_id}", array(), "DELETE");

        $this->assertTrue(isset($restReply['reply']['id']),
            "An user was not deleted");

        $this->deleted_user_id = $this->new_user_id;

        $restReply = $this->_restCall("Users/",
            json_encode(array('first_name'=>'UNIT TEST', 'last_name' => '- AFTER', 'is_admin' => true)),
            'POST');

        $this->assertTrue(isset($restReply['reply']['id']),
            "An user was not created");

        $this->new_user_id = $restReply['reply']['id'];

        $this->admin_user_id = $user->id;
        // test is_admin set with original user
        $this->_restLogin();

        $restReply = $this->_restCall("Users/{$this->new_user_id}", json_encode(array("is_admin" => false)), "PUT");

        $this->assertEquals($restReply['reply']['error'], 'not_authorized',
            "An user was created");

        // test delete with original user
        $restReply = $this->_restCall("Users/{$this->new_user_id}", array(), "DELETE");

        $this->assertEquals($restReply['reply']['error'], 'not_authorized',
            "An user was deleted");

        //test getting picture file of another user
        $restReply = $this->_restCall("Users/1/file/picture", array());

        $this->assertEmpty($restReply['reply']['error'], "An error was thrown it was: " . print_r($restReply['reply']['error'], true));

        $this->assertNotEmpty($restReply['replyRaw'], "No reply");
    }

}
