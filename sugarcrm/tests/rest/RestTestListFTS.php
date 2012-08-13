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
require_once('modules/SugarFavorites/SugarFavorites.php');

class RestTestListFTS extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
        $this->accounts = array();
        $this->opps = array();
        $this->contacts = array();
        $this->cases = array();
        $this->bugs = array();
        $this->files = array();
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
        $caseIds = array();
        foreach ( $this->cases as $aCase ) {
            $caseIds[] = $aCase->id;
        }
        $caseIds = "('".implode("','",$caseIds)."')";

        $bugIds = array();
        foreach( $this->bugs AS $bug ) {
            $bugIds[] = $bug->id;
        }
        $bugIds = "('" . implode( "','", $bugIds) . "')";

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id IN {$accountIds}");
        $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c IN {$accountIds}");
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM opportunities_cstm WHERE id_c IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM accounts_opportunities WHERE opportunity_id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM opportunities_contacts WHERE opportunity_id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id IN {$contactIds}");
        $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c IN {$contactIds}");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id IN {$contactIds}");
        $GLOBALS['db']->query("DELETE FROM cases WHERE id IN {$caseIds}");
        $GLOBALS['db']->query("DELETE FROM cases_cstm WHERE id_c IN {$caseIds}");
        $GLOBALS['db']->query("DELETE FROM bugs WHERE id IN {$bugIds}");
        $GLOBALS['db']->query("DELETE FROM bugs_cstm WHERE id_c IN {$bugIds}");
        $GLOBALS['db']->query("DELETE FROM accounts_cases WHERE case_id IN {$caseIds}");
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '".$GLOBALS['current_user']->id."'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        foreach($this->files AS $file) {
            unlink($file);
        }
    }

    public function testModuleSearch() {
        // Make sure there is at least one page of accounts
        for ( $i = 0 ; $i < 40 ; $i++ ) {
            $account = new Account();
            $account->name = "UNIT TEST ".count($this->accounts)." - ".create_guid();
            $account->billing_address_postalcode = sprintf("%08d",count($this->accounts));
            if ( $i > 25 && $i < 36 ) {
                $account->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $account->assigned_user_id = '1';
            }
            $account->save();
            $this->accounts[] = $account;
            if ( $i > 33 ) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Accounts',$account->id);
                $fav->new_with_id = true;
                $fav->module = 'Accounts';
                $fav->record_id = $account->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
        }
        $sfi = new SugarSearchEngineFullIndexer();
        $sfi->performFullSystemIndex();
        // Test searching for a lot of records
        $restReply = $this->_restCall("Accounts/?q=".rawurlencode("UNIT TEST")."&max_num=30");

        $this->assertEquals(30,$restReply['reply']['next_offset'],"Next offset was set incorrectly.");

        // Test Offset
        $restReply2 = $this->_restCall("Accounts?offset=".$restReply['reply']['next_offset']);

        $this->assertNotEquals($restReply['reply']['next_offset'],$restReply2['reply']['next_offset'],"Next offset was not set correctly on the second page.");

        // Test finding one record
        $restReply3 = $this->_restCall("Accounts/?q=".rawurlencode($this->accounts[17]->name));
        
        $tmp = array_keys($restReply3['reply']['records']);
        $firstRecord = $restReply3['reply']['records'][$tmp[0]];
        $this->assertEquals($this->accounts[17]->name,$firstRecord['name'],"The search failed for record: ".$this->accounts[17]->name);

        // Sorting descending
        $restReply4 = $this->_restCall("Accounts?q=".rawurlencode("UNIT TEST")."&order_by=id:DESC");
        
        $tmp = array_keys($restReply4['reply']['records']);
        $this->assertLessThan($restReply4['reply']['records'][$tmp[0]]['id'],
                              $restReply4['reply']['records'][$tmp[1]]['id'],
                              'Second record is not lower than the first, decending order failed.');

        // Sorting ascending
        $restReply5 = $this->_restCall("Accounts?q=".rawurlencode("UNIT TEST")."&order_by=id:ASC");
        
        $tmp = array_keys($restReply5['reply']['records']);
        $this->assertGreaterThan($restReply5['reply']['records'][$tmp[0]]['id'],
                                 $restReply5['reply']['records'][$tmp[1]]['id'],
                                 'Second record is not lower than the first, ascending order failed.');

        // Get a list, no searching
        $restReply = $this->_restCall("Accounts?max_num=10");
        $this->assertEquals(10,count($restReply['reply']['records']));

        // Get 2 pages, verify the data is different [check guids]
        $restReply_page1 = $this->_restCall("Accounts?offset=0&max_num=5");
        $restReply_page2 = $this->_restCall("Accounts?offset=5&max_num=5");
        foreach($restReply_page1['reply']['records'] AS $page1_record) {
            foreach($restReply_page2['reply']['records'] AS $page2_record) {
                $this->assertNotEquals($page2_record['id'], $page1_record['id'], "ID's match, pagination may be broke");
            }
        }

    }


    public function testGlobalSearch() {
        // Make sure there is at least one page of accounts
        for ( $i = 0 ; $i < 40 ; $i++ ) {
            $account = new Account();
            $account->name = "UNIT TEST ".count($this->accounts)." - ".create_guid();
            $account->billing_address_postalcode = sprintf("%08d",count($this->accounts));
            if ( $i > 25 && $i < 36 ) {
                $account->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $account->assigned_user_id = '1';
            }
            $account->save();
            $this->accounts[] = $account;
            if ( $i > 33 ) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Accounts',$account->id);
                $fav->new_with_id = true;
                $fav->module = 'Accounts';
                $fav->record_id = $account->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
        }

        for ( $i = 0 ; $i < 30 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT ".count($this->contacts);
            $contact->last_name = "TEST ".create_guid();
            if ( $i > 15 && $i < 26 ) {
                $contact->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $contact->assigned_user_id = '1';
            }
            $contact->save();
            $this->contacts[] = $contact;
            if ( $i > 23 ) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Contacts',$contact->id);
                $fav->new_with_id = true;
                $fav->module = 'Contacts';
                $fav->record_id = $contact->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
        }

        for ( $i = 0 ; $i < 30 ; $i++ ) {
            $opportunity = new Opportunity();
            $opportunity->name = "UNIT ".count($this->opps)." TEST ".create_guid();
            
            if ( $i > 15 && $i < 26 ) {
                $opportunity->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $opportunity->assigned_user_id = '1';
            }
            $opportunity->save();
            $this->opps[] = $opportunity;
            if ( $i > 23 ) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Opportunities',$opportunity->id);
                $fav->new_with_id = true;
                $fav->module = 'Opportunities';
                $fav->record_id = $opportunity->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
        }
        $sfi = new SugarSearchEngineFullIndexer();
        $sfi->performFullSystemIndex();

        // Test searching for a lot of records
        $restReply = $this->_restCall("search?q=".rawurlencode("UNIT TEST")."&max_num=5");

        $this->assertEquals(5,$restReply['reply']['next_offset'],"Next offset was set incorrectly.");

        // Test Offset
        $restReply2 = $this->_restCall("search/?offset=".$restReply['reply']['next_offset']);

        $this->assertNotEquals($restReply['reply']['next_offset'],$restReply2['reply']['next_offset'],"Next offset was not set correctly on the second page.");

        // Test finding one record
        $restReply3 = $this->_restCall("search/?q=".rawurlencode($this->opps[17]->name));
        
        $tmp = array_keys($restReply3['reply']['records']);
        $firstRecord = $restReply3['reply']['records'][$tmp[0]];
        $this->assertEquals($this->opps[17]->name,$firstRecord['name'],"The search failed for record: ".$this->opps[17]->name);

        // Sorting descending
        $restReply4 = $this->_restCall("search?q=".rawurlencode("UNIT TEST")."&order_by=id:DESC");
        
        $tmp = array_keys($restReply4['reply']['records']);
        $this->assertLessThan($restReply4['reply']['records'][$tmp[0]]['id'],
                              $restReply4['reply']['records'][$tmp[1]]['id'],
                              'Second record is not lower than the first, decending order failed.');

        // Sorting ascending
        $restReply5 = $this->_restCall("search?q=".rawurlencode("UNIT TEST")."&order_by=id:ASC");
        
        $tmp = array_keys($restReply5['reply']['records']);
        $this->assertGreaterThan($restReply5['reply']['records'][$tmp[0]]['id'],
                                 $restReply5['reply']['records'][$tmp[1]]['id'],
                                 'Second record is not lower than the first, ascending order failed.');

        // Get a list, no searching
        $restReply = $this->_restCall("search?max_num=10");
        $this->assertEquals(10,count($restReply['reply']['records']));
        
    }

}

