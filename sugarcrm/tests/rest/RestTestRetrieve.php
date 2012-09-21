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

class RestTestRetrieve extends RestTestBase {
    public function tearDown()
    {
        if ( isset($this->account_id) ) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account->id}'");
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id = '{$this->account->id}'");
        }
        parent::tearDown();
    }

    public function testRetrieve() {
        $this->account = new Account();
        $this->account->name = "UNIT TEST - BEFORE";
        $this->account->save();
        $GLOBALS['db']->commit();
        $restReply = $this->_restCall("Accounts/{$this->account->id}");

        $this->assertEquals($this->account->id,$restReply['reply']['id'],"The returned account id was not the same as the requested account.");
        $this->assertEquals("UNIT TEST - BEFORE",$restReply['reply']['name'],"Did not retrieve the account name.");

    }

    // test that the reply is html decoded Story Id: 30925015 Url: https://www.pivotaltracker.com/story/show/30925015
    public function testRetrieveHTMLEntity() {
        $this->account = new Account();
        $this->account->name = "UNIT TEST << >> BEFORE";
        $this->account->save();
        $GLOBALS['db']->commit();
        $restReply = $this->_restCall("Accounts/{$this->account->id}");

        $this->assertEquals($this->account->id,$restReply['reply']['id'],"The returned account id was not the same as the requested account.");
        $this->assertEquals("UNIT TEST << >> BEFORE",$restReply['reply']['name'],"Did not retrieve the account name.");
    }

}