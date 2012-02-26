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
 
require_once('data/SugarBean.php');

/**
 * Bug49385Test.php
 *
 * This test handles verifying that the SQL string or array returned from the create_new_list_query call can properly
 * processing the alter_many_to_many_query flag in SugarBean.
 *
 * @author Collin Lee
 *
 */
class Bug49385Test extends Sugar_PHPUnit_Framework_OutputTestCase
{

    static $call_id = null;
    static $meeting_id = null;
    static $contact_id = null;

    static public function setUpBeforeClass()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();

        global $beanList, $beanFiles;
        require('include/modules.php');

        $contact1 = SugarTestContactUtilities::createContact();
        Bug49385Test::$contact_id = $contact1->id;

        $contact2 = SugarTestContactUtilities::createContact();
        $contact3 = SugarTestContactUtilities::createContact();

        $meeting = SugarTestMeetingUtilities::createMeeting();
        Bug49385Test::$meeting_id = $meeting->id;
        $meeting->name = 'Bug49385Test';
        $meeting->save();

        $data_values = array('accept_status'=>'none');

        $relate_values = array('contact_id'=>$contact1->id,'meeting_id'=>$meeting->id);
     	$meeting->set_relationship($meeting->rel_contacts_table, $relate_values, true, true, $data_values);

        $relate_values = array('contact_id'=>$contact2->id,'meeting_id'=>$meeting->id);
     	$meeting->set_relationship($meeting->rel_contacts_table, $relate_values, true, true, $data_values);

        $relate_values = array('contact_id'=>$contact3->id,'meeting_id'=>$meeting->id);
     	$meeting->set_relationship($meeting->rel_contacts_table, $relate_values, true, true, $data_values);

        $call = SugarTestCallUtilities::createCall();
        Bug49385Test::$call_id = $call->id;
        $call->name = 'Bug49385Test';
        $call->save();

        $relate_values = array('contact_id'=>$contact1->id,'call_id'=>$call->id);
     	$call->set_relationship($call->rel_contacts_table, $relate_values, true, true, $data_values);

        $relate_values = array('contact_id'=>$contact2->id,'call_id'=>$call->id);
     	$call->set_relationship($call->rel_contacts_table, $relate_values, true, true, $data_values);

        $relate_values = array('contact_id'=>$contact3->id,'call_id'=>$call->id);
     	$call->set_relationship($call->rel_contacts_table, $relate_values, true, true, $data_values);

    }

    static public function tearDownAfterClass()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestCallUtilities::removeAllCreatedCalls();
        $GLOBALS['db']->query("DELETE FROM meetings_contacts WHERE meeting_id = '" . Bug49385Test::$meeting_id . "'");
        $GLOBALS['db']->query("DELETE FROM calls_contacts WHERE call_id = '" . Bug49385Test::$call_id . "'");
        unset($GLOBALS['current_user']);
    }

    /**
     * providerTestAlterManyToManyQuery
     *
     * This provider method returns a bunch of simulated arguments that mimics the arguments that would be created in
     * the create_new_list_query method.
     *
     */
    public function providerTestAlterManyToManyQuery()
    {
        return array
        (

            array(
                 array(
                    'select' =>  "SELECT  meetings.id , meetings.status , meetings.join_url , meetings.host_url , meetings.name  , LTRIM(RTRIM(CONCAT(IFNULL(contacts.first_name,''),' ',IFNULL(contacts.last_name,'')))) contact_name, jtl0.contact_id contact_id, meetings.parent_id , meetings.parent_type , meetings.date_start  , LTRIM(RTRIM(CONCAT(IFNULL(jt1.first_name,''),' ',IFNULL(jt1.last_name,'')))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, meetings.date_entered , meetings.assigned_user_id  , sfav.id is_favorite ",
                    'from' =>  "FROM meetings   LEFT JOIN  meetings_contacts jtl0 ON meetings.id=jtl0.meeting_id AND jtl0.deleted=0

                          LEFT JOIN  contacts contacts ON contacts.id=jtl0.contact_id AND contacts.deleted=0
                          AND contacts.deleted=0  LEFT JOIN  users jt1 ON meetings.assigned_user_id=jt1.id AND jt1.deleted=0

                          AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Meetings' AND sfav.record_id=meetings.id AND sfav.created_by='1' AND sfav.deleted=0 ",
                     'from_min' =>  "FROM meetings ",
                     'where' =>  "where ((meetings.name like 'Bug49385Test%')) AND meetings.deleted=0 ",
                     'order_by' =>  "ORDER BY meetings.name ASC ",
                 ),
                 'Meetings',
                 true
            ),

            array(
                 array(
                    'select' =>  "SELECT  meetings.*,  LTRIM(RTRIM(CONCAT(IFNULL(contacts.first_name,''),' ',IFNULL(contacts.last_name,'')))) contact_name, jtl0.contact_id contact_id, meetings.parent_id , meetings.parent_type , meetings.date_start  , LTRIM(RTRIM(CONCAT(IFNULL(jt1.first_name,''),' ',IFNULL(jt1.last_name,'')))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, meetings.date_entered , meetings.assigned_user_id  , sfav.id is_favorite ",
                    'from' =>  "FROM meetings   LEFT JOIN  meetings_contacts jtl0 ON meetings.id=jtl0.meeting_id AND jtl0.deleted=0

                          LEFT JOIN  contacts contacts ON contacts.id=jtl0.contact_id AND contacts.deleted=0
                          AND contacts.deleted=0  LEFT JOIN  users jt1 ON meetings.assigned_user_id=jt1.id AND jt1.deleted=0

                          AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Meetings' AND sfav.record_id=meetings.id AND sfav.created_by='1' AND sfav.deleted=0 ",
                     'from_min' =>  "FROM meetings ",
                     'where' =>  "where ((meetings.name like 'Bug49385Test%')) AND meetings.deleted=0 ",
                     'order_by' =>  "ORDER BY meetings.name ASC ",
                 ),
                 'Meetings',
                 true
            ),


            array(
                 array(
                    'select' =>  " SELECT  meetings.name , meetings.status, LTRIM(RTRIM(CONCAT(IFNULL(contacts.first_name,''),' ',IFNULL(contacts.last_name,'')))) contact_name, jtl0.contact_id contact_id, meetings.parent_id , meetings.parent_type , meetings.date_start  , LTRIM(RTRIM(CONCAT(IFNULL(jt1.first_name,''),' ',IFNULL(jt1.last_name,'')))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, meetings.date_entered , meetings.assigned_user_id  , sfav.id is_favorite ",
                    'from' =>  "FROM meetings   LEFT JOIN  meetings_contacts jtl0 ON meetings.id=jtl0.meeting_id AND jtl0.deleted=0

                          LEFT JOIN  contacts contacts ON contacts.id=jtl0.contact_id AND contacts.deleted=0
                          AND contacts.deleted=0  LEFT JOIN  users jt1 ON meetings.assigned_user_id=jt1.id AND jt1.deleted=0

                          AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Meetings' AND sfav.record_id=meetings.id AND sfav.created_by='1' AND sfav.deleted=0 ",
                     'from_min' =>  "FROM meetings ",
                     'where' =>  "where ((meetings.name like 'Bug49385Test%')) AND meetings.deleted=0 ",
                     'order_by' =>  "ORDER BY meetings.name ASC ",
                 ),
                 'Meetings',
                 true
            ),


            array(
                 array(
                    'select' =>  "       SELECT       meetings.name,meetings.status, LTRIM(RTRIM(CONCAT(IFNULL(contacts.first_name,''),' ',IFNULL(contacts.last_name,'')))) contact_name, jtl0.contact_id contact_id, meetings.parent_id , meetings.parent_type , meetings.date_start  , LTRIM(RTRIM(CONCAT(IFNULL(jt1.first_name,''),' ',IFNULL(jt1.last_name,'')))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, meetings.date_entered , meetings.assigned_user_id  , sfav.id is_favorite ",
                    'from' =>  "FROM meetings   LEFT JOIN  meetings_contacts jtl0 ON meetings.id=jtl0.meeting_id AND jtl0.deleted=0

                          LEFT JOIN  contacts contacts ON contacts.id=jtl0.contact_id AND contacts.deleted=0
                          AND contacts.deleted=0  LEFT JOIN  users jt1 ON meetings.assigned_user_id=jt1.id AND jt1.deleted=0

                          AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Meetings' AND sfav.record_id=meetings.id AND sfav.created_by='1' AND sfav.deleted=0 ",
                     'from_min' =>  "FROM meetings ",
                     'where' =>  "where ((meetings.name like 'Bug49385Test%')) AND meetings.deleted=0 ",
                     'order_by' =>  "ORDER BY meetings.name ASC ",
                 ),
                 'Meetings',
                 true
            ),

            array(
                 array(
                    'select' =>  "SELECT count(meetings.name) AS foo,  meetings.status, LTRIM(RTRIM(CONCAT(IFNULL(contacts.first_name,''),' ',IFNULL(contacts.last_name,'')))) contact_name, jtl0.contact_id contact_id, meetings.parent_id , meetings.parent_type , meetings.date_start  , LTRIM(RTRIM(CONCAT(IFNULL(jt1.first_name,''),' ',IFNULL(jt1.last_name,'')))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, meetings.date_entered , meetings.assigned_user_id  , sfav.id is_favorite ",
                    'from' =>  "FROM meetings   LEFT JOIN  meetings_contacts jtl0 ON meetings.id=jtl0.meeting_id AND jtl0.deleted=0

                          LEFT JOIN  contacts contacts ON contacts.id=jtl0.contact_id AND contacts.deleted=0
                          AND contacts.deleted=0  LEFT JOIN  users jt1 ON meetings.assigned_user_id=jt1.id AND jt1.deleted=0

                          AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Meetings' AND sfav.record_id=meetings.id AND sfav.created_by='1' AND sfav.deleted=0 ",
                     'from_min' =>  "FROM meetings ",
                     'where' =>  "where ((meetings.name like 'Bug49385Test%')) AND meetings.deleted=0 ",
                     'order_by' =>  "ORDER BY meetings.name ASC ",
                 ),
                 'Meetings',
                 true
            ),

            array(
                array(
                    'select' =>  "SELECT  calls.id , calls.status , calls.direction , calls.name  , LTRIM(RTRIM(CONCAT(IFNULL(contacts.first_name,''),' ',IFNULL(contacts.last_name,'')))) contact_name, jtl0.contact_id contact_id, calls.parent_id , calls.parent_type , calls.date_start  , LTRIM(RTRIM(CONCAT(IFNULL(jt1.first_name,''),' ',IFNULL(jt1.last_name,'')))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, calls.date_entered , calls.assigned_user_id  , sfav.id is_favorite ",
                    'from' =>  "FROM calls   LEFT JOIN  calls_contacts jtl0 ON calls.id=jtl0.call_id AND jtl0.deleted=0
                    LEFT JOIN  contacts contacts ON contacts.id=jtl0.contact_id AND contacts.deleted=0
                    AND contacts.deleted=0  LEFT JOIN  users jt1 ON calls.assigned_user_id=jt1.id AND jt1.deleted=0
                    AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Calls' AND sfav.record_id=calls.id AND sfav.created_by='1' AND sfav.deleted=0  ",
                    'from_min' =>  "FROM calls ",
                    'where' =>  "where ((calls.name like 'Bug49385Test%')) AND calls.deleted=0 ",
                    'order_by' => "ORDER BY calls.date_entered DESC ",
                ),
                'Calls',
                true
            ),

            array(
                array(
                    'select' =>  "SELECT  calls.*, LTRIM(RTRIM(CONCAT(IFNULL(contacts.first_name,''),' ',IFNULL(contacts.last_name,'')))) contact_name, jtl0.contact_id contact_id, calls.parent_id , calls.parent_type , calls.date_start  , LTRIM(RTRIM(CONCAT(IFNULL(jt1.first_name,''),' ',IFNULL(jt1.last_name,'')))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, calls.date_entered , calls.assigned_user_id  , sfav.id is_favorite ",
                    'from' =>  "FROM calls   LEFT JOIN  calls_contacts jtl0 ON calls.id=jtl0.call_id AND jtl0.deleted=0
                    LEFT JOIN  contacts contacts ON contacts.id=jtl0.contact_id AND contacts.deleted=0
                    AND contacts.deleted=0  LEFT JOIN  users jt1 ON calls.assigned_user_id=jt1.id AND jt1.deleted=0
                    AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Calls' AND sfav.record_id=calls.id AND sfav.created_by='1' AND sfav.deleted=0  ",
                    'from_min' =>  "FROM calls ",
                    'where' =>  "where ((calls.name like 'Bug49385Test%%')) AND calls.deleted=0 ",
                    'order_by' => "ORDER BY calls.date_entered DESC ",
                ),
                'Calls',
                true
            ),

            array(
                array(
                    'select' =>  "SELECT  calls.name, calls.name, LTRIM(RTRIM(CONCAT(IFNULL(contacts.first_name,''),' ',IFNULL(contacts.last_name,'')))) contact_name, jtl0.contact_id contact_id, calls.parent_id , calls.parent_type , calls.date_start  , LTRIM(RTRIM(CONCAT(IFNULL(jt1.first_name,''),' ',IFNULL(jt1.last_name,'')))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, calls.date_entered , calls.assigned_user_id  , sfav.id is_favorite ",
                    'from' =>  "FROM calls   LEFT JOIN  calls_contacts jtl0 ON calls.id=jtl0.call_id AND jtl0.deleted=0
                    LEFT JOIN  contacts contacts ON contacts.id=jtl0.contact_id AND contacts.deleted=0
                    AND contacts.deleted=0  LEFT JOIN  users jt1 ON calls.assigned_user_id=jt1.id AND jt1.deleted=0
                    AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Calls' AND sfav.record_id=calls.id AND sfav.created_by='1' AND sfav.deleted=0  ",
                    'from_min' =>  "FROM calls ",
                    'where' =>  "where ((calls.name like 'Bug49385Test%')) AND calls.deleted=0 ",
                    'order_by' => "ORDER BY calls.date_entered DESC ",
                ),
                'Calls',
                true
            ),

            array(
                array(
                    'select' =>  "DELETE FROM calls WHERE id = 'foo'", //This really shouldn't happen, but we check anyway
                ),
                'Calls',
                false
            ),

            array(
                array(
                    'select' =>  "UPDATE calls set deleted = 0", //This really shouldn't happen, but we check anyway
                ),
                'Calls',
                false
            ),

        );
    }

    /**
     * testAlterManyToManyQuery
     *
     * This is the main test function of this class.  This test checks that the array output returned from the alter_many_to_many array
     * contains the modified SQL code; namely that a "SELECT DISTINCT {table_name}.id, count({$table_name}.id) AS total_m_to_m_count" clause
     * is present.  It also tests that a "GROUP BY {table_name}.id" clause is added.
     *
     * @dataProvider providerTestAlterManyToManyQuery
     * @param array $ret_array The simulated $ret_array input
     * @param string $module String value of the module for which bean is being tested
     * @param boolean $altered Boolean value indicating whether or not to expect the $ret_array contents to be altered
     */
    public function testAlterManyToManyQuery($ret_array, $module, $altered=true)
    {
        $mock = new Bug49385SugarBeanMock();
        $mod = strtolower($module);
        $mock->table_name = $mod;

        //The alter_many_to_many_array is the key method we are testing that handles modifying the SQL
        $result = $mock->alter_many_to_many_array($ret_array);

        $regex = "/^\s*?SELECT\s+?DISTINCT\s+?{$mod}\.id\,\s+?count\s*?\(\s*?{$mod}\.id\s*?\)\s+?AS\s+?total_m_to_m_count/";

        if($altered)
        {
            $this->assertRegExp($regex, $result['select'], 'Failed to create DISTINCT SQL: ' . $result['select']);
            $this->assertRegExp("/GROUP BY {$mod}.id/", $result['group_by'], 'Failed to create GROUP BY SQL: ' . $result['group_by']);

            $query = $result['select'] . $result['from'] . $result['where'] . (isset($result['group_by']) ? $result['group_by'] : '') . $result['order_by'];
            $result = $GLOBALS['db']->query($query);

            while(($row=$GLOBALS['db']->fetchByAssoc($result)) != null)
            {

                $this->assertEquals(3, $row['total_m_to_m_count'], "Failed to get the correct count from the count({$mod}.id) function");
            }


        } else {
            $this->assertNotRegExp($regex, $result['select'], 'Failed to not create DISTINCT SQL: ' . $result['select']);
        }

    }


    /**
     * testMeetingsController
     *
     * This tests the controller code added to make sure we get contacts displayed for the popup window
     */
    public function testMeetingsController()
    {
        require_once('modules/Meetings/controller.php');
        $controller = new MeetingsController();
        $_REQUEST['bean_id'] = Bug49385Test::$meeting_id;
        $_REQUEST['related_id'] = Bug49385Test::$contact_id;
        $controller->action_DisplayInline();
        $this->expectOutputRegex('/SugarContact/');
    }


    /**
     * testCallsController
     *
     * This tests the controller code to make sure we get contacts displayed for the popup window
     */
    public function testCallsController()
    {
        require_once('modules/Calls/controller.php');
        $controller = new CallsController();
        $_REQUEST['bean_id'] = Bug49385Test::$call_id;
        $_REQUEST['related_id'] = Bug49385Test::$contact_id;
        $controller->action_DisplayInline();
        $this->expectOutputRegex('/SugarContact/');
    }

}

class Bug49385SugarBeanMock extends SugarBean
{
    public function alter_many_to_many_array($ret_array=array())
    {
        return parent::alter_many_to_many_array($ret_array);
    }
}

?>