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

class RestRelateTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();

        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
        $this->accounts = array();
        $this->contacts = array();
        $this->opps = array();
    }
    
    public function tearDown()
    {
        $accountIds = array();
        foreach ( $this->accounts as $account ) {
            $accountIds[] = $account->id;
        }
        $accountIds = "('".implode("','",$accountIds)."')";
        $oppIds = array();
        foreach ( $this->opps as $opp ) {
            $oppIds[] = $opp->id;
        }
        $oppIds = "('".implode("','",$oppIds)."')";
        $contactIds = array();
        foreach ( $this->contacts as $contact ) {
            $contactIds[] = $contact->id;
        }
        $contactIds = "('".implode("','",$contactIds)."')";
        
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id IN {$accountIds}");
        $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id IN {$accountIds}");
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM opportunities_cstm WHERE id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM accounts_opportunities WHERE opportunity_id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM opportunities_contacts WHERE opportunity_id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id IN {$contactIds}");
        $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id IN {$contactIds}");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id IN {$contactIds}");
        
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testRelateList() {
        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, ignore it
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least one page of each of the related modules
        for ( $i = 0 ; $i < 5 ; $i++ ) {
            $account = new Account();
            $account->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $account->billing_address_postalcode = sprintf("%08d",($i+1));
            $account->save();
            $this->accounts[] = $account;
        }
        for ( $i = 0 ; $i < 60 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT".($i+1);
            $contact->last_name = create_guid();
            $contact->title = sprintf("%08d",($i+1));
            $contact->save();
            $this->contacts[] = $contact;

            $contact->load_relationship('accounts');
            if ( $i > 4 ) {
                // The final account gets all the fun.
                $accountNum = 4;
            } else {
                $accountNum = $i;
            }
            $contact->accounts->add(array($this->accounts[$accountNum]));

        }
        for ( $i = 0 ; $i < 30 ; $i++ ) {
            $opp = new Opportunity();
            $opp->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $opp->amount = (10000*$i)+500;
            $opp->date_closed = '2014-12-'.($i+1);
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;

            $opp->load_relationship('accounts');
            if ( $i > 4 ) {
                // The final account gets all the fun.
                $accountNum = 4;
            } else {
                $accountNum = $i;
            }
            $opp->accounts->add(array($this->accounts[$accountNum]));

            $contactNums = array($i);
            if ( $i == 29 ) {
                // It's the last opportunity, give it all of the remaining contacts
                for ( $ii = 30 ; $ii < 60 ; $ii++ ) {
                    $contactNums[] = $ii;
                }
            }

            foreach ( $contactNums as $contactNum ) {
                $opp->load_relationship('contacts');
                $contact_type = $cts[($contactNum%$ctsCount)];
                $opp->contacts->add(array($this->contacts[$contactNum]),array('contact_role'=>$contact_type));
            }
        }
        
        $GLOBALS['db']->commit();

        // Test normal fetch
        $restReply = $this->_restCall("Accounts/".$this->accounts[4]->id."/link/opportunities");

        $this->assertEquals(10,$restReply['reply']['next_offset'],"Next offset was set incorrectly.");

        // Test Offset
        $restReply2 = $this->_restCall("Accounts/".$this->accounts[4]->id."/link/opportunities?offset=20");

        $this->assertEquals(-1,$restReply2['reply']['next_offset'],"Next offset was set incorrectly on the second page.");

        // Test basic search
        $restReply3 = $this->_restCall("Accounts/".$this->accounts[4]->id."/link/contacts?q=".rawurlencode($this->contacts[47]->last_name));
        
        $tmp = array_keys($restReply3['reply']['records']);
        $firstRecord = $restReply3['reply']['records'][$tmp[0]];
        $this->assertEquals($this->contacts[47]->last_name,$firstRecord['last_name'],"The search failed for record: ".$this->contacts[47]->last_name);

        // Sorting descending
        $restReply4 = $this->_restCall("Accounts/".$this->accounts[4]->id."/link/contacts?order_by=last_name:DESC");
        
        $tmp = array_keys($restReply4['reply']['records']);
        $this->assertLessThan($restReply4['reply']['records'][$tmp[0]]['last_name'],
                              $restReply4['reply']['records'][$tmp[1]]['last_name'],
                              'Second record is not lower than the first, decending order failed.');

        // Sorting ascending
        $restReply5 = $this->_restCall("Accounts/".$this->accounts[4]->id."/link/contacts?order_by=first_name:ASC");
        
        $tmp = array_keys($restReply5['reply']['records']);
        $this->assertGreaterThan($restReply5['reply']['records'][$tmp[0]]['first_name'],
                                 $restReply5['reply']['records'][$tmp[1]]['first_name'],
                                 'Second record is not lower than the first, ascending order failed.');


        // Fetching the role field from the opportunity contact relationship
        $restReply6 = $this->_restCall("Opportunities/".$this->opps[29]->id."/link/contacts?order_by=first_name:DESC");
        $this->assertNotEmpty($restReply6['reply']['records'][0]['opportunity_role'],"The role field on the Opportunity -> Contact relationship was not populated.");

        // verify accounts don't return the same contacts
        $restReply7 = $this->_restCall("Accounts/".$this->accounts[4]->id."/link/contacts?order_by=last_name:ASC");

        $restReply8 = $this->_restCall("Accounts/".$this->accounts[3]->id."/link/contacts?order_by=last_name:ASC");

        $account3 = $restReply8['reply']['records'];

        $account4 = $restReply7['reply']['records'];

        // due to account #4 having the most we will loop thru that checking to see if the names are equal or not
        // we can assume the GUID's will never match
        foreach($account4 AS $account) {
            foreach($account3 AS $acc) {
                $this->assertNotEquals($account['last_name'],$acc['last_name'], 'GUIDs Match something is wrong');
            }
        }
    }

}

