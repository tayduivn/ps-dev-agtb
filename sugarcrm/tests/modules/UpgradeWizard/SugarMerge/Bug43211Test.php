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
require_once 'include/dir_inc.php';

class Bug43211Test extends Sugar_PHPUnit_Framework_TestCase  {
	
var $merge;

function setUp() {
   SugarTestMergeUtilities::setupFiles(array('Leads', 'Accounts'), array('searchdefs'), 'tests/modules/UpgradeWizard/SugarMerge/metadata_files');
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();
}

function test_leads_searchdefs_merge() {	
   require_once 'modules/UpgradeWizard/SugarMerge/SearchMerge.php';		
   $this->merge = new SearchMerge();
   $this->merge->merge('Leads', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/600/modules/Leads/metadata/searchdefs.php', 'modules/Leads/metadata/searchdefs.php', 'custom/modules/Leads/metadata/searchdefs.php');
   $this->assertTrue(file_exists('custom/modules/Leads/metadata/searchdefs.php.suback.php'));
   require('custom/modules/Leads/metadata/searchdefs.php');
   
   //Here's the main test... check to see that maxColumns has been changed to 3
   $this->assertEquals($searchdefs['Leads']['templateMeta']['maxColumns'], '3', 'Assert that maxColumns remains set to 3 for Leads module'); 
   $fields = array();
   foreach($searchdefs['Leads']['layout']['basic_search'] as $col_key=>$col) {
      	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
        if(!empty($id) && !is_array($id)) {
   	  	   $fields[$id] = $col;
   	  	}
   }
  
   $this->assertTrue(count($fields) == 3, "Assert that there are 3 fields in the basic_search layout for Leads metadata");
   $this->assertTrue(isset($fields['search_name']), "Assert that search_name field is present");
   $this->assertTrue(isset($fields['team_name']), "Assert that team_name field is present");
   $this->assertTrue(isset($fields['current_user_only']), "Assert that current_user_only field is present");
   $this->assertFalse(isset($fields['open_only']), "Assert that 620 OOTB open_only field is not added since there was a customization");
   
   $this->assertEquals($searchdefs['Leads']['templateMeta']['maxColumnsBasic'], $searchdefs['Leads']['templateMeta']['maxColumns'], 'Assert that maxColumnsBasic is set to value of maxColumns');   
}


function test_accounts_searchdefs_merge() {	
   require_once 'modules/UpgradeWizard/SugarMerge/SearchMerge.php';		
   $this->merge = new SearchMerge();
   $this->merge->merge('Accounts', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/600/modules/Accounts/metadata/searchdefs.php', 'modules/Accounts/metadata/searchdefs.php', 'custom/modules/Accounts/metadata/searchdefs.php');
   $this->assertTrue(file_exists('custom/modules/Accounts/metadata/searchdefs.php.suback.php'));
   require('custom/modules/Accounts/metadata/searchdefs.php');
   //echo var_export($searchdefs['Accounts'], true);
   
   //Here's the main test... check to see that maxColumns is still 3 since Accounts is not a module with maxColumn altered OOTB
   $this->assertEquals($searchdefs['Accounts']['templateMeta']['maxColumns'], '3', 'Assert that maxColumns is still 3 for Accounts module'); 
   $fields = array();
   foreach($searchdefs['Accounts']['layout']['basic_search'] as $col_key=>$col) {
      	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
        if(!empty($id) && !is_array($id)) {
   	  	   $fields[$id] = $col;
   	  	}
   }
  
   $this->assertTrue(count($fields) == 3, "Assert that there are 3 fields in the basic_search layout for Leads metadata");
   $this->assertTrue(isset($fields['name']), "Assert that name field is present");
   $this->assertTrue(isset($fields['created_by_name']), "Assert that created_by_name field is present");
   $this->assertTrue(isset($fields['current_user_only']), "Assert that current_user_only field is present");
   $this->assertFalse(isset($fields['open_only']), "Assert that 620 OOTB open_only field is not added since there was a customization");
   
   $this->assertEquals($searchdefs['Accounts']['templateMeta']['maxColumnsBasic'], $searchdefs['Accounts']['templateMeta']['maxColumns'], 'Assert that maxColumnsBasic is set to value of maxColumns');
}

}
?>