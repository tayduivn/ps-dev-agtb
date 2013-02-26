<?php

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once ('modules/Meetings/MeetingFormBase.php');

/**
 * Bug #57299
 *
 * Calendar  |  Meetings with status held are not displaying in the Calendar
 * @ticket 57299
 * @author imatsiushyna@sugarcrm.com
 */

class Bug57299Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    /**
     * @var FormBase
     */
    protected $formBase = null;

    /**
     * @var Bean
     */
    protected $bean = null;

    /**
     * @var module name
     */
    protected $name = 'Meetings';

    /**
     * @var User
     */
    protected $user = null;

    public function setUp()
    {
        unset($GLOBALS['disable_date_format']);
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('mod_strings', array($this->name));
        SugarTestHelper::setUp('timedate');

        $this->user = $GLOBALS['current_user'];

        $this->user->setPreference('datef', 'm/d/Y');
        $this->user->setPreference('timef', 'h:ia');
        $this->user->setPreference('timezone', 'UTC');

    }

    public function tearDown()
    {
        $_POST = array();

        $this->bean->db->query("DELETE FROM meetings WHERE id = '". $this->bean->id ."'");
        $this->bean->db->query("DELETE FROM {$this->bean->rel_users_table} WHERE meeting_id = '". $this->bean->id ."'");

        parent::tearDown();
        SugarTestHelper::tearDown();
    }

    public function getPostData()
    {
        return array(
            'module' => 'Calendar',
            'name' => 'Bug57299_'.time(),
            'current_module' => $this->name,
            'record' => '',
            'user_invitees' => '1',
            'contact_invitees' => '',
            'lead_invitees' => '',
            'send_invites' => '',
            'edit_all_recurrences' => true,
            'repeat_parent_id' => '',
            'repeat_type' => '',
            'repeat_interval' => '',
            'repeat_count' => '',
            'repeat_until' => '',
            'repeat_dow' => '',
            'appttype' => $this->name,
            'type' => 'Sugar',
            'date_start' => '11/25/2012 12:00pm',
            'parent_type' => 'Accounts',
            'parent_name' => '',
            'parent_id' => '',
            'date_end' => '11/25/2012 12:15pm',
            'location' => '',
            'duration' => 900,
            'duration_hours' => 0,
            'duration_minutes' => 15,
            'reminder_checked' => 1,
            'reminder_time' => 1800,
            'email_reminder_checked' => 0,
            'email_reminder_time' => 60,
            'assigned_user_name' => 'Administrator',
            'assigned_user_id' => 1,
            'update_fields_team_name_collection' => '',
            'team_name_new_on_update' => false,
            'team_name_allow_update' => '',
            'team_name_allow_new' => true,
            'team_name' => 'team_name',
            'team_name_field' => 'team_name_table',
            'arrow_team_name' => 'hide',
            'team_name_collection_0' => 'Global',
            'id_team_name_collection_0' => 1,
            'primary_team_name_collection' => 0,
            'description' => '',
        );
    }

    /**
     * providerData
     *
     * @return Array values for testing
     */
    public function providerData()
    {
        return array(
            array('Held', true),
            array('Held', false),
        );
    }

    /**
     * @group 57299
     * Test that new Meeting created from module Calendar save in database correctly
     *
     * @dataProvider providerData
     * @return void
     */
    public function testDisplaysMeetingWithStatusHeldInCalendar($status, $return_module)
    {
        $_POST = $this->getPostData();
        $_POST['status'] = $status;
        $_POST['return_module'] = ($return_module) ? 'Calendar' : '';
        $_REQUEST = $_POST;

        $this->formBase = new MeetingFormBase();
        $this->bean = $this->formBase->handleSave('', false, false);

        $sql = "SELECT * FROM {$this->bean->rel_users_table} WHERE meeting_id = '". $this->bean->id . "'";
        $result = $this->bean->db->query($sql);
        $rows = $this->bean->db->fetchByAssoc($result);

        //assert that if we return name of Calendar module
        //create relation between created Meeting and current User
        if($return_module)
        {
            $this->assertNotNull($rows);
        }
        else
        {
            $this->assertFalse($rows);
        }
    }
}
