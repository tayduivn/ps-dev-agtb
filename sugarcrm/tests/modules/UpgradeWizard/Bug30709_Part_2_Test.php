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
class Bug30709_Part_2_Test extends  Sugar_PHPUnit_Framework_TestCase {

function setUp() {
    //Create the language files with bad name
    if(file_exists('custom/include/language/en_us.lang.php')) {
       copy('custom/include/language/en_us.lang.php', 'custom/include/language/en_us.lang.php.backup');
    }
	
    if( $fh = @fopen('custom/include/language/en_us.lang.php', 'w+') )
    {
$string = <<<EOQ
<?php
\$GLOBALS['app_list_strings'] = array (
  'test'=>array(
    'abc' => 'ABC',
    'cbs' => 'CBS',
    'nbc' => 'NBC',
  ),
  'lead_source_dom' =>
  array (
    '' => '',
    'Cold Call' => 'Cold Call',
    'Existing Customer' => 'Existing Customer',
    'Self Generated' => 'Self Generated',
    'Employee' => 'Employee',
    'Partner' => 'Partner',
    'Public Relations' => 'Public Relations',
    'Direct Mail' => 'Direct Mail',
    'Conference' => 'Conference',
    'Trade Show' => 'Trade Show',
    'Web Site' => 'Web Site',
    'Word of mouth' => 'Word of mouth',
    'Email' => 'Email',
    'Campaign'=>'Campaign',
    'Other' => 'Other',
  ),
  //BEGIN SUGARCRM flav=pro ONLY
  'opportunity_type_dom' =>
  array (
    '' => '',
    'Existing Business' => 'Existing Business',
    'New Business' => 'New Business',
  ),
  //END SUGARCRM flav=pro ONLY
  'moduleList' =>
  array (
    'Home' => 'Home',
    'Dashboard' => 'Dashboard',
    'Contacts' => 'Contacts',
    'Accounts' => 'Accounts Module',
    'Opportunities' => 'Opportunities',
    'Cases' => 'Cases',
    'Notes' => 'Notes',
    'Calls' => 'Calls',
    'Emails' => 'Emails',
    'Meetings' => 'Meetings',
    'Tasks' => 'Tasks',
    'Calendar' => 'Calendar',
    'Leads' => 'Leads',
    'Currencies' => 'Currencies',
    'Contracts' => 'Contracts',
    'Quotes' => 'Quotes',
    'Products' => 'Products',
    'ProductCategories' => 'Product Categories',
    'ProductTypes' => 'Product Types',
    'ProductTemplates' => 'Product Catalog',
    'Reports' => 'Reports',
    'Reports_1' => 'Reports',
    'Forecasts' => 'Forecasts',
    'ForecastSchedule' => 'Forecast Schedule',
    'MergeRecords' => 'Merge Records',
    'Quotas' => 'Quotas',
    'Teams' => 'Teams',
    'Activities' => 'Activities',
    'Bugs' => 'Bug Tracker',
    'Feeds' => 'RSS',
    'iFrames' => 'My Portal',
    'TimePeriods' => 'Time Periods',
    'Project' => 'Projects',
    'ProjectTask' => 'Project Tasks',
    'Campaigns' => 'Campaigns',
    'CampaignLog' => 'Campaign Log',
    'Documents' => 'Documents',
    'Sync' => 'Sync',
    'WorkFlow' => 'Work Flow',
    'Users' => 'Users',
    'Releases' => 'Releases',
    'Prospects' => 'Targets',
    'Queues' => 'Queues',
    'EmailMarketing' => 'Email Marketing',
    'EmailTemplates' => 'Email Templates',
    'ProspectLists' => 'Target Lists',
    'SavedSearch' => 'Saved Searches',
    'Trackers' => 'Trackers',
    'TrackerPerfs' => 'Tracker Performance',
    'TrackerSessions' => 'Tracker Sessions',
    'TrackerQueries' => 'Tracker Queries',
    'FAQ' => 'FAQ',
    'Newsletters' => 'Newsletters',
    'SugarFeed' => 'Sugar Feed',
    'Library' => 'Library',
    'EmailAddresses' => 'Email Address',
    'KBDocuments' => 'Knowledge Base',
    'my_personal_module' => 'My Personal Module',    
  ),
);

\$GLOBALS['app_strings']['LBL_TEST'] = 'This is a test';
EOQ;
       fputs( $fh, $string);
       fclose( $fh );
    }
}

function tearDown() {
    if(file_exists('custom/include/language/en_us.lang.php.backup')) {
       copy('custom/include/language/en_us.lang.php.backup', 'custom/include/language/en_us.lang.php');
       unlink('custom/include/language/en_us.lang.php.backup');  
    } else {
       unlink('custom/include/language/en_us.lang.php');
    }
    
    if(file_exists('custom/include/language/en_us.lang.php.bak')) {
       unlink('custom/include/language/en_us.lang.php.bak');
    }   

    $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
}

function test_dropdown_fixed() {	
    require_once('modules/UpgradeWizard/uw_utils.php');
    fix_dropdown_list();
        
    //Check to make sure we don't have the buggy format where '$GLOBALS["app_list_strings"] = array (...' was declared
    $contents = file_get_contents('custom/include/language/en_us.lang.php');
    //$this->assertFalse(preg_match('/\$GLOBALS\[\s*[\"|\']app_list_strings[\"|\']\s*\]\s+=\s+array\s+\(/', $contents));

    unset($GLOBALS['app_list_strings']);
    require('custom/include/language/en_us.lang.php');
    //BEGIN SUGARCRM flav=pro ONLY
    $this->assertEquals(3, count($GLOBALS['app_list_strings']));
    //END SUGARCRM flav=pro ONLY
    //BEGIN SUGARCRM flav=com ONLY
    $this->assertEquals(2, count($GLOBALS['app_list_strings']));
    //END SUGARCRM flav=com ONLY
    $this->assertTrue(isset($GLOBALS['app_list_strings']['moduleList']['my_personal_module']));
    $this->assertEquals($GLOBALS['app_list_strings']['moduleList']['Accounts'],'Accounts Module');
    $this->assertEquals(count($GLOBALS['app_strings']),1);
    $this->assertEquals($GLOBALS['app_strings']['LBL_TEST'],'This is a test');
}


}

?>