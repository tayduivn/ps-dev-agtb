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

class RestTestList extends RestTestBase {
    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        $this->_restLogin($this->_user->user_name,$this->_user->user_name);

        $this->accounts = array();
    }
    
    public function tearDown()
    {
        foreach ( $this->accounts as $account ) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$account->id}'");
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id = '{$account->id}'");
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testList() {
        // Make sure there is at least one page of accounts
        for ( $i = 0 ; $i < 30 ; $i++ ) {
            $account = new Account();
            $account->name = "UNIT TEST ".count($this->accounts)." - ".create_guid();
            $account->billing_address_postalcode = sprintf("%08d",count($this->accounts));
            $account->save();
            $this->accounts[] = $account;
        }

        // Test normal fetch
        $restReply = $this->_restCall("Accounts/");

        $this->assertEquals(21,$restReply['reply']['result_count'],"The result count did not have 21 entries");
        $this->assertEquals(20,$restReply['reply']['next_offset'],"Next offset was set incorrectly.");

        // Test Offset
        $restReply2 = $this->_restCall("Accounts?offset=".$restReply['reply']['next_offset']);

        $this->assertNotEquals($restReply['reply']['next_offset'],$restReply2['reply']['next_offset'],"Next offset was not set correctly on the second page.");

        // Test basic search
        $restReply3 = $this->_restCall("Accounts/search/".rawurlencode($this->accounts[17]->name));
        
        $tmp = array_keys($restReply3['reply']['records']);
        $firstRecord = $restReply3['reply']['records'][$tmp[0]];
        $this->assertEquals($this->accounts[17]->name,$firstRecord['name'],"The search failed for record: ".$this->accounts[17]->name);

        // Sorting descending
        $restReply4 = $this->_restCall("Accounts?orderBy=id:DESC");
        
        $tmp = array_keys($restReply4['reply']['records']);
        $this->assertLessThan($restReply4['reply']['records'][$tmp[0]]['id'],
                              $restReply4['reply']['records'][$tmp[1]]['id'],
                              'Second record is not lower than the first, decending order failed.');

        // Sorting ascending
        $restReply5 = $this->_restCall("Accounts?orderBy=id:ASC");
        
        $tmp = array_keys($restReply5['reply']['records']);
        $this->assertGreaterThan($restReply5['reply']['records'][$tmp[0]]['id'],
                                 $restReply5['reply']['records'][$tmp[1]]['id'],
                                 'Second record is not lower than the first, ascending order failed.');

    }

}