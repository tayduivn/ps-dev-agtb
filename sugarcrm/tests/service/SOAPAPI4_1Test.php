<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('include/nusoap/nusoap.php');
require_once 'tests/service/SOAPTestCase.php';
require_once('tests/service/APIv3Helper.php');


class SOAPAPI4_1Test extends SOAPTestCase
{
    protected $contact1;
    protected $contact2;
    protected $another_user;
    protected $meeting1;
    protected $meeting2;
    protected $meeting3;

    /**
     * setUp
     *
     */
	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        parent::setUp();
        $this->_login();
        global $current_user;

        $this->another_user = SugarTestUserUtilities::createAnonymousUser();

        $this->contact1 = SugarTestContactUtilities::createContact();
        $this->contact1->contacts_users_id = $current_user->id;
        $this->contact1->first_name = 'First1';
        $this->contact1->last_name = 'Last1';
        $this->contact1->save();

        $this->contact1->user_sync->add($current_user);
        $this->contact1->sync_contact = 1;
        $this->contact1->save();

        $this->contact2 = SugarTestContactUtilities::createContact();
        $this->contact2->contacts_users_id = $this->another_user->id;
        $this->contact2->first_name = 'First2';
        $this->contact2->last_name = 'Last2';
        $this->contact2->save();

        $this->contact2->user_sync->add($this->another_user);
        $this->contact2->sync_contact = 1;
        $this->contact2->save();

        $this->meeting1 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting1->name = 'SOAPAPI4_1Test1';
        $this->meeting1->load_relationship('users');
        $this->meeting1->users->add($current_user);
        $this->meeting1->save();

        $this->meeting2 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting2->name = 'SOAPAPI4_1Test2';
        $this->meeting2->load_relationship('users');
        $this->meeting2->users->add($this->another_user);
        $this->meeting2->save();

        $this->meeting3 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting3->name = 'SOAPAPI4_1Test3';
        $this->meeting3->load_relationship('users');
        $this->meeting3->users->add($current_user);
        $this->meeting3->save();
        $GLOBALS['db']->commit();
    }

    /**
     * tearDown
     *
     */
    public function tearDown()
    {
        parent::tearDown();
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
    }

    /**
     * testGetModifiedRelationships
     *
     */
    public function testGetModifiedRelationships()
    {
        global $timedate, $current_user;
        $one_hour_ago = $timedate->asDb($timedate->getNow()->get("-1 hours"));
        $one_hour_later = $timedate->asDb($timedate->getNow()->get("+1 hours"));
        $callsAndMeetingsFields = array('id', 'date_modified', 'deleted', 'name', 'rt.deleted synced');
        $contactsSelectFields = array('id', 'date_modified', 'deleted', 'first_name', 'last_name', 'rt.deleted synced');

       	$result = $this->_soapClient->call('get_modified_relationships', array('session' => $this->_sessionId, 'module_name' => 'Users', 'related_module' => 'Meetings', 'from_date' => $one_hour_ago, 'to_date' => $one_hour_later, 'offset' => 0, 'max_results' => 10, 'deleted' => 0, 'user_id' => $current_user->id, 'select_fields'=> $callsAndMeetingsFields, 'ids'=>array(), 'relationship_name' => 'meetings_users', 'deletion_date' => ''));
        $GLOBALS['log']->fatal(var_export($result, true));
        $this->assertNotEmpty($result[2]['item']);
        $this->assertTrue(count($result[2]['item']) == 2);

        //Now say we wanted the other user's meetings
        /*
        $ids = array($this->another_user->id, $current_user->id);
        $ids[] = $this->meeting1;
        $ids[] = $this->meeting2;
        $ids[] = $this->meeting3;
        *
       	$result = $this->_soapClient->call('get_modified_relationships', array('session' => $this->_sessionId, 'module_name' => 'Users', 'related_module' => 'Meetings', 'from_date' => $one_hour_ago, 'to_date' => $one_hour_later, 'offset' => 0, 'max_results' => 10, 'deleted' => 0, 'module_user_id' => $this->another_user->id, 'select_fields'=> $callsAndMeetingsSelectFields, 'ids' => $ids, 'relationship_name' => 'meetings_users', 'deletion_date' => ''));
        $GLOBALS['log']->fatal(var_export($result, true));
        $this->assertNotEmpty($result[2]['item']);
        $this->assertTrue(count($result[2]['item']) == 2);
        */

        //$ids = array($this->contact2);
        //$current_user = $this->another_user;
        //$this->_login();
        $result = $this->_soapClient->call('get_modified_relationships', array('session' => $this->_sessionId, 'module_name' => 'Users', 'related_module' => 'Contacts', 'from_date' => $one_hour_ago, 'to_date' => $one_hour_later, 'offset' => 0, 'max_results' => 10, 'deleted' => 0, 'user_id' => $current_user->id, 'select_fields'=> $contactsSelectFields, 'ids'=>array($this->another_user->id), 'relationship_name' => 'contacts_users', 'deletion_date' => ''));
        $GLOBALS['log']->fatal(var_export($result, true));

    }


    /**
     * testGetMeetingWithGetEntryList
     *
     * Test the equivalent using get_entry_list and get_relationships
     */
    /*
    public function testGetMeetingWithGetEntryList()
    {
        global $timedate;
        $one_hour_ago = $timedate->asDb($timedate->getNow()->get("-1 hours"));
        $one_hour_later = $timedate->asDb($timedate->getNow()->get("+1 hours"));
   		$result = $this->_soapClient->call('get_entry_list',
           array(
               'session' => $this->_sessionId,
               'module_name' => 'Meetings',
               'query' => "(meetings.date_modified >= '{$one_hour_ago}' AND meetings.date_modified <= '{$one_hour_later}')",
               'order_by' => '',
               'offset' => 0,
               'select_fields' => array('name', 'id'),
               'link_name_to_fields_array' =>  array(array('name' =>'users', 'value' => array('id'))),
               'max_results' => 20,
               'deleted' => 0,
               'favorites' => false,
           )
        );

        //Now you'd have to loop through Meetings to find user relationship
        foreach($result as $entry)
        {
            $relationships = $this->_soapClient->call('get_relationships', array(
                                'session' => $this->_sessionId,
                                'module_name' => 'Users',
                                'module_id' => self::$_user->id,
                                'link_field_name' => 'meetings',
                                'related_module_query' => '',
                                'related_fields' => array('id', 'name'),
                                'related_module_link_name_to_fields_array' => '',
                                'deleted' => 0,
                                'order_by' => 'date_entered',
                                'offset' => 0,
                                'limit' => false)
            );
        }
    }
    */

}
