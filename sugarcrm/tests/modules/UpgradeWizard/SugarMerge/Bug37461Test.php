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

class Bug37461Test extends Sugar_PHPUnit_Framework_TestCase  {

var $merge;
var $has_dir;
var $modules;

function setUp() {
   $this->modules = array('Accounts');
   $this->has_dir = array();
   
   foreach($this->modules as $module) {
	   if(!file_exists("custom/modules/{$module}/metadata")){
		  mkdir_recursive("custom/modules/{$module}/metadata", true);
	   }
	   
	   if(file_exists("custom/modules/{$module}")) {
	   	  $this->has_dir[$module] = true;
	   }
	   
	   $files = array('searchdefs', 'listviewdefs');
	   foreach($files as $file) {
	   	   if(file_exists("custom/modules/{$module}/metadata/{$file}")) {
		   	  copy("custom/modules/{$module}/metadata/{$file}.php", "custom/modules/{$module}/metadata/{$file}.php.bak");
		   }
		   
		   if(file_exists("custom/modules/{$module}/metadata/{$file}.php.suback.php")) {
		      copy("custom/modules/{$module}/metadata/{$file}.php.suback.php", "custom/modules/{$module}/metadata/{$file}.php.suback.bak");
		   }
		   
		   if(file_exists("tests/modules/UpgradeWizard/SugarMerge/siupgrade_metadata_files/custom/modules/{$module}/metadata/{$file}.php")) {
		   	  copy("tests/modules/UpgradeWizard/SugarMerge/siupgrade_metadata_files/custom/modules/{$module}/metadata/{$file}.php", "custom/modules/{$module}/metadata/{$file}.php");
		   }
	   } //foreach
   } //foreach
}


function tearDown() {

   foreach($this->modules as $module) {
	   if(!$this->has_dir[$module]) {
	   	  rmdir_recursive("custom/modules/{$module}");
	   }  else {
	   	   $files = array('searchdefs', 'listviewdefs');
		   foreach($files as $file) {
		      if(file_exists("custom/modules/{$module}/metadata/{$file}.php.bak")) {
		      	 copy("custom/modules/{$module}/metadata/{$file}.php.bak", "custom/modules/{$module}/metadata/{$file}.php");
	             unlink("custom/modules/{$module}/metadata/{$file}.php.bak");
		      } else if(file_exists("custom/modules/{$module}/metadata/{$file}.php")) {
		      	 unlink("custom/modules/{$module}/metadata/{$file}.php");
		      }
		      
		   	  if(file_exists("custom/modules/{$module}/metadata/{$module}.php.suback.bak")) {
		      	 copy("custom/modules/{$module}/metadata/{$file}.php.suback.bak", "custom/modules/{$module}/metadata/{$file}.php.suback.php");
	             unlink("custom/modules/{$module}/metadata/{$file}.php.suback.bak");
		      } else if(file_exists("custom/modules/{$module}/metadata/{$file}.php.suback.php")) {
		      	 unlink("custom/modules/{$module}/metadata/{$file}.php.suback.php");
		      }  
		   }
	   }
   } //foreach
}


function test_accounts_searchdefs_merge() {	
   require_once 'modules/UpgradeWizard/SugarMerge/SearchMerge.php';		
   $this->merge = new SearchMerge();	
   $this->merge->merge('Accounts', 'tests/modules/UpgradeWizard/SugarMerge/siupgrade_metadata_files/551/modules/Accounts/metadata/searchdefs.php', 'modules/Accounts/metadata/searchdefs.php', 'custom/modules/Accounts/metadata/searchdefs.php');
   $this->assertTrue(file_exists('custom/modules/Accounts/metadata/searchdefs.php.suback.php'));
   require('custom/modules/Accounts/metadata/searchdefs.php');
   $fields = array();
   
   //echo var_export($searchdefs, true);
   
   foreach($searchdefs['Accounts']['layout']['basic_search'] as $col_key=>$col) {
      	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
      	if(!empty($id) && !is_array($id)) {
   	  	   $fields[$id] = $col;
   	  	}
   }
   
   
   $this->assertTrue(count($fields) == 6, "Assert that there are 6 fields in the basic_search layout for Accounts metadata");
   
   $fields = array();
   foreach($searchdefs['Accounts']['layout']['advanced_search'] as $col_key=>$col) {
      	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
      	if(!empty($id)) {
      		$fields[$id] = $col;
      	}
   }
   $this->assertTrue(count($fields) == 18, "Assert that there are 18 fields in the advanced_search layout for Accounts metadata");
}


function test_accounts_listviewdefs_merge() {	
   require('custom/modules/Accounts/metadata/listviewdefs.php');
   $original_fields = array();
   $original_displayed_fields = array();
   foreach($listViewDefs['Accounts'] as $col_key=>$col) {
   	  	$original_fields[$col_key] = $col;
   	  	if(isset($col['default']) && $col['default']) {
   	  	   $original_displayed_fields[$col_key] = $col;
   	  	}
   }
	
   //echo var_export($original_displayed_fields, true);
   
   require_once 'modules/UpgradeWizard/SugarMerge/ListViewMerge.php';		
   $this->merge = new ListViewMerge();	
   $this->merge->merge('Accounts', 'tests/modules/UpgradeWizard/SugarMerge/siupgrade_metadata_files/551/modules/Accounts/metadata/listviewdefs.php', 'modules/Accounts/metadata/listviewdefs.php', 'custom/modules/Accounts/metadata/listviewdefs.php');
   $this->assertTrue(file_exists('custom/modules/Accounts/metadata/listviewdefs.php.suback.php'));
   require('custom/modules/Accounts/metadata/listviewdefs.php');
   $fields = array();
   $displayed_fields = array();
   foreach($listViewDefs['Accounts'] as $col_key=>$col) {
   	  	$fields[$col_key] = $col;
   	  	if(isset($col['default']) && $col['default']) {
   	  	   $displayed_fields[$col_key] = $col;
   	  	}
   }
  
   //echo var_export($displayed_fields, true);
   //echo var_export($listViewDefs['Accounts'], true);
   
   $this->assertTrue(count($displayed_fields) == count($original_displayed_fields), "Assert that there are the same number of fields displayed in the listview layout for Accounts metadata");
   $this->assertTrue(isset($displayed_fields['NAME']), "Assert that NAME field is present");
   $this->assertTrue(isset($displayed_fields['BILLING_ADDRESS_CITY']), "Assert that BILLING_ADDRESS_CITY field is present");
   $this->assertTrue(isset($displayed_fields['BILLING_ADDRESS_STATE']), "Assert that BILLING_ADDRESS_CITY field is present");
   $this->assertTrue(isset($displayed_fields['TEAM_NAME']), "Assert that TEAM_NAME (removed in 6.0 OOTB) field is present");
   $this->assertTrue(!isset($displayed_fields['BILLING_ADDRESS_COUNTRY']), "Assert that BILLING_ADDRESS_COUNTRY (added in 6.0 OOTB) field is not present");
}


}
?>