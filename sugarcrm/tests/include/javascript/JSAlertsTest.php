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
 
require_once 'include/javascript/jsAlerts.php';

class JSAlertsTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $beans;

    public function setUp()
    {
        global $current_user;
        $this->beans = array();
        $this->old_user = $current_user;
        $current_user = $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }
    
    public function tearDown()
    {
        foreach($this->beans as $bean) {
            $bean->mark_deleted($bean->id);
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

		unset($GLOBALS['app_list_strings']);
		unset($GLOBALS['current_user']);
		unset($GLOBALS['app_strings']);
    }

    protected function createNewMeeting()
    {
        $m = new Meeting();
        $m->name = "40541TestMeeting";
        $m->date_start = gmdate($GLOBALS['timedate']->get_db_date_time_format(), time() + 3000);
        $m->duration_hours = 0;
        $m->duration_minutes = 15;
        $m->reminder_time = 60;
        $m->reminder_checked = true;
        $m->save();
        $m->load_relationship("users");
        $m->users->add($this->_user->id);
        $this->beans[] = $m;
        return $m;
    }

    public function testGetAlertsForUser()
    {

        global $app_list_strings;
            $app_list_strings['reminder_max_time'] = 5000;
        $m = $this->createNewMeeting();
        $alerts = new jsAlerts();
        $script = $alerts->getScript();
        $this->assertRegExp("/addAlert.*\"{$m->name}\"/", $script);
    }

    public function testGetDeclinedAlertsForUser()
    {

        global $app_list_strings;
            $app_list_strings['reminder_max_time'] = 5000;
        $m = $this->createNewMeeting();
        //Decline the meeting
        $query = "UPDATE meetings_users SET deleted = 0, accept_status = 'decline' " .
    			 "WHERE meeting_id = '$m->id' AND user_id = '{$this->_user->id}'";
    	$m->db->query($query);
        $alerts = new jsAlerts();
        $script = $alerts->getScript();
        $this->assertNotRegExp("/addAlert.*\"{$m->name}\"/", $script);
    }
}
