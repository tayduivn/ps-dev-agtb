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

require_once 'modules/Leads/views/view.convertlead.php';


class ConvertLeadTests extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['current_user']);
    }
    
	/**
	 * @group bug39787
	 */
    public function testOpportunityNameValueFilled(){
        $lead = SugarTestLeadUtilities::createLead();
        $lead->opportunity_name = 'SBizzle Dollar Store';
        $lead->save();
        
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'ConvertLead';
        $_REQUEST['record'] = $lead->id;
        
        // Check that the opportunity name doesn't get populated when it's not in the Leads editview layout
        require_once('include/MVC/Controller/ControllerFactory.php');
        require_once('include/MVC/View/ViewFactory.php');
        $GLOBALS['app']->controller = ControllerFactory::getController($_REQUEST['module']);
        ob_start();
        $GLOBALS['app']->controller->execute();
        $output = ob_get_clean();
        
        $matches_one = array();
        $pattern = '/SBizzle Dollar Store/';
        preg_match($pattern, $output, $matches_one);
        $this->assertTrue(count($matches_one) == 0, "Opportunity name got carried over to the Convert Leads page when it shouldn't have.");

        // Add the opportunity_name to the Leads EditView
        SugarTestStudioUtilities::addFieldToLayout('Leads', 'editview', 'opportunity_name');
        
        // Check that the opportunity name now DOES get populated now that it's in the Leads editview layout
        ob_start();
        $GLOBALS['app']->controller = ControllerFactory::getController($_REQUEST['module']);
        $GLOBALS['app']->controller->execute();
        $output = ob_get_clean();
        $matches_two = array();
        $pattern = '/SBizzle Dollar Store/';
        preg_match($pattern, $output, $matches_two);
        $this->assertTrue(count($matches_two) > 0, "Opportunity name did not carry over to the Convert Leads page when it should have.");
        
        SugarTestStudioUtilities::removeAllCreatedFields();
        unset($GLOBALS['app']->controller);
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
        unset($_REQUEST['record']);
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }
    /**
     * @group bug44033
     */
    public function testActivityMove() {
        // init
        $lead = SugarTestLeadUtilities::createLead();
        $contact = SugarTestContactUtilities::createContact();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // refresh the meeting to include parent_id and parent_type
        $meeting_id = $meeting->id;
        $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // action: move meeting from lead to contact
        $convertObj = new TestViewConvertLead();
        $convertObj->moveActivityWrapper($meeting, $contact);

        // verification 1, parent id should be contact id
        $this->assertTrue($meeting->parent_id == $contact->id, 'Meeting parent id is not converted to contact id.');

        // verification 2, parent type should be "Contacts"
        $this->assertTrue($meeting->parent_type == 'Contacts', 'Meeting parent type is not converted to Contacts.');

        // verification 3, record should be deleted from meetings_leads table
        $sql = "select id from meetings_leads where meeting_id='{$meeting->id}' and lead_id='{$lead->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertNull($row, "Meeting-Lead relationship is not removed.");

        // verification 4, record should be added to meetings_contacts table
        $sql = "select id from meetings_contacts where meeting_id='{$meeting->id}' and contact_id='{$contact->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Contact relationship is not added.");

        // clean up
        unset($_REQUEST['record']);
        SugarTestMeetingUtilities::deleteMeetingLeadRelation($relation_id);
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }
}

class TestViewConvertLead extends ViewConvertLead
{
    public function moveActivityWrapper($activity, $bean) {
        parent::moveActivity($activity, $bean);
    }
}
