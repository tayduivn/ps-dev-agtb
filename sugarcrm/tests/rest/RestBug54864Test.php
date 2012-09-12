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
 *
 ********************************************************************************/

require_once('tests/rest/RestTestPortalBase.php');

class RestBug54864Test extends RestTestPortalBase {
    /**
     * @group rest
     */
    public function testMeEndpoint() {
        // Build three accounts, we'll associate to two of them.
        for ( $i = 0 ; $i < 3 ; $i++ ) {
            $account = new Account();
            $account->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $account->billing_address_postalcode = sprintf("%08d",($i+1));
            $account->save();
            $this->accounts[] = $account;
        }
        for ( $i = 0 ; $i < 3 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT".($i+1);
            $contact->last_name = create_guid();
            $contact->title = sprintf("%08d",($i+1));
            $contact->save();
            $this->contacts[$i] = $contact;

            $contact->load_relationship('accounts');
            $accountNum = $i;
            $contact->accounts->add(array($this->accounts[$accountNum]));
            if ( $i == 2 ) {
                // This guy is our guy
                $contact->portal_active = true;
                $contact->portal_name = "unittestportal";
                $contact->portal_password = User::getPasswordHash("unittest");
                
                // Add it to two accounts, just to make sure we get that much visibility
                $contact->accounts->add(array($this->accounts[1]));

                $this->portalGuy = $contact;
            }
            $contact->save();
        }

        $GLOBALS['db']->commit();
        

        $restReply = $this->_restCall("me");
        $this->assertTrue(in_array($this->accounts[1]->id,$restReply['reply']['current_user']['account_ids']),'The first account id is missing from the list #1');
        $this->assertTrue(in_array($this->accounts[2]->id,$restReply['reply']['current_user']['account_ids']),'The second account id is missing from the list #1');
        
        
        $this->portalGuy->accounts->delete($this->portalGuy->id,$this->accounts[1]);

        $restReply = $this->_restCall("me");
        $this->assertFalse(in_array($this->accounts[1]->id,$restReply['reply']['current_user']['account_ids']),'The first account id is not missing from the list when it should be #2');
        $this->assertTrue(in_array($this->accounts[2]->id,$restReply['reply']['current_user']['account_ids']),'The second account id is missing from the list #2');

        $this->portalGuy->accounts->delete($this->portalGuy->id,$this->accounts[2]);

        $restReply = $this->_restCall("me");
        $this->assertFalse(in_array($this->accounts[1]->id,$restReply['reply']['current_user']['account_ids']),'The first account id is not missing from the list when it should be #3');
        $this->assertFalse(in_array($this->accounts[2]->id,$restReply['reply']['current_user']['account_ids']),'The second account id is not missing from the list when it should be #3');

    }
}
