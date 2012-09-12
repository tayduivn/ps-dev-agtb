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

class RestUpdateTest extends RestTestBase {
    public function tearDown()
    {
        if (isset($this->account->id)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account->id}'");
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id = '{$this->account->id}'");
        }
        if (isset($this->contact->id)) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
            $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id = '{$this->contact->id}'");
        }
        if (isset($this->meeting->id)) {
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting->id}'");
            $GLOBALS['db']->query("DELETE FROM meetings_contacts WHERE meeting_id = '{$this->meeting->id}'");
            $GLOBALS['db']->query("DELETE FROM meetings_leads WHERE meeting_id = '{$this->meeting->id}'");
            $GLOBALS['db']->query("DELETE FROM meetings_users WHERE meeting_id = '{$this->meeting->id}'");
        }
        
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '".$GLOBALS['current_user']->id."'");

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testUpdate()
    {
        $this->account = new Account();
        $this->account->name = "UNIT TEST - BEFORE";
        $this->account->save();

        $GLOBALS['db']->commit();

        $restReply = $this->_restCall("Accounts/{$this->account->id}", json_encode(array('name' => 'UNIT TEST - AFTER')), "PUT");

        $this->assertEquals($this->account->id, $restReply['reply']['id'], "The returned account id was not the same.");

        $account2 = new Account();
        $account2->retrieve($this->account->id);

        $this->assertEquals("UNIT TEST - AFTER",
                            $account2->name,
                            "Did not set the account name.");

        $this->assertEquals($restReply['reply']['name'],
                            $account2->name,
                            "Rest Reply and Bean Do Not Match.");
    }

    /**
     * @group rest
     */
    public function testSetFavorite()
    {
        $this->account = new Account();
        $this->account->name = "UNIT TEST - BEFORE";
        $this->account->save();

        $GLOBALS['db']->commit();


        $restReply = $this->_restCall("Accounts/{$this->account->id}", json_encode(array('my_favorite' => true)), "PUT");

        $is_fav = SugarFavorites::isUserFavorite('Accounts', $this->account->id, $this->_user->id);
        
        $this->assertEquals($is_fav, (bool) $restReply['reply']['my_favorite'], "The returned favorite was not the same.");
    }

    /**
     * @group rest
     */
    public function testRemoveFavorite()
    {
        $this->account = new Account();
        $this->account->name = "UNIT TEST - BEFORE";
        $this->account->save();

        $GLOBALS['db']->commit();

        $fav = new SugarFavorites();
        $fav->id = SugarFavorites::generateGUID('Accounts',$this->account->id);
        $fav->new_with_id = true;
        $fav->module = 'Accounts';
        $fav->record_id = $this->account->id;
        $fav->created_by = $GLOBALS['current_user']->id;
        $fav->assigned_user_id = $GLOBALS['current_user']->id;
        $fav->deleted = 0;
        $fav->save();

        $GLOBALS['db']->commit();

        $is_fav = SugarFavorites::isUserFavorite('Accounts', $this->account->id, $this->_user->id);

        $this->assertEquals($is_fav, true, "Didn't actually set the favorite");

        $restReply = $this->_restCall("Accounts/{$this->account->id}", json_encode(array('my_favorite' => false)), "PUT");
        
        $is_fav = SugarFavorites::isUserFavorite('Accounts', $this->account->id, $this->_user->id);
        
        $this->assertEquals($is_fav, (bool) $restReply['reply']['my_favorite'], "The returned favorite was not the same.");
    }

    /**
     * @group rest
     */
    public function testUpdateEmail()
    {
        $this->contact = new Contact();
        $this->contact->first_name = "UNIT TEST - BEFORE";
        $this->contact->save();

        $GLOBALS['db']->commit();


        $emails = array(
                        array(
                            'email_address'=>'test@test.com',
                            'opt_out'=>'0',
                            'invalid_email'=>'0',
                            'primary_address'=>'1'
                        ),
                        array(
                            'email_address'=>'asdf@test.com',
                            'opt_out'=>'0',
                            'invalid_email'=>'1',
                            'primary_address'=>'0'
                        ),
                    );
        $restReply = $this->_restCall("Contacts/{$this->contact->id}", json_encode(array(
            'first_name' => 'UNIT TEST - AFTER',
            'email' => $emails,
        )), "PUT");

        $this->assertEquals($this->contact->id, $restReply['reply']['id'], "The returned contact id was not the same.");

        $contact2 = new Contact();
        $contact2->retrieve($this->contact->id);
        $restReply = $this->_restCall("Contacts/{$this->contact->id}");

        $this->assertEquals($restReply['reply']['email'], $emails,"Returned emails don't match");

        $this->assertEquals("UNIT TEST - AFTER",
                            $contact2->name,
                            "Did not set the contact name.");

        $this->assertEquals($restReply['reply']['name'],
                            $contact2->name,
                            "Rest Reply and Bean Do Not Match.");
    }
    
    /**
     * @group rest
     */
    public function testHasParentNameAfterSave() {
        // Build an account
        $this->account = new Account();
        $this->account->name = 'ABC TEST';
        $this->account->save();
        
        // Build a Meeting with a parent id of the account
        $this->meeting = new Meeting();
        $this->meeting->name = 'UNIT TEST MEETING';
        $this->meeting->parent_id = $this->account->id;
        $this->meeting->parent_type = 'Accounts';
        $this->meeting->status = 'Not Held';
        $this->meeting->save();

        $GLOBALS['db']->commit();
        
        // Change the meeting status and check for parent_name
        $reply = $this->_restCall("Meetings/{$this->meeting->id}", json_encode(array('status' => 'Held')), 'PUT');
        $this->assertEquals($this->meeting->id, $reply['reply']['id'], 'Meeting ID was not the correct ID');
        $this->assertEquals($this->account->name, $reply['reply']['parent_name'], 'Parent Account name was not returned or was incorrect');
    }
}