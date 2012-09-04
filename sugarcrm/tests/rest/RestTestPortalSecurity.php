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

class RestTestPortalSecurity extends RestTestPortalBase {
    public function testPortalSecurity() {
        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, ignore it
        array_shift($cts);
        $ctsCount = count($cts);
        // Build three accounts, we'll associate to two of them.
        for ( $i = 0 ; $i < 3 ; $i++ ) {
            $account = new Account();
            $account->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $account->billing_address_postalcode = sprintf("%08d",($i+1));
            $account->save();
            $this->accounts[] = $account;
        }
        for ( $i = 0 ; $i < 10 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT".($i+1);
            $contact->last_name = create_guid();
            $contact->title = sprintf("%08d",($i+1));
            $contact->save();
            $this->contacts[$i] = $contact;

            $contact->load_relationship('accounts');
            if ( $i > 4 ) {
                // The final account gets all the fun.
                $accountNum = 2;
            } else {
                $accountNum = $i%2;
            }
            $contact->accounts->add(array($this->accounts[$accountNum]));
            if ( $i == 5 ) {
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
        // Add some Opportunities to make sure we can't get to them.
        for ( $i = 0 ; $i < 3 ; $i++ ) {
            $opp = new Opportunity();
            $opp->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $opp->amount = (10000*$i)+500;
            $opp->date_closed = '2014-12-'.($i+1);
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;

            $opp->load_relationship('accounts');
            $accountNum = $i;
            $opp->accounts->add(array($this->accounts[$accountNum]));

            $contactNums = array($i);
            if ( $i == 2 ) {
                // It's the last opportunity, give it all of the remaining contacts
                for ( $ii = 2 ; $ii < 10 ; $ii++ ) {
                    $contactNums[] = $ii;
                }
            }

            foreach ( $contactNums as $contactNum ) {
                $opp->load_relationship('contacts');
                $contact_type = $cts[($contactNum%$ctsCount)];
                $opp->contacts->add(array($this->contacts[$contactNum]),array('contact_role'=>$contact_type));
            }
        }
        // Add some KBDocuments
        for ( $i = 0 ; $i < 5 ; $i++ ) {
            $kbdoc = new KBDocument();
            $kbdoc->kbdocument_name = "KBDocument ".($i+1)." - ".create_guid();
            $kbdoc->body = 'This is a document for the unit test system';
            $startDate = new DateTime();
            $startDate->modify('-7 weeks');
            $endDate = new DateTime();
            $endDate->modify('+7 weeks');
            $kbdoc->active_date = $startDate->format('Y-m-d');
            $kbdoc->exp_date = $endDate->format('Y-m-d');
            $kbdoc->status_id = 'Published';
            $kbdoc->is_external_article = '1';

            switch($i) {
                case 0:
                    $kbdoc->status_id = 'Not Published';
                    break;
                case 1:
                    $kbdoc->is_external_article = '0';
                    break;
                case 2:
                    // Set the start date to the future.
                    $startDate->modify('+8 weeks');
                    $kbdoc->active_date = $startDate->format('Y-m-d');
                    break;
                case 3:
                    // Set the end date to the past
                    $endDate->modify('-8 weeks');
                    $kbdoc->exp_date = $endDate->format('Y-m-d');
                    break;
            }

            $kbdoc->save();
            $this->kbdocs[] = $kbdoc;
        }


        // How about some cases?
        for ( $i = 0 ; $i < 30 ; $i++ ) {
            $acase = new aCase();
            $acase->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $acase->work_log = "The portal should never see this.";
            $acase->description = "The portal can see this.";
            //BEGIN SUGARCRM flav=ent ONLY
            if ( $i%2 == 1 ) {
                $acase->portal_viewable = true;
            }
            //END SUGARCRM flav=ent ONLY
            $acase->save();
            $this->cases[] = $acase;

            $acase->load_relationship('accounts');
            $accountNum = $i%3;
            $acase->_accountNum = $accountNum;
            $acase->accounts->add(array($this->accounts[$accountNum]));

            $acase->load_relationship('contacts');
            if ( $accountNum == 2 ) {
                // It is the primary account we can see, contacts 5-10 are assigned to this
                $contactNum = 4+$i%6;
            } else if ( $accountNum == 1 ) {
                // This is the other account we can see, contact 2,4,5 are assigned to this
                $contactNums = array(2,4,5);
                $contactNum = $contactNums[($i%3)];
            } else {
                // Contacts 1 and 3 are assigned to this
                $contactNums = array(1,3);
                $contactNum = $contactNums[($i%2)];
            }
            
            $acase->contacts->add(array($this->contacts[$contactNum]));

            // 4 out of 5 cases have bugs
            if ( ($i%5) < 4 ) {
                $bug = new Bug();
                $bug->name = "UNIT TEST ".($i+1)." - ".create_guid();
                $bug->work_log = "The portal should never see this.";
                $bug->description = "The portal can see this.";
                
                //BEGIN SUGARCRM flav=ent ONLY
                if ( $i%2 == 1 && $acase->portal_viewable == true ) {
                    $bug->portal_viewable = true;
                }
                //END SUGARCRM flav=ent ONLY

                $bug->save();
                $bug->_accountNum = $accountNum;
                $this->bugs[] = $bug;
                
                $bug->load_relationship('cases');
                $bug->cases->add(array($acase));
            }

        }
        // Add some Notes
        $caseCount = count($this->cases);
        $bugCount = count($this->bugs);
        for ( $i=0; $i<60; $i++ ) {
            $note = new Note();
            $note->name = "UNIT TEST ".($i+1);
            $note->description = "This is a unit test note.";
            $note->save();
            if ( $i%3 < 2 ) {
                $linkBean = $this->cases[($i%$caseCount)];
                if ( $i%5 < 4 ) {
                    $note->portal_flag = true;
                }
            } else {
                $linkBean = $this->bugs[($i%$bugCount)];
                if ( $i%2 != 0 ) {
                    $note->portal_flag = true;
                }
            }
            $linkBean->load_relationship('notes');
            $linkBean->notes->add(array($note));
            
            $this->notes[] = $note;
        }
        // Clean up any hanging related records
        SugarRelationship::resaveRelatedBeans();


        // Negative test: Try and fetch a Contact you shouldn't be able to see
        $restReply = $this->_restCall("Contacts/".$this->contacts[2]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Positive test: Fetch a Contact that you should be able to see
        $restReply = $this->_restCall("Contacts/".$this->contacts[1]->id);
        $this->assertEquals($this->contacts[1]->id,$restReply['reply']['id']);

        // Positive test: Should be able to change the name of our Contact
        $restReply = $this->_restCall("Contacts/".$this->contacts[5]->id,json_encode(array('last_name'=>'UnitTestMyGuy')),'PUT');
        $this->assertEquals('UnitTestMyGuy',$restReply['reply']['last_name']);
        $restReply = $this->_restCall("Contacts/".$this->contacts[5]->id);
        $this->assertEquals('UnitTestMyGuy',$restReply['reply']['last_name']);

        // Negative test: Should not be able to create a new Contact
        $restReply = $this->_restCall("Contacts/",json_encode(array('last_name'=>'UnitTestNew','first_name'=>'NewGuy')),'POST');
        $this->assertEquals('not_authorized',$restReply['reply']['error']);
        
        // Fetch contacts, make sure we can only see the correct ones.
        $restReply = $this->_restCall("Contacts");

        foreach ( $restReply['reply']['records'] as $record ) {
            // We should be linked to accounts[1] and accounts[2]
            $this->assertNotEquals($this->accounts[0]->id,$record['account_id']);
            $foundOne = ($record['account_id']==$this->accounts[1]->id)
                ||($record['account_id']==$this->accounts[2]->id);
            $this->assertTrue($foundOne);
        }

        // Negative test: Try and fetch a Account you shouldn't be able to see
        $restReply = $this->_restCall("Accounts/".$this->accounts[0]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Positive test: Fetch a Account that you should be able to see
        $restReply = $this->_restCall("Accounts/".$this->accounts[1]->id);
        $this->assertEquals($this->accounts[1]->id,$restReply['reply']['id']);

        // Positive test: Fetch the other Account that you should be able to see
        $restReply = $this->_restCall("Accounts/".$this->accounts[2]->id);
        $this->assertEquals($this->accounts[2]->id,$restReply['reply']['id']);

        // Negative test: Should not be able to create a new Account
        $restReply = $this->_restCall("Accounts/",json_encode(array('name'=>'UnitTestNew')),'POST');
        $this->assertEquals('not_authorized',$restReply['reply']['error']);
        
        $restReply = $this->_restCall("Accounts");

        foreach ( $restReply['reply']['records'] as $record ) {
            // We should be linked to accounts[1] and accounts[2]
            $this->assertNotEquals($this->accounts[0]->id,$record['id']);
            $foundOne = ($record['id']==$this->accounts[1]->id)
                ||($record['id']==$this->accounts[2]->id);
            $this->assertTrue($foundOne);
        }


        $restReply = $this->_restCall("Accounts/".$this->accounts[2]->id."/link/cases");

        foreach ( $restReply['reply']['records'] as $record ) {
            // We should only get cases that have the portal_viewable flag set to true
            $this->assertEquals(1,$record['portal_viewable']);
        }

        $restReply = $this->_restCall("Accounts/".$this->accounts[1]->id."/link/bugs");

        foreach ( $restReply['reply']['records'] as $record ) {
            // We should only get cases that have the portal_viewable flag set to true
            $this->assertEquals(1,$record['portal_viewable']);
        }

        // Negative test: We should not be able to fetch an Opportunity
        $restReply = $this->_restCall("Opportunities/".$this->opps[1]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Negative test: We should not be able to list opportunities
        $restReply = $this->_restCall("Opportunities/");
        $this->assertEquals(-1,$restReply['reply']['next_offset']);
        
        // Negative test: Should not be able to create a new Opportunity
        $restReply = $this->_restCall("Opportunities/",json_encode(array('name'=>'UnitTestNew','account_id'=>$this->accounts[1]->id,'expected_close_date'=>'2012-10-11 12:00:00')),'POST');
        $this->assertEquals('not_authorized',$restReply['reply']['error']);


        // Negative test: Try and fetch a Case you shouldn't be able to see
        $restReply = $this->_restCall("Cases/".$this->cases[0]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Negative test: Fetch a Case that is related to an account you can see, but is not portal visible
        $restReply = $this->_restCall("Cases/".$this->cases[2]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Positive test: Fetch a Case assigned to the other account that you should be able to see
        $restReply = $this->_restCall("Cases/".$this->cases[1]->id);
        $this->assertEquals($this->cases[1]->id,$restReply['reply']['id']);

        // Positive test: Should be able to create a new Case
        $restReply = $this->_restCall("Cases/",json_encode(array('name'=>'UnitTestNew','account_id'=>$this->accounts[1]->id,'portal_viewable'=>1)),'POST');
        $this->assertNotEmpty($restReply['reply']['id']);
        $createdCase = BeanFactory::getBean('Cases',$restReply['reply']['id']);
        $this->cases[] = $createdCase;
        
        // Positive test: Should be able to fetch this new bean
        $restReply = $this->_restCall("Cases/".$createdCase->id);
        $this->assertEquals($restReply['reply']['id'],$createdCase->id);
        
        $restReply = $this->_restCall("Cases");

        foreach ( $restReply['reply']['records'] as $record ) {
            // Cases should be linked to accounts[1] or accounts[2]
            $this->assertEquals('1',$record['portal_viewable']);
            $foundOne = ($record['account_id']==$this->accounts[1]->id)
                ||($record['account_id']==$this->accounts[2]->id);
            $this->assertTrue($foundOne);
        }

        $restReply = $this->_restCall("Cases/".$this->cases[1]->id."/link/bugs");

        foreach ( $restReply['reply']['records'] as $record ) {
            // We should only get cases that have the portal_viewable flag set to true
            $this->assertEquals('1',$record['portal_viewable']);
        }

        // Negative test: Try and fetch a Bug you shouldn't be able to see
        $restReply = $this->_restCall("Bugs/".$this->bugs[0]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Positive test: Fetch a Bug that is related to an account you can see, but is not portal visible
        $restReply = $this->_restCall("Bugs/".$this->bugs[5]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Positive test: Fetch a Bug assigned to the other account that you should be able to see
        $restReply = $this->_restCall("Bugs/".$this->bugs[1]->id);
        $this->assertEquals($this->bugs[1]->id,$restReply['reply']['id']);

        // Positive test: Should be able to create a new Bug, as long as it is related to a case.
        $restReply = $this->_restCall("Cases/".$this->cases[1]->id."/link/bugs/",json_encode(array('name'=>'UnitTestNew','portal_viewable'=>1)),'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id']);
        $createdBug = BeanFactory::getBean('Bugs',$restReply['reply']['related_record']['id']);
        $this->bugs[] = $createdBug;
        
        // Positive test: Should be able to fetch this new bean
        $restReply = $this->_restCall("Bugs/".$createdBug->id);
        $this->assertEquals($restReply['reply']['id'],$createdBug->id);
        
        $restReply = $this->_restCall("Bugs");

        foreach ( $restReply['reply']['records'] as $record ) {
            $this->assertEquals('1',$record['portal_viewable']);
        }

        // Note debugging, to figure out which notes have what properties
        /*
        foreach ( $this->notes as $i => $note ) {
            if ( $note->parent_type == 'Cases' ) {
                $parentNum = $i%$caseCount;
                $parent = $this->cases[$parentNum];
            } else {
                $parentNum = $i%$bugCount;
                $parent = $this->bugs[$parentNum];
            }
            printf("%3d = %1d %10s %1d %3d %2d\n",
                   $i,    $note->portal_flag, $note->parent_type, $parent->portal_viewable, $parentNum, $parent->_accountNum );
        }
        */

        // Note 2: no portal_flag, related to bug #2, bug not portal visible, related to account #2
        $restReply = $this->_restCall("Notes/".$this->notes[2]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);
        
        // Note 5: portal_flag, related to bug #5, bug not portal visible, related to account #0
        $restReply = $this->_restCall("Notes/".$this->notes[5]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);
        
        // Note 35: portal_flag, related to bug #11, bug portal visible, related to account #1
        $restReply = $this->_restCall("Notes/".$this->notes[35]->id);
        $this->assertEquals($this->notes[35]->id,$restReply['reply']['id']);

        // Note 17: portal_flag, related to bug #17, bug portal visible, related to account #0
        $restReply = $this->_restCall("Notes/".$this->notes[17]->id);
        $this->assertEquals($this->notes[17]->id,$restReply['reply']['id']);

        // Note 14: no portal_flag, related to bug #14, bug portal visible, related to account #2
        $restReply = $this->_restCall("Notes/".$this->notes[14]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Note 15: portal_flag, related to case #15, case portal visible, related to account #0
        $restReply = $this->_restCall("Notes/".$this->notes[15]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Note 13: portal_flag, related to case #13, case portal visible, related to account #1
        $restReply = $this->_restCall("Notes/".$this->notes[13]->id);
        $this->assertEquals($this->notes[13]->id,$restReply['reply']['id']);

        // Make sure we can find Note #13 through the relationship API
        $restReply = $this->_restCall('Cases/'.$this->cases[13]->id.'/link/notes/'.$this->notes[13]->id);
        $this->assertEquals($this->notes[13]->id,$restReply['reply']['id']);
        
        // Make sure we can find Note #13 through the relationship list API
        $restReply = $this->_restCall('Cases/'.$this->cases[13]->id.'/link/notes/');
        $foundIt = false;
        foreach ( $restReply['reply']['records'] as $noteRecord ) {
            if ( $noteRecord['id'] == $this->notes[13]->id ) {
                $foundIt = true;
            }
            $this->assertEquals(1,$noteRecord['portal_flag']);
        }
        $this->assertTrue($foundIt);        

        // Note 22: portal_flag, related to case #22, case not portal visible, related to account #1
        $restReply = $this->_restCall("Notes/".$this->notes[22]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Note 49: no portal_flag, related to case #19, case portal visible, related to account #1
        $restReply = $this->_restCall("Notes/".$this->notes[49]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Positive test: Should be able to create a new Note, as long as it is related to a case or a bug.
        $restReply = $this->_restCall("Cases/".$this->cases[25]->id."/link/notes/",json_encode(array('name'=>'UnitTestNew','portal_flag'=>1)),'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id']);
        $createdNote = BeanFactory::getBean('Notes',$restReply['reply']['related_record']['id']);
        $this->notes[] = $createdNote;

        $restReply = $this->_restCall("Bugs/".$this->bugs[20]->id."/link/notes/",json_encode(array('name'=>'UnitTestNew','portal_flag'=>1)),'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id']);
        $createdNote = BeanFactory::getBean('Notes',$restReply['reply']['related_record']['id']);
        $this->notes[] = $createdNote;


        // Validate KBDocuments
        $restReply = $this->_restCall("KBDocuments/");
        foreach ( $restReply['reply']['records'] as $kbdoc ) {
            $this->assertEquals('1',$kbdoc['is_external_article']);
            $this->assertEquals('Published',$kbdoc['status_id']);
            $startTime = DateTime::createFromFormat('Y-m-d',$kbdoc['active_date'])->getTimestamp();
            $this->assertLessThan(time(),$startTime,"Current date is less than: ".$kbdoc['active_date']);
            $endTime = DateTime::createFromFormat('Y-m-d',$kbdoc['exp_date'])->getTimestamp();
            $this->assertGreaterThan(time(),$endTime,"Current date is after: ".$kbdoc['exp_date']);
        }
        // Should not be able to fetch some of the records, let's test that.
        for ( $i = 0; $i < 4 ; $i++ ) {
            $restReply = $this->_restCall("KBDocuments/".$this->kbdocs[$i]->id);
            $this->assertEquals('not_found',$restReply['reply']['error']);
        }

        $restReply = $this->_restCall("KBDocuments/".$this->kbdocs[4]->id);
        $this->assertEquals($this->kbdocs[4]->id,$restReply['reply']['id']);

    }

    public function testPortalSecurityChangeAccount() {
        // Build three accounts, we'll associate to two of them.
        for ( $i = 0 ; $i < 3 ; $i++ ) {
            $account = new Account();
            $account->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $account->billing_address_postalcode = sprintf("%08d",($i+1));
            $account->save();
            $this->accounts[] = $account;
        }
        for ( $i = 0 ; $i < 10 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT".($i+1);
            $contact->last_name = create_guid();
            $contact->title = sprintf("%08d",($i+1));
            $contact->save();
            $this->contacts[$i] = $contact;

            $contact->load_relationship('accounts');
            if ( $i > 4 ) {
                // The final account gets all the fun.
                $accountNum = 2;
            } else {
                $accountNum = $i%2;
            }
            $contact->accounts->add(array($this->accounts[$accountNum]));
            if ( $i == 5 ) {
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

        // Negative test: Try and fetch a Contact you shouldn't be able to see
        $restReply = $this->_restCall("Contacts/".$this->contacts[2]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Positive test: Fetch a Contact that you should be able to see
        $restReply = $this->_restCall("Contacts/".$this->portalGuy->id);
        $this->assertEquals($this->portalGuy->id,$restReply['reply']['id']);

        // Positive test: Fetch another Contact that you should be able to see
        $restReply = $this->_restCall("Contacts/".$this->contacts[1]->id);
        $this->assertEquals($this->contacts[1]->id,$restReply['reply']['id']);

        // Positive test: Should be able to change the name of our Contact
        $restReply = $this->_restCall("Contacts/".$this->portalGuy->id,json_encode(array('last_name'=>'UnitTestMyGuy')),'PUT');
        $this->assertEquals('UnitTestMyGuy',$restReply['reply']['last_name']);
        $restReply = $this->_restCall("Contacts/".$this->portalGuy->id);
        $this->assertEquals('UnitTestMyGuy',$restReply['reply']['last_name']);

        // Positive test: Make sure we can see both accounts for now
        $restReply = $this->_restCall('Accounts/');
        foreach ( $restReply['reply']['records'] as $record ) {
            $this->assertNotEquals($this->accounts[0]->id,$record['id']);
        }

        // Positive test: Make sure we can access this account first
        $restReply = $this->_restCall('Accounts/'.$this->accounts[1]->id);
        $this->assertEquals($this->accounts[1]->id,$restReply['reply']['id']);
        
        // Remove the contact from one of the accounts and make sure that
        // it doesn't come up on the list
        $this->portalGuy->accounts->delete($this->portalGuy->id,$this->accounts[1]);

        // Positive test: Make sure we can only see one account now
        $restReply = $this->_restCall('Accounts/');
        foreach ( $restReply['reply']['records'] as $record ) {
            $this->assertEquals($this->accounts[2]->id,$record['id']);
        }

        // Negative test: Try and fetch the account we unlinked
        $restReply = $this->_restCall("Accounts/".$this->accounts[1]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);
    }

    public function testPortalSecurityNoAccount() {
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
            $this->contacts[$i] = $contact;

            if ( $i == 2 ) {
                // This guy is our guy
                $contact->portal_active = true;
                $contact->portal_name = "unittestportal";
                $contact->portal_password = User::getPasswordHash("unittest");
                
                $this->portalGuy = $contact;
            }
            $contact->save();
        }

        // How about some cases?
        for ( $i = 0 ; $i < 3 ; $i++ ) {
            $acase = new aCase();
            $acase->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $acase->work_log = "The portal should never see this.";
            $acase->description = "The portal can see this.";
            //BEGIN SUGARCRM flav=ent ONLY
            if ( $i != 2 ) {
                $acase->portal_viewable = true;
            }
            //END SUGARCRM flav=ent ONLY
            $acase->save();
            $this->cases[] = $acase;

            $acase->load_relationship('accounts');
            $accountNum = $i;
            $acase->_accountNum = $accountNum;
            $acase->accounts->add(array($this->accounts[$accountNum]));

            // All cases have bugs
            $bug = new Bug();
            $bug->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $bug->work_log = "The portal should never see this.";
            $bug->description = "The portal can see this.";
            
            //BEGIN SUGARCRM flav=ent ONLY
            if ( $acase->portal_viewable == true ) {
                $bug->portal_viewable = true;
            }
            //END SUGARCRM flav=ent ONLY
            
            $bug->save();
            $bug->_accountNum = $accountNum;
            $this->bugs[] = $bug;
            
            $bug->load_relationship('cases');
            $bug->cases->add(array($acase));

            $bug->load_relationship('notes');
            // All Bugs have two notes, one portal visible, one not
            for ( $ii = 0 ; $ii < 2 ; $ii++ ) {
                $note = new Note();
                $note->name = "UNIT TEST ".($i+1)." - ".($ii+1);
                $note->description = "SOME UNIT TEST STUFF";
                //BEGIN SUGARCRM flav=ent ONLY
                if ( $ii == 1 && $acase->portal_viewable == true && $bug->portal_viewable == true) {
                    $note->portal_flag = true;
                }
                //END SUGARCRM flav=ent ONLY
                $note->save();
                $this->notes[] = $note;

                $bug->notes->add(array($note));
            }
        }
        // Clean up any hanging related records
        SugarRelationship::resaveRelatedBeans();


        // Negative test: Try and fetch an account we never linked
        $restReply = $this->_restCall("Accounts/".$this->accounts[1]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);
        
        // Negative test: Try and fetch a contact we never linked
        $restReply = $this->_restCall("Contacts/".$this->contacts[1]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Positive test: Make sure we can access this contact
        $restReply = $this->_restCall('Contacts/'.$this->portalGuy->id);
        $this->assertEquals($this->portalGuy->id,$restReply['reply']['id']);

        // Positive test: Make sure we can only see this contact in the contact list
        $restReply = $this->_restCall('Contacts/');
        foreach ( $restReply['reply']['records'] as $record ) {
            $this->assertEquals($this->portalGuy->id,$record['id']);
        }

        // Negative test: Should not be able to create a new Contact
        $restReply = $this->_restCall("Contacts/",json_encode(array('last_name'=>'UnitTestNew','first_name'=>'NewGuy')),'POST');
        $this->assertEquals('not_authorized',$restReply['reply']['error']);

        // Negative test: Should not be able to create a new Case
        $restReply = $this->_restCall("Cases/",json_encode(array('name'=>'UnitTestNew','description'=>'UNIT TEST SHOULD FAIL')),'POST');
        $this->assertEquals('not_authorized',$restReply['reply']['error']);
        
        // Fetch contacts, make sure we can only see the correct one.
        $restReply = $this->_restCall("Contacts");

        foreach ( $restReply['reply']['records'] as $record ) {
            $this->assertEquals($this->portalGuy->id,$record['id']);
        }

        // Positive test: Make sure we can fetch a bug that is visible
        $restReply = $this->_restCall('Bugs/'.$this->bugs[1]->id);
        $this->assertEquals($this->bugs[1]->id,$restReply['reply']['id']);

        // BEGIN SUGARCRM flav=ent ONLY
        // Negative test: Try and fetch a non-visible bug
        $restReply = $this->_restCall("Bugs/".$this->bugs[2]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);
        // END SUGARCRM flav=ent ONLY

        // Positive test: Make sure we can fetch a note that is related to a visible bug
        $restReply = $this->_restCall('Notes/'.$this->notes[1]->id);
        $this->assertEquals($this->notes[1]->id,$restReply['reply']['id']);
        
        // BEGIN SUGARCRM flav=ent ONLY
        // Negative test: Try and fetch a non-visible note
        $restReply = $this->_restCall("Notes/".$this->notes[0]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);
        // END SUGARCRM flav=ent ONLY
        
        // Positive test: Make sure we can create a new note
        $restReply = $this->_restCall('Notes/',json_encode(array('name'=>'UNIT TEST POSTED','description'=>'This was posted by a unit test.','parent_type'=>'Bugs','parent_id'=>$this->bugs[1]->id,'portal_flag'=>true)),'POST');
        $this->assertTrue(!empty($restReply['reply']['id']));

        // Add it to the list of beans so we can properly delete it.
        $this->notes[] = BeanFactory::getBean('Notes',$restReply['reply']['id']);

        // Negative test: Should not be able to create a new Opportunity
        $restReply = $this->_restCall("Opportunities/",json_encode(array('name'=>'UnitTestNew','account_id'=>$this->accounts[1]->id,'expected_close_date'=>'2012-10-11 12:00:00')),'POST');
        $this->assertEquals('not_authorized',$restReply['reply']['error']);

        // Negative test: Should not be able to create a new Case
        $restReply = $this->_restCall("Cases/",json_encode(array('name'=>'UnitTestNew','account_id'=>'','portal_viewable'=>1)),'POST');
        $this->assertEquals('not_authorized',$restReply['reply']['error']);

    }
}
