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

class RestTestRelateRecord extends RestTestBase {
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
        $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c IN {$accountIds}");
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM opportunities_cstm WHERE id_c IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM accounts_opportunities WHERE opportunity_id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM opportunities_contacts WHERE opportunity_id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id IN {$contactIds}");
        $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c IN {$contactIds}");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id IN {$contactIds}");

        parent::tearDown();
    }

    public function testFetchRelatedRecord() {
        global $db;

        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, ignore it
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least one page of each of the related modules
        for ( $i = 0 ; $i < 2 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT".($i+1);
            $contact->last_name = create_guid();
            $contact->title = sprintf("%08d",($i+1));
            $contact->save();
            $this->contacts[] = $contact;

        }
        for ( $i = 0 ; $i < 1 ; $i++ ) {
            $opp = new Opportunity();
            $opp->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $opp->amount = (10000*$i)+500;
            $opp->date_closed = '2014-12-'.($i+1);
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;


            $contactNums = array(0,1);

            foreach ( $contactNums as $contactNum ) {
                $opp->load_relationship('contacts');
                $contact_type = $cts[($contactNum%$ctsCount)];
                $this->contacts[$contactNum]->contact_role = $contact_type;
                $opp->contacts->add(array($this->contacts[$contactNum]),array('contact_role'=>$contact_type));
            }
        }

        // Test normal fetch
        $restReply = $this->_restCall("Opportunities/".$this->opps[0]->id."/link/contacts/".$this->contacts[0]->id);
        
        $this->assertEquals($this->contacts[0]->id,$restReply['reply']['id'],"Did not fetch the related contact");
        $this->assertNotEmpty($restReply['reply']['opportunity_role'],"The role field on the Opportunity -> Contact relationship was not populated.");
        $this->assertEquals($this->contacts[0]->contact_role, $restReply['reply']['opportunity_role'],"The role field on the Opportunity -> Contact relationship does not match the bean.");

        // Test fetch where the opp id is not there
        $restReply = $this->_restCall("Opportunities/UNIT_TEST_THIS_IS_NOT_A_REAL_ID/link/contacts/".$this->contacts[0]->id);
        $this->assertEquals('not_found',$restReply['reply']['error']);

        // Test fetch where the opp id is there, but the contact ID isn't
        $restReply = $this->_restCall("Opportunities/".$this->opps[0]->id."/link/contacts/UNIT_TEST_THIS_IS_NOT_A_REAL_ID");
        $this->assertEquals('not_found',$restReply['reply']['error']);

    }

    public function testSameNumberOfRecords() {
        global $db;
        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, ignore it
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least one page of each of the related modules
        for ( $i = 0 ; $i < 2 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT".($i+1);
            $contact->last_name = create_guid();
            $contact->title = sprintf("%08d",($i+1));
            $contact->save();
            $this->contacts[] = $contact;

        }
        for ( $i = 0 ; $i < 1 ; $i++ ) {
            $opp = new Opportunity();
            $opp->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $opp->amount = (10000*$i)+500;
            $opp->date_closed = '2014-12-'.($i+1);
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;


            $contactNums = array(0,1);

            foreach ( $contactNums as $contactNum ) {
                $opp->load_relationship('contacts');
                $contact_type = $cts[($contactNum%$ctsCount)];
                $this->contacts[$contactNum]->contact_role = $contact_type;
                $opp->contacts->add(array($this->contacts[$contactNum]),array('contact_role'=>$contact_type));
            }
        }

        // Test normal fetch
        $restReply = $this->_restCall("Opportunities/".$this->opps[0]->id."/link/contacts/".$this->contacts[0]->id);
        $fetch_fields = count($restReply['reply']);
        // create a record

        for ( $i = 0 ; $i < 1 ; $i++ ) {
            $opp = new Opportunity();
            $opp->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $opp->amount = (10000*$i)+500;
            $opp->date_closed = '2014-12-'.($i+1);
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;
        }

        $restReply = $this->_restCall("Opportunities/".$this->opps[0]->id."/link/contacts",
                                      json_encode(array(
                                                      'last_name'=>'TEST',
                                                      'first_name'=>'UNIT',
                                                      'description'=>'UNIT TEST CONTACT'
                                      )),'POST');

        $create_fields = count($restReply['reply']['related_record']);

        // update a record


        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, throw it away
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least two of the related modules
        for ( $i = 0 ; $i < 2 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT".($i+1);
            $contact->last_name = create_guid();
            $contact->title = sprintf("%08d",($i+1));
            $contact->save();

            $this->contacts[] = $contact;

            $contact_type = $cts[($i%$ctsCount)];
            $this->contacts[$i]->contact_role = $contact_type;
        }
        for ( $i = 0 ; $i < 1 ; $i++ ) {
            $opp = new Opportunity();
            $opp->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $opp->amount = (10000*$i)+500;
            $opp->date_closed = '2014-12-'.($i+1);
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;

            $contactNums = array(0,1);
            $opp->load_relationship('contacts');
            foreach ( $contactNums as $contactNum ) {
                $opp->contacts->add(array($this->contacts[$contactNum]),array('contact_role'=>$this->contacts[$contactNum]->contact_role));
            }

        }

        $restReply = $this->_restCall("Opportunities/".$this->opps[0]->id."/link/contacts/".$this->contacts[1]->id,
                                      json_encode(array(
                                                      'last_name'=>"Test O'Chango",
                                      )),'PUT');


        $update_fields = count($restReply['reply']['related_record']);
        // test fetch vs create
        $this->assertEquals($fetch_fields, $create_fields, "Number of fields doesn't match");

        // test fetch vs update
        $this->assertEquals($fetch_fields, $update_fields, "Number of fields doesn't match");

    }

    public function testCreateRelatedRecord() {
        global $db;

        for ( $i = 0 ; $i < 1 ; $i++ ) {
            $opp = new Opportunity();
            $opp->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $opp->amount = (10000*$i)+500;
            $opp->date_closed = '2014-12-'.($i+1);
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;
        }

        $restReply = $this->_restCall("Opportunities/".$this->opps[0]->id."/link/contacts",
                                      json_encode(array(
                                                      'last_name'=>'TEST',
                                                      'first_name'=>'UNIT',
                                                      'contact_role'=>'Primary Decision Maker',
                                                      'description'=>'UNIT TEST CONTACT'
                                      )),'POST');

        $contact = new Contact();
        $contact->retrieve($restReply['reply']['related_record']['id']);
        // Save it here so it gets deleted later
        $this->contacts[] = $contact;

        $this->assertTrue(!empty($restReply['reply']['related_record']['date_entered']), "Date Entered was not set on the creat retrun of the related record");


        $ret = $db->query("SELECT * FROM opportunities_contacts WHERE opportunity_id ='".$this->opps[0]->id."' AND contact_id = '".$this->contacts[0]->id."'");
        
        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('Primary Decision Maker',$row['contact_role'],"Did not set the related contact's role");
    }

    public function testUpdateRelatedLink() {
        global $db;

        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, throw it away
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least two of the related modules
        for ( $i = 0 ; $i < 2 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT".($i+1);
            $contact->last_name = create_guid();
            $contact->title = sprintf("%08d",($i+1));
            $contact->save();

            $this->contacts[] = $contact;

            $contact_type = $cts[($i%$ctsCount)];
            $this->contacts[$i]->contact_role = $contact_type;
        }
        for ( $i = 0 ; $i < 1 ; $i++ ) {
            $opp = new Opportunity();
            $opp->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $opp->amount = (10000*$i)+500;
            $opp->date_closed = '2014-12-'.($i+1);
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;

            $contactNums = array(0,1);
            $opp->load_relationship('contacts');
            foreach ( $contactNums as $contactNum ) {
                $opp->contacts->add(array($this->contacts[$contactNum]),array('contact_role'=>$this->contacts[$contactNum]->contact_role));
            }

        }

        $restReply = $this->_restCall("Opportunities/".$this->opps[0]->id."/link/contacts/".$this->contacts[1]->id,
                                      json_encode(array(
                                                      'contact_role'=>'Primary Decision Maker',
                                                      'last_name'=>"Test O Chango",
                                      )),'PUT');

        $this->assertTrue(!empty($restReply['reply']['related_record']['date_entered']), "Date Entered was not set in the Update related record reply");
        $this->assertEquals($this->contacts[1]->id,$restReply['reply']['related_record']['id'],"Changed the related ID when it shouldn't have");
        $this->assertEquals("Test O Chango",$restReply['reply']['related_record']['last_name'],"Did not change the related contact");
        // FIXME: Need to wait for this to be repaired in link2.php
        // $this->assertEquals($this->contacts[1]->contact_role,$restReply['reply']['related_record']['contact_role'],"Did not fetch the related contact's role");

        
        $ret = $db->query("SELECT * FROM opportunities_contacts WHERE opportunity_id ='".$this->opps[0]->id."' AND contact_id = '".$this->contacts[1]->id."'");
        
        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('Primary Decision Maker',$row['contact_role'],"Did not set the related contact's role");
        
    }

    public function testCreateRelatedLink() {
        global $db;

        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, throw it away
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least two of the related modules
        for ( $i = 0 ; $i < 2 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT".($i+1);
            $contact->last_name = create_guid();
            $contact->title = sprintf("%08d",($i+1));
            $contact->save();

            $this->contacts[] = $contact;

            $contact_type = $cts[($i%$ctsCount)];
            $this->contacts[$i]->contact_role = $contact_type;
        }
        for ( $i = 0 ; $i < 1 ; $i++ ) {
            $opp = new Opportunity();
            $opp->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $opp->amount = (10000*$i)+500;
            $opp->date_closed = '2014-12-'.($i+1);
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;
        }

        $restReply = $this->_restCall("Opportunities/".$this->opps[0]->id."/link/contacts/".$this->contacts[1]->id,
                                      json_encode(array(
                                                      'contact_role'=>$this->contacts[1]->contact_role,
                                      )),'POST');

        $this->assertEquals($this->contacts[1]->id,$restReply['reply']['related_record']['id'],"Did not link the related contact");
        // FIXME: Need to wait for this to be repaired in link2.php
        // $this->assertEquals($this->contacts[1]->contact_role,$restReply['reply']['related_record']['contact_role'],"Did not fetch the related contact's role");

        
        $ret = $db->query("SELECT * FROM opportunities_contacts WHERE opportunity_id ='".$this->opps[0]->id."' AND contact_id = '".$this->contacts[1]->id."'");
        
        $row = $db->fetchByAssoc($ret);
        $this->assertEquals($this->contacts[1]->contact_role,$row['contact_role'],"Did not set the related contact's role");
        
    }

    public function testDeleteRelatedLink() {
        global $db;

        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, throw it away
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least two of the related modules
        for ( $i = 0 ; $i < 2 ; $i++ ) {
            $contact = new Contact();
            $contact->first_name = "UNIT".($i+1);
            $contact->last_name = create_guid();
            $contact->title = sprintf("%08d",($i+1));
            $contact->save();

            $this->contacts[] = $contact;

            $contact_type = $cts[($i%$ctsCount)];
            $this->contacts[$i]->contact_role = $contact_type;
        }
        for ( $i = 0 ; $i < 1 ; $i++ ) {
            $opp = new Opportunity();
            $opp->name = "UNIT TEST ".($i+1)." - ".create_guid();
            $opp->amount = (10000*$i)+500;
            $opp->date_closed = '2014-12-'.($i+1);
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;

            $contactNums = array(0,1);
            $opp->load_relationship('contacts');
            foreach ( $contactNums as $contactNum ) {
                $opp->contacts->add(array($this->contacts[$contactNum]),array('contact_role'=>$this->contacts[$contactNum]->contact_role));
            }

        }

        $ret = $db->query("SELECT COUNT(*) AS link_count FROM opportunities_contacts WHERE opportunity_id ='".$this->opps[0]->id."' AND deleted = 0");
        
        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('2',$row['link_count'],"The links were not properly generated");

        $restReply = $this->_restCall("Opportunities/".$this->opps[0]->id."/link/contacts/".$this->contacts[1]->id,
                                      '','DELETE');

        $ret = $db->query("SELECT COUNT(*) AS link_count FROM opportunities_contacts WHERE opportunity_id ='".$this->opps[0]->id."' AND deleted = 0");
        
        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('1',$row['link_count'],"The first link was not properly deleted");

        $ret = $db->query("SELECT COUNT(*) AS link_count FROM opportunities_contacts WHERE opportunity_id ='".$this->opps[0]->id."' AND contact_id = '".$this->contacts[0]->id."' AND deleted = 0");
        
        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('1',$row['link_count'],"The wrong link was deleted");

        $restReply = $this->_restCall("Opportunities/".$this->opps[0]->id."/link/contacts/".$this->contacts[0]->id,
                                      '','DELETE');

        $ret = $db->query("SELECT COUNT(*) AS link_count FROM opportunities_contacts WHERE opportunity_id ='".$this->opps[0]->id."' AND deleted = 0");
        
        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('0',$row['link_count'],"The second link was not properly deleted");

        $ret = $db->query("SELECT COUNT(*) AS link_count FROM opportunities_contacts WHERE opportunity_id ='".$this->opps[0]->id."' AND contact_id = '".$this->contacts[0]->id."' AND deleted = 0");
        
        $row = $db->fetchByAssoc($ret);
        $this->assertEquals('0',$row['link_count'],"The second link was never deleted");


    }

    
}
