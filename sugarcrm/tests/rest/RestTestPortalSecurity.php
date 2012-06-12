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

class RestTestPortalSecurity extends RestTestBase {
    public function setUp()
    {
        global $db;

        parent::setUp();

        // Disable the other portal users
        $this->oldPortal = array();
        $ret = $db->query("SELECT id FROM users WHERE portal_only = '1' AND deleted = '0'");
        while ( $row = $db->fetchByAssoc($ret) ) {
            $this->oldPortal[] = $row['id'];
        }
        $db->query("UPDATE users SET deleted = '1' WHERE portal_only = '1'");

        $this->_user->portal_only = '1';
        $this->_user->save();
        $this->role = $this->_getPortalACLRole();
        if (!($this->_user->check_role_membership($this->role->name))) {
            $this->_user->load_relationship('aclroles');
            $this->_user->aclroles->add($this->role);
            $this->_user->save();
        }

        // A little bit destructive, but necessary.
        $db->query("DELETE FROM contacts WHERE portal_name = 'unittestportal'");

        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
        $this->accounts = array();
        $this->contacts = array();
        $this->opps = array();
        $this->cases = array();
        $this->bugs = array();
    }
    public function tearDown()
    {
        global $db;
        // Re-enable the old portal users
        $portalIds = "('".implode("','",$this->oldPortal)."')";
        $db->query("UPDATE users SET deleted = '0' WHERE id IN {$portalIds}");

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
        foreach ( $this->cases as $acase ) {
            $caseIds[] = $acase->id;
        }
        $caseIds = "('".implode("','",$caseIds)."')";
        $bugIds = array();
        foreach ( $this->bugs as $bug ) {
            $bugIds[] = $bug->id;
        }
        $bugIds = "('".implode("','",$bugIds)."')";
        
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
        $GLOBALS['db']->query("DELETE FROM accounts_cases WHERE case_id IN {$caseIds}");
        $GLOBALS['db']->query("DELETE FROM bugs WHERE id IN {$bugIds}");
        $GLOBALS['db']->query("DELETE FROM bugs_cstm WHERE id_c IN {$bugIds}");
        $GLOBALS['db']->query("DELETE FROM cases_bugs WHERE bug_id IN {$bugIds}");
        
        parent::tearDown();
    }


    protected function _restLogin($username = '', $password = '')
    {
        $args = array(
            'grant_type' => 'password',
            'username' => 'unittestportal',
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
        );
        
        // Prevent an infinite loop, put a fake authtoken in here.
        $this->authToken = 'LOGGING_IN';

        $reply = $this->_restCall('oauth2/token',json_encode($args));
        if ( empty($reply['reply']['access_token']) ) {
            throw new Exception("Rest authentication failed, message looked like: ".$reply['replyRaw']);
        }
        $this->authToken = $reply['reply']['access_token'];
        $this->refreshToken = $reply['reply']['refresh_token'];
    }
    
    // Copied from parser.portalconfig.php, when that gets merged we should probably just abuse that function.
    protected function _getPortalACLRole()
    {
        $allowedModules = array('Accounts','Bugs', 'Cases', 'Notes', 'KBDocuments', 'Contacts');
        $allowedActions = array('edit', 'admin', 'access', 'list', 'view');
        $role = new ACLRole();
        $role->retrieve_by_string_fields(array('name' => 'Customer Self-Service Portal Role'));
        $role->name = "Customer Self-Service Portal Role";
        $role->description = "Customer Self-Service Portal Role";
        $role->save();
        $roleActions = $role->getRoleActions($role->id);
        foreach ($roleActions as $moduleName => $actions) {
            // enable allowed moduels
            if (isset($actions['module']['access']['id']) && !in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_DISABLED);
            } elseif (isset($actions['module']['access']['id']) && in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
            } else {
                foreach ($actions as $action => $actionName) {
                    if (isset($actions[$action]['access']['id'])) {
                        $role->setAction($role->id, $actions[$action]['access']['id'], ACL_ALLOW_DISABLED);
                    }
                }
            }
            
            if (in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
                $role->setAction($role->id, $actions['module']['admin']['id'], ACL_ALLOW_ALL);
                foreach ($actions['module'] as $actionName => $action) {
                    if (in_array($actionName, $allowedActions)) {
                        $aclAllow = ACL_ALLOW_ALL;
                    } else {
                        $aclAllow = ACL_ALLOW_NONE;
                    }
                    if ($moduleName == 'KBDocuments' && $actionName == 'edit') {
                        $aclAllow = ACL_ALLOW_NONE;
                    }
                    if ($moduleName == 'Contacts') {
                        if ($actionName == 'edit' ) {
                            $aclAllow = ACL_ALLOW_OWNER;
                        }
                    }
                    if ($moduleName == 'Accounts' && $actionName == 'edit') {
                        $aclAllow = ACL_ALLOW_NONE;
                    }
                    $role->setAction($role->id, $action['id'], $aclAllow);
                }
            }
            
        }
        return $role;
    }

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
            $acase->accounts->add(array($this->accounts[($i%3)]));

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

            // 2 out of 3 cases have bugs
            if ( ($i%3) < 2 ) {
                $bug = new Bug();
                $bug->name = "UNIT TEST ".($i+1)." - ".create_guid();
                $bug->work_log = "The portal should never see this.";
                $bug->description = "The portal can see this.";
                
                //BEGIN SUGARCRM flav=ent ONLY
                if ( $i%2 == 1 ) {
                    $bug->portal_viewable = true;
                }
                //END SUGARCRM flav=ent ONLY

                $bug->save();
                $this->bugs[] = $bug;
                
                $bug->load_relationship('cases');
                $bug->cases->add(array($acase));
            }

        }
        

        // Negative test: Try and fetch a Contact you shouldn't be able to see
        $restReply = $this->_restCall("Contacts/".$this->contacts[2]->id);
        $this->assertContains('ERROR',$restReply['replyRaw']);

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
        $this->assertContains('ERROR',$restReply['replyRaw']);
        
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
        $this->assertContains('ERROR',$restReply['replyRaw']);

        // Positive test: Fetch a Account that you should be able to see
        $restReply = $this->_restCall("Accounts/".$this->accounts[1]->id);
        $this->assertEquals($this->accounts[1]->id,$restReply['reply']['id']);

        // Positive test: Fetch the other Account that you should be able to see
        $restReply = $this->_restCall("Accounts/".$this->accounts[2]->id);
        $this->assertEquals($this->accounts[2]->id,$restReply['reply']['id']);

        // Negative test: Should not be able to create a new Account
        $restReply = $this->_restCall("Accounts/",json_encode(array('name'=>'UnitTestNew')),'POST');
        $this->assertContains('ERROR',$restReply['replyRaw']);
        
        $restReply = $this->_restCall("Accounts");

        foreach ( $restReply['reply']['records'] as $record ) {
            // We should be linked to accounts[1] and accounts[2]
            $this->assertNotEquals($this->accounts[0]->id,$record['id']);
            $foundOne = ($record['id']==$this->accounts[1]->id)
                ||($record['id']==$this->accounts[2]->id);
            $this->assertTrue($foundOne);
        }

        // Negative test: We should not be able to fetch an Opportunity
        $restReply = $this->_restCall("Opportunities/".$this->opps[1]->id);
        $this->assertContains('ERROR',$restReply['replyRaw']);

        // Negative test: We should not be able to list opportunities
        $restReply = $this->_restCall("Opportunities/");
        $this->assertEquals(-1,$restReply['reply']['next_offset']);
        
        // Negative test: Should not be able to create a new Opportunity
        $restReply = $this->_restCall("Opportunities/",json_encode(array('name'=>'UnitTestNew','account_id'=>$this->accounts[1]->id,'expected_close_date'=>'2012-10-11 12:00:00')),'POST');
        $this->assertContains('ERROR',$restReply['replyRaw']);

    }
}
