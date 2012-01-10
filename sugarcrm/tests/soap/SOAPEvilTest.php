<?php
//FILE SUGARCRM flav=pro ONLY 
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

class SOAPEvilTest extends Sugar_PHPUnit_Framework_TestCase {
    public $_user = null;
    public $_sc = null;
    public $_adminTeamId = null;
    public $_sessionId = null;
    public $_safeAccountId = null;
    public $_evilAccountId = null;
    public $_adminAccountId = null;
    public $_sqlTestStrings = array(
        "UPDATE accounts SET team_id = 1, team_set_id = 1 WHERE name = 'UNIT TEST ADMIN'",
        "INSERT INTO accounts (id,name,team_id,team_set_id) VALUES ('1234-5678-911112','UNIT TEST NOPE',1,1)",
        );

    public function setUp() {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->is_admin = false;
        $this->_user->save();
        $this->_sc = new SoapClient(null,
                                    array(
                                        'location' =>$GLOBALS['sugar_config']['site_url'].'/soap.php',
                                        'uri' => 'http://www.sugarcrm.com/sugarcrm',
                                        'trace' => true,
                                        ));

        $GLOBALS['current_user'] = $this->_user;

        $loginReply = $this->_sc->login(array('user_name'=>$this->_user->user_name,'password'=>$this->_user->user_hash,'version'=>'.01'),
                                        $this->_user->user_name);
        $this->_sessionId = $loginReply->id;

        // Need to find an ID that this user can't read
        $ret = $GLOBALS['db']->query("SELECT id FROM teams WHERE associated_user_id = '1' AND private = 1",true);
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->_adminTeamId = $row['id'];
    }

    public function tearDown() {
       SugarTestUserUtilities::removeAllCreatedAnonymousUsers();        
        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");
        unset($GLOBALS['current_user']);
    }

    public function _resetTestAccounts() {
        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");

        // Can't use the normal bean methods, they don't work on older versions
        $this->_safeAccountId = create_guid();
        $GLOBALS['db']->query("INSERT INTO accounts (id,name,team_set_id,team_id,date_modified,modified_user_id,date_entered,created_by) VALUES ('".$GLOBALS['db']->quote($this->_safeAccountId)."','UNIT TEST SAFETY','1','1',NOW(),'".$GLOBALS['db']->quote($this->_user->id)."',NOW(),'".$GLOBALS['db']->quote($this->_user->id)."')",true);

        $this->_evilAccountId = "i-am-evil-'-is-a-bad-id-unless-test!";
        $GLOBALS['db']->query("INSERT INTO accounts (id,name,team_set_id,team_id,date_modified,modified_user_id,date_entered,created_by) VALUES ('".$GLOBALS['db']->quote($this->_evilAccountId)."','UNIT TEST EVIL','1','1',NOW(),'".$GLOBALS['db']->quote($this->_user->id)."',NOW(),'".$GLOBALS['db']->quote($this->_user->id)."')",true);

        $this->_adminAccountId = create_guid();
        $GLOBALS['db']->query("INSERT INTO accounts (id,name,team_set_id,team_id,date_modified,modified_user_id,date_entered,created_by) VALUES ('".$GLOBALS['db']->quote($this->_adminAccountId)."','UNIT TEST ADMIN','".$GLOBALS['db']->quote($this->_adminTeamId)."','".$GLOBALS['db']->quote($this->_adminTeamId)."',NOW(),'1',NOW(),'1')",true);


    }

    public function _checkTestAccounts() {
        global $db;
        
        $ret = $db->query("SELECT name FROM accounts WHERE id = '".$db->quote($this->_safeAccountId)."'",true);
        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('UNIT TEST SAFETY',$row['name']);

        $ret = $db->query("SELECT name FROM accounts WHERE id = '".$db->quote($this->_evilAccountId)."'",true);
        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('UNIT TEST EVIL',$row['name']);

        $ret = $db->query("SELECT id FROM accounts WHERE name = 'UNIT TEST NOPE'",true);
        $row = $db->fetchByAssoc($ret);
        $this->assertFalse($row);
        
        // We should not be able to retrieve this account
        $account = new Account();
        $account->retrieve($this->_adminAccountId);
        $this->assertNull($account->name);

        // But it should still exist
        $ret = $db->query("SELECT name FROM accounts WHERE id = '".$db->quote($this->_adminAccountId)."'",true);
        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('UNIT TEST ADMIN',$row['name']);

    }

    public function testResetTestAccounts() {
        global $db;

        // This code was written so that both of these functions should pass regardless
        // of what version this is running against, so we know that the framework around the tests is correct
        $this->_resetTestAccounts();
        $this->_checkTestAccounts();

    }

    public function testget_entry_id() {
        $this->_resetTestAccounts();
        try {
            $results = $this->_sc->get_entry($this->_sessionId,'Accounts',"' UNION ALL SELECT * FROM accounts WHERE id='".$this->_adminAccountId."' AND '1'='1",'');
            $this->assertNotEquals($this->_adminAccountId,$results->entry_list[0]->id);
        } catch (SoapFault $sf) {}
        $this->_checkTestAccounts();
    }
    
    public function testget_entry_evil_id() {
        $this->_resetTestAccounts();
        try {
            $results = $this->_sc->get_entry($this->_sessionId,'Accounts',$this->_evilAccountId,'');
            $this->assertEquals(htmlentities($this->_evilAccountId,ENT_QUOTES),$results->entry_list[0]->id);
        } catch (SoapFault $sf) {
            $this->assertNull("I GOT AN EXCEPTION");
        }
        $this->_checkTestAccounts();
    }
}