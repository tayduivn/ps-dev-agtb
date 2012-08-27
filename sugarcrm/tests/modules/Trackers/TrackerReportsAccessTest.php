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
 
class TrackerReportsAccessTest extends Sugar_PHPUnit_Framework_OutputTestCase {

	var $non_admin_user;
    var $current_user;

    function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        global $sugar_version, $sugar_flavor, $beanFiles, $beanList, $moduleList, $modListHeader, $sugar_config;
        require('config.php');
        require('include/modules.php');
        $modListHeader = $moduleList;
        require_once('modules/Reports/config.php');
        $GLOBALS['report_modules'] = getAllowedReportModules($modListHeader);
        $this->current_user = $GLOBALS['current_user'];
        $this->non_admin_user = SugarTestUserUtilities::createAnonymousUser();
    }

    function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->non_admin_user = null;
        $GLOBALS['current_user'] = $this->current_user;
        unset($GLOBALS['mod_strings']);
        SugarTestHelper::tearDown();
    }

    /**
     * Test whereby an Admin user attempts to access the TrackerSessions reports
     * @outputBuffering enabled
     */
    public function test_Admin_Tracker_Session_Report_access ()
    {
    	$admin = new User();
    	$admin->retrieve('1');
    	$GLOBALS['current_user'] = $admin;
    	global $theme, $mod_strings;
    	$theme = 'Sugar';
    	$mod_strings = return_module_language($GLOBALS['current_language'], 'Reports');
    	$GLOBALS['_REQUEST']['action'] = 'ReportCriteriaResults';
    	$GLOBALS['_REQUEST']['module'] = 'Reports';
    	$GLOBALS['request_string'] = '';

    	$saved_report_seed = new SavedReport();
	    $saved_report_seed->disable_row_level_security = true;
	    $query = "SELECT id FROM saved_reports WHERE module = 'TrackerSessions' AND deleted=0";
	    $results = $GLOBALS['db']->query($query);

    	while($row = $GLOBALS['db']->fetchByAssoc($results)) {
        	    $id = $row['id'];
                $_REQUEST['id'] = $id;
		    	require('modules/Reports/ReportCriteriaResults.php');
		    	$this->assertTrue(checkSavedReportACL($args['reporter'], $args));
		}
    }

    /**
     * Test whereby a non-admin user is given the Tracker Role and attempts to access both of the TrackerSessions reports
     *
     */
    /*
    public function test_NonAdmin_Tracker_Session_Report_access () {

    	$GLOBALS['current_user'] = $this->non_admin_user;
    	$queryTrackerRole = "SELECT id FROM acl_roles where name='Tracker'";
		$result = $GLOBALS['db']->query($queryTrackerRole);
		$trackerRoleId = $GLOBALS['db']->fetchByAssoc($result);
		if(!empty($trackerRoleId['id'])) {
		   require_once('modules/ACLRoles/ACLRole.php');
		   $role1= new ACLRole();
		   $role1->retrieve($trackerRoleId['id']);
		   $role1->set_relationship('acl_roles_users', array('role_id'=>$role1->id ,'user_id'=>$this->non_admin_user->id), false);

		   global $theme, $mod_strings;
	       $theme = 'Sugar';
	       $mod_strings = return_module_language($GLOBALS['current_language'], 'Reports');
	       $GLOBALS['_REQUEST']['action'] = 'ReportCriteriaResults';
	       $GLOBALS['request_string'] = '';

	       $saved_report_seed = new SavedReport();
		   $saved_report_seed->disable_row_level_security = true;
		   $query = "SELECT id FROM saved_reports WHERE module = 'TrackerSessions'";
		   $results = $GLOBALS['db']->query($query);
	       while($row = $GLOBALS['db']->fetchByAssoc($results)) {
			      $id = $row['id'];
	              $GLOBALS['_REQUEST']['id'] = $id;
			      include('modules/Reports/ReportCriteriaResults.php');
			      $this->assertTrue(checkSavedReportACL($args['reporter'],$args));
	       }
		}
    }
    */

}



?>
