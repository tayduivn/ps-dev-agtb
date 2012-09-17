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

require_once('modules/Leads/LeadConvert.php');

class LeadConvertTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $lead;

    public function setUp() {
        $this->lead = SugarTestLeadUtilities::createLead();
        $this->lead->id = $this->lead->id;
        $this->modules = array(
            'Contacts' => array(
                'copyData' => true,
                'required' => true,
                'view' => 'convert',
                'context' => array(
                    'module' => 'Prospects'
                ),
            ),
            'Accounts' => array(
                'copyData' => true,
                'required' => true,
                'contactRelateField' => 'account_name',
                'view' => 'convert',
                'context' => array(
                    'module' => 'ProspectLists'
                ),
            ),
            'Opportunities' => array(
                'copyData' => true,
                'required' => true,
                'view' => 'convert',
                'context' => array(
                    'module' => 'Leads'
                ),
            ),
            'Tasks' => array(
                'copyData' => true,
                'required' => true,
                'view' => 'convert',
                'context' => array(
                    'module' => 'Leads'
                ),
            )
        );
    }

    public function tearDown() {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        unset($this->lead);
        unset($this->modules);
    }

    /**
     * @group leadconvert
     */
    public function testInitialize_Successful() {
        $leadConvert = $this->getMock('LeadConvert', array('getVarDefs'), array($this->lead->id));

        $leadConvert->expects($this->once())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modules));

        $leadConvert->initialize($this->lead->id);
    }

    /**
     * @group leadconvert
     */
    public function testInitialize_InvalidLeadId_ThrowsException() {
        $leadConvert = $this->getMock('LeadConvert', array('getVarDefs'), array($this->lead->id));

        $leadConvert->expects($this->once())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modules));

        $this->setExpectedException('Exception');

        $leadConvert->initialize('abcd');
    }

    /**
     * @group leadconvert
     * @dataProvider providerDataAddLogForContactInCampaign
     */
    public function testAddLogForContactInCampaign_LogsProperlyWhenCorrectDataSet($hasCampaign, $hasContact, $expected) {
        $leadConvert = $this->getMock('LeadConvert', array('addCampaignLog'), array($this->lead->id));

        if ($hasCampaign) {
            $campaign = SugarTestCampaignUtilities::createCampaign();
            $leadConvert->getLead()->campaign_id = $campaign->id;
        }

        if($hasContact) {
            $leadConvert->setContact(SugarTestContactUtilities::createContact());
        }
        $leadConvert->expects($this->exactly($expected))
            ->method('addCampaignLog');


        $leadConvert->AddLogForContactInCampaign();
    }

    /**
     * @return array
     */
    public function providerDataAddLogForContactInCampaign()
    {
        return array(
            array(true, true, 1),
            array(true, false, 0),
            array(false, false, 0),
            array(false, true, 0)
        );
    }

    /**
     * @group leadconvert
     */
    public function testFindRelationship_ReturnsCorrectRelationKey()
    {
        $leadConvert = $this->getMock('LeadConvert', array('getVarDefs'), array($this->lead->id));

        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();

        //Relationship is in own def and based on lhs

        $relationshipField = $leadConvert->findRelationship($account, $contact);
        $this->assertEquals('contacts', $relationshipField, "Relationship is not correct From:" . $account->name . "To:" . $contact->name);

        //Relationship is in from module def in the relationship section
        $to = SugarTestProductUtilities::createProduct();
        $relationshipField = $leadConvert->findRelationship($contact, $to);
        $this->assertEquals('products', $relationshipField, "Relationship is not correct From:" . $contact->name . "To:" . $to->name);

        //Relationship is in 'to' module def in the relationship section
        $from = SugarTestProductUtilities::createProduct();
        $to = SugarTestQuoteUtilities::createQuote();
        $relationshipField = $leadConvert->findRelationship($from, $to);
        $this->assertEquals('quotes', $relationshipField, "Relationship is not correct From:" . $from->name . "To:" . $to->name);

        //Relationship is in 'to' module def in the relationship section
        $from = SugarTestMeetingUtilities::createMeeting();
        $to = SugarTestProductUtilities::createProduct();
        $relationshipField = $leadConvert->findRelationship($from, $to);
        $this->assertEquals(false, $relationshipField, "Relationship is not correct From:" . $contact->name . "To:" . $to->name);
   }

    /**
     * @group leadconvert
     */
    public function testSetRelationshipForModulesToLeads_OneToManyRelationship_RelationshipIsStoredOnlead()
    {
        $leadConvert = $this->getMock('LeadConvert', array('getVarDefs'), array($this->lead->id));

        $account = SugarTestAccountUtilities::createAccount();

        $leadConvert->setModules(array('Accounts' => $account));

        $this->assertNull($leadConvert->getLead()->account_id);

        $leadConvert->setRelationshipForModulesToLeads('Accounts');

        $lead = $leadConvert->getLead();
        $this->assertNotNull($lead->account_id);
        $this->assertEquals($account->id, $lead->account_id);
        $this->assertTrue($lead->load_relationship("accounts"));
        $this->assertInstanceOf("Link2", $lead->accounts);
        $this->assertTrue($lead->accounts->loadedSuccesfully());
    }

    /**
     * @group leadconvert
     */
    public function testSetRelationshipForModulesToLeads_NotOneToManyRelationship_RelationshipIsAddedToModule_NotLead()
    {
        $leadConvert = $this->getMock('LeadConvert', array('getVarDefs'), array($this->lead->id));

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $leadConvert->setModules(array('Meetings' => $meeting));

        $leadConvert->setRelationshipForModulesToLeads('Meetings');

        $lead = $leadConvert->getLead();

        $this->assertTrue($lead->load_relationship("meetings"));
        $this->assertInstanceOf("Link2", $lead->meetings);
        $this->assertTrue($lead->meetings->loadedSuccesfully());

        $related = $lead->meetings->getBeans();
        $this->assertNotEmpty($related);
        $this->assertNotEmpty($related[$meeting->id]);

    }

/**
     * @group leadconvert
     */
    public function testSetRelationshipsForModulesToContacts_ContactRelatedFieldInVarDef_FieldOnContactSet()
    {
        $leadConvert = $this->getMock('LeadConvert', array('getVarDefs'), array($this->lead->id));
        $leadConvert->expects($this->once())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modules));

        $leadConvert->initialize($this->lead->id);

        $account = SugarTestAccountUtilities::createAccount();
        $contact = SugarTestContactUtilities::createContact();

        $leadConvert->setModules(array('Accounts' => $account,
            'Contacts' => $contact));
        $leadConvert->setContact($contact);

        $leadConvert->setRelationshipsForModulesToContacts('Accounts');

        $contact = $leadConvert->getContact();

        $this->assertTrue($contact->load_relationship("accounts"));

        $this->assertInstanceOf("Link2", $contact->accounts);
        $this->assertTrue($contact->accounts->loadedSuccesfully());

        $related = $contact->accounts->getBeans();
        $this->assertEmpty($related);

        $relatedField = $this->modules['Accounts']['contactRelateField'];
        $this->assertEquals($account->name, $contact->$relatedField);
    }

    /**
     * @group leadconvert
     */
    public function testSetRelationshipsForModulesToContacts_ManyToManyRelationship_RelationshipIsAddToContact()
    {
        $leadConvert = $this->getMock('LeadConvert', array('getVarDefs'), array($this->lead->id));
        $leadConvert->expects($this->once())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modules));

        $leadConvert->initialize($this->lead->id);

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $contact = SugarTestContactUtilities::createContact();

        $leadConvert->setModules(array('Opportunities' => $opp,
            'Contacts' => $contact));
        $leadConvert->setContact($contact);

        $leadConvert->setRelationshipsForModulesToContacts('Opportunities');

        $contact = $leadConvert->getContact();

        $this->assertTrue($contact->load_relationship("opportunities"));

        $this->assertInstanceOf("Link2", $contact->opportunities);
        $this->assertTrue($contact->opportunities->loadedSuccesfully());

        $related = $contact->opportunities->getBeans();
        $this->assertNotEmpty($related);
        $this->assertNotEmpty($related[$opp->id]);
    }

    /**
     * @group leadconvert
     */
    public function testSetRelationshipsForModulesToContacts_OneToManyRelationship_RelationshipIsAdded_FieldOnContactSet()
    {
        $leadConvert = $this->getMock('LeadConvert', array('getVarDefs'), array($this->lead->id));
        $leadConvert->expects($this->once())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modules));

        $leadConvert->initialize($this->lead->id);

        $task = SugarTestTaskUtilities::createTask();
        $contact = SugarTestContactUtilities::createContact();

        $leadConvert->setModules(array('Tasks' => $task,
            'Contacts' => $contact));
        $leadConvert->setContact($contact);

        $leadConvert->setRelationshipsForModulesToContacts('Tasks');

        $contact = $leadConvert->getContact();

        $this->assertTrue($contact->load_relationship("tasks"));

        $this->assertInstanceOf("Link2", $contact->tasks);
        $this->assertTrue($contact->tasks->loadedSuccesfully());

        $related = $contact->tasks->getBeans();
        $this->assertEmpty($related);

        $modules = $leadConvert->getModules();
        $this->assertEquals($contact->id, $modules['Tasks']->contact_id);
    }

    /**
     * @group leadconvert
     */
    public function testConvertLead_NoOpportunity_LeadIsConverted()
    {
        $task = SugarTestTaskUtilities::createTask();
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();

        $modules = array(
            'Tasks' => $task,
            'Contacts' => $contact,
            'Accounts' => $account
        );

        $leadConvert = $this->getMock('LeadConvert',
            array('getVarDefs','setRelationshipsForModulesToContacts', 'setAssignedForModulesToLeads', 'setRelationshipForModulesToLeads', 'addLogForContactInCampaign', 'updateOpportunityWithAccountInformation'), array($this->lead->id));
        $leadConvert->expects($this->once())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modules));
        $leadConvert->expects($this->exactly(2))
            ->method('setRelationshipsForModulesToContacts');
        $leadConvert->expects($this->exactly(3))
            ->method('setAssignedForModulesToLeads');
        $leadConvert->expects($this->exactly(3))
            ->method('setRelationshipForModulesToLeads');
        $leadConvert->expects($this->once())
            ->method('addLogForContactInCampaign');
        $leadConvert->expects($this->never())
            ->method('updateOpportunityWithAccountInformation');

        $leadConvert->initialize($this->lead->id);
        $leadConvert->convertLead($modules);


        $lead = BeanFactory::getBean('Leads', $this->lead->id);

        $this->assertEquals(LeadConvert::STATUS_CONVERTED, $lead->status, 'Lead status field was not changed properly.');
        $this->assertEquals(true, $lead->converted, 'Lead converted field not set properly');
        $this->assertEquals(true, $lead->in_workflow, 'Lead workflow field not set properly');
    }

    /**
     * @group leadconvert
     */
    public function testConvertLead_NoContact_LeadIsConverted()
    {
        $task = SugarTestTaskUtilities::createTask();
        $account = SugarTestAccountUtilities::createAccount();
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $modules = array(
            'Tasks' => $task,
            'Accounts' => $account,
            'Opportunities' => $opp
        );

        $leadConvert = $this->getMock('LeadConvert',
            array('getVarDefs','setRelationshipsForModulesToContacts', 'setAssignedForModulesToLeads', 'setRelationshipForModulesToLeads', 'addLogForContactInCampaign', 'updateOpportunityWithAccountInformation'), array($this->lead->id));
        $leadConvert->expects($this->once())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modules));
        $leadConvert->expects($this->never())
            ->method('setRelationshipsForModulesToContacts');
        $leadConvert->expects($this->exactly(3))
            ->method('setAssignedForModulesToLeads');
        $leadConvert->expects($this->exactly(3))
            ->method('setRelationshipForModulesToLeads');
        $leadConvert->expects($this->never())
            ->method('addLogForContactInCampaign');
        $leadConvert->expects($this->once())
            ->method('updateOpportunityWithAccountInformation');

        $leadConvert->initialize($this->lead->id);
        $leadConvert->convertLead($modules);


        $lead = BeanFactory::getBean('Leads', $this->lead->id);

        $this->assertEquals(LeadConvert::STATUS_CONVERTED, $lead->status, 'Lead status field was not changed properly.');
        $this->assertEquals(true, $lead->converted, 'Lead converted field not set properly');
        $this->assertEquals(true, $lead->in_workflow, 'Lead workflow field not set properly');
    }

    /**
     * @group leadconvert
     */
    public function testConvertLead_WithOpportunity_LeadIsConverted()
    {
        $task = SugarTestTaskUtilities::createTask();
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $modules = array(
            'Tasks' => $task,
            'Contacts' => $contact,
            'Accounts' => $account,
            'Opportunities' => $opp
        );

        $leadConvert = $this->getMock('LeadConvert',
            array('getVarDefs','setRelationshipsForModulesToContacts', 'setAssignedForModulesToLeads', 'setRelationshipForModulesToLeads', 'addLogForContactInCampaign', 'updateOpportunityWithAccountInformation'), array($this->lead->id));
        $leadConvert->expects($this->once())
            ->method('getVarDefs')
            ->will($this->returnValue($this->modules));
        $leadConvert->expects($this->exactly(3))
            ->method('setRelationshipsForModulesToContacts');
        $leadConvert->expects($this->exactly(4))
            ->method('setAssignedForModulesToLeads');
        $leadConvert->expects($this->exactly(4))
            ->method('setRelationshipForModulesToLeads');
        $leadConvert->expects($this->once())
            ->method('addLogForContactInCampaign');
        $leadConvert->expects($this->once())
            ->method('updateOpportunityWithAccountInformation');

        $leadConvert->initialize($this->lead->id);
        $leadConvert->convertLead($modules);


        $lead = BeanFactory::getBean('Leads', $this->lead->id);

        $this->assertEquals(LeadConvert::STATUS_CONVERTED, $lead->status, 'Lead status field was not changed properly.');
        $this->assertEquals(true, $lead->converted, 'Lead converted field not set properly');
        $this->assertEquals(true, $lead->in_workflow, 'Lead workflow field not set properly');
    }
}