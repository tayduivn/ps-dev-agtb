<?php
//FILE SUGARCRM flav=pro ONLY
class TrackerReportsAccessTest extends Sugar_PHPUnit_Framework_OutputTestCase {

	var $non_admin_user;
    var $current_user;

    function setUp()
    {
        global $sugar_version, $sugar_flavor, $beanFiles, $beanList, $moduleList, $modListHeader, $sugar_config;
        require('config.php');
        require('include/modules.php');
        $modListHeader = $moduleList;
        require_once('modules/Reports/config.php');
        $GLOBALS['report_modules'] = getAllowedReportModules($modListHeader);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $this->current_user = $GLOBALS['current_user'];
        $this->non_admin_user = SugarTestUserUtilities::createAnonymousUser();

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }

    function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->non_admin_user = null;
        $GLOBALS['current_user'] = $this->current_user;
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
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
