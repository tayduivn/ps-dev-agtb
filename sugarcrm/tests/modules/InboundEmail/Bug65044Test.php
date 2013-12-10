<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


require_once("modules/InboundEmail/InboundEmail.php");

/**
 * Bug #65044
 * Inbound Email to Case creation does not automatically link up to the Account
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 65044
*/
class Bug65044Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $ie;
    private $account;
    private $contact;

    public function setUp()
    {
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("app_strings");

        SugarTestHelper::setUp("current_user");

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->name = "Boro SugarTest 65044";
        $this->account->save();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->first_name = "Boro";
        $this->contact->last_name = "SugarTest 65044";
        $this->contact->email1 = "bsitnikovskiBug65044Test@sugarcrm.com";
        $this->contact->save();

        $this->ie = new InboundEmail();
        $this->ie->name = $this->ie->casename = "[CASE:Bug65044] Bug65044 Test";
        $this->ie->description = "This is a test for Bug65044";
        $this->ie->mailbox_type = "createcase";
        $this->ie->groupfolder_id = "non-empty";
        $this->ie->from_addr = $this->contact->email1;

        //BEGIN SUGARCRM flav=pro ONLY
        $teamId = $GLOBALS["current_user"]->getPrivateTeam();
        $this->ie->team_id = $_REQUEST["team_id"] = $teamId;
        $this->ie->team_set_id = $_REQUEST["team_set_id"] = $this->ie->getTeamSetIdForTeams($teamId);
        //END SUGARCRM flav=pro ONLY

        $this->ie->save();
    }

    public function tearDown()
    {
        $GLOBALS["db"]->query("DELETE FROM inbound_email WHERE id = '{$this->ie->id}'");
        $GLOBALS["db"]->query("DELETE FROM cases WHERE name = '{$this->ie->casename}'");
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeCreatedContactsEmailAddresses();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    private function getCase()
    {
        // Cache intentionally bypassed
        $case = BeanFactory::newBean("Cases");
        $case->retrieve($this->ie->parent_id);

        $this->assertTrue($case->load_relationship("accounts"));

        $this->assertTrue($case->load_relationship("contacts"));

        return $case;
    }

    public function testContactWithAccountLink()
    {
        // link contact to accounts
        $this->assertTrue($this->contact->load_relationship("accounts"));
        $this->contact->accounts->add($this->account->id);

        $this->ie->handleCreateCase($this->ie, $GLOBALS["current_user"]->id);

        $case = $this->getCase();

        $this->assertContains($this->account->id, $case->accounts->get());
        $this->assertContains($this->contact->id, $case->contacts->get());
    }

    public function testContactWithoutAccountLink()
    {
        $this->ie->handleCreateCase($this->ie, $GLOBALS["current_user"]->id);
        $case = $this->getCase();

        $this->assertNotContains($this->account->id, $case->accounts->get());
        $this->assertContains($this->contact->id, $case->contacts->get());
    }

    public function testContactAccountEmail()
    {
        // set same e-mail address
        $this->account->email1 = $this->contact->email1;
        $this->account->save();

        $this->ie->handleCreateCase($this->ie, $GLOBALS["current_user"]->id);

        $case = $this->getCase();

        $this->assertContains($this->account->id, $case->accounts->get());
        $this->assertContains($this->contact->id, $case->contacts->get());
    }
}
