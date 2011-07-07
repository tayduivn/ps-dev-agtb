<?php
//FILE SUGARCRM flav!=sales ONLY 
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

class LeadsMergeTest extends Sugar_PHPUnit_Framework_TestCase  {

var $merge;
var $has_dir;
var $modules;

function setUp() {
   $this->modules = array('Leads');
   $this->has_dir = array();
   
   foreach($this->modules as $module) {
	   if(!file_exists("custom/modules/{$module}/metadata")){
		  mkdir_recursive("custom/modules/{$module}/metadata", true);
	   }
	   
	   if(file_exists("custom/modules/{$module}")) {
	   	  $this->has_dir[$module] = true;
	   }
	   
	   $files = array('detailviewdefs', 'editviewdefs');
	   foreach($files as $file) {
	   	   if(file_exists("custom/modules/{$module}/metadata/{$file}")) {
		   	  copy("custom/modules/{$module}/metadata/{$file}.php", "custom/modules/{$module}/metadata/{$file}.php.bak");
		   }
		   
		   if(file_exists("custom/modules/{$module}/metadata/{$file}.php.suback.php")) {
		      copy("custom/modules/{$module}/metadata/{$file}.php.suback.php", "custom/modules/{$module}/metadata/{$file}.php.suback.bak");
		   }
		   
		   if(file_exists("tests/modules/UpgradeWizard/SugarMerge/metadata_files/custom/modules/{$module}/metadata/{$file}.php")) {
		   	  copy("tests/modules/UpgradeWizard/SugarMerge/metadata_files/custom/modules/{$module}/metadata/{$file}.php", "custom/modules/{$module}/metadata/{$file}.php");
		   }
	   } //foreach
   } //foreach
}


function tearDown() {

   foreach($this->modules as $module) {
	   if(!$this->has_dir[$module]) {
	   	  rmdir_recursive("custom/modules/{$module}");
	   }  else {
	   	   $files = array('detailviewdefs', 'editviewdefs');
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

/*
function test_600_leads_detailview_merge() {		
   $this->assertTrue(file_exists('custom/modules/Leads/metadata/detailviewdefs.php'));	
   require('custom/modules/Leads/metadata/detailviewdefs.php');
   $pre_upgrade_fields = array();
   $pre_upgrade_panels = array();
   foreach($viewdefs['Leads']['DetailView']['panels'] as $panel_key=>$panel) {
   	  $pre_upgrade_panels[$panel_key] = $panel_key;
   	  foreach($panel as $row) {
   	  	 foreach($row as $col_key=>$col) {
   	  	 	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
   	  	 	if(!empty($id) && !is_array($id)) {
   	  	 	   $pre_upgrade_fields[$id] = $col;
   	  	 	}
   	  	 }
   	  }
   } 	
	
   require_once('modules/UpgradeWizard/SugarMerge/DetailViewMerge.php');
   $this->merge = new DetailViewMerge();	
   $this->merge->merge('Leads', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/551/modules/Leads/metadata/detailviewdefs.php', 'modules/Leads/metadata/detailviewdefs.php', 'custom/modules/Leads/metadata/detailviewdefs.php');
   $this->assertTrue(file_exists('custom/modules/Leads/metadata/detailviewdefs.php.suback.php'));
   require('custom/modules/Leads/metadata/detailviewdefs.php');
   $fields = array();
   $new_fields = array();
   foreach($viewdefs['Leads']['DetailView']['panels'] as $panel) {
   	  foreach($panel as $row) {
   	  	 foreach($row as $col_key=>$col) {
   	  	 	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
   	  	 	$fields[$id] = $col;
   	  	    if(!empty($id) && !isset($pre_upgrade_fields[$id])) {
   	  	 	   $new_fields[$id] = $id;
   	  	 	}   	  	 	
   	  	 }
   	  }
   }
   
   //echo var_export($new_fields, true);
   //echo var_export($viewdefs['Leads']['DetailView']['panels'], true);
   $this->assertTrue(count($new_fields) == 1 && isset($new_fields['website']), 'Assert that website was the only field added');
   $this->assertTrue(isset($fields['website']), 'Assert that website field was added');
   
   $panel_keys = array_keys($viewdefs['Leads']['DetailView']['panels']);
   $end_key = end($panel_keys);
   
   $end_row = end(array_keys($viewdefs['Leads']['DetailView']['panels'][$end_key]));
   $this->assertTrue($viewdefs['Leads']['DetailView']['panels'][$end_key][$end_row][0] == 'website', 'Assert that website field was added to new space in new row');
}
*/

function test_600_leads_editview_merge() {		
	
   $this->assertTrue(file_exists('custom/modules/Leads/metadata/editviewdefs.php'));	
   require('custom/modules/Leads/metadata/editviewdefs.php');
   $pre_upgrade_fields = array();
   $pre_upgrade_panels = array();
   foreach($viewdefs['Leads']['EditView']['panels'] as $panel_key=>$panel) {
   	foreach($panel as $row) {  	 
	   	foreach($row as $col_key=>$col) {
	   	  	 	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
	   	  	 	if(!empty($id) && !is_array($id)) {
	   	  	 	   $pre_upgrade_fields[$id] = $col;
	   	  	 	}
	   	}
   	}
   } 	
	
   require_once('modules/UpgradeWizard/SugarMerge/EditViewMerge.php');
   $this->merge = new EditViewMerge();	
   $this->merge->merge('Leads', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/551/modules/Leads/metadata/editviewdefs.php', 'modules/Leads/metadata/editviewdefs.php', 'custom/modules/Leads/metadata/editviewdefs.php');
   $this->assertTrue(file_exists('custom/modules/Leads/metadata/editviewdefs.php.suback.php'));
   require('custom/modules/Leads/metadata/editviewdefs.php');
   $fields = array();
   $new_fields = array();
   foreach($viewdefs['Leads']['EditView']['panels'] as $panel) {
   	  foreach($panel as $row) {
   	  	 foreach($row as $col_key=>$col) {
   	  	 	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
   	  	 	
   	  	 	if(empty($id) || !is_string($id)) {
   	  	 	   continue;
   	  	 	}
   	  	 	
   	  	 	$fields[$id] = $col;
   	  	    if(!isset($pre_upgrade_fields[$id])) {
   	  	 	   $new_fields[$id] = $id;
   	  	 	}   	  	 	
   	  	 }
   	  }
   }
   
   //echo var_export($new_fields, true);
   //echo var_export($viewdefs['Leads']['EditView'], true);
   $this->assertTrue(count($new_fields) == 1 && isset($new_fields['website']), 'Assert that website was the only field added');
   $this->assertTrue(isset($fields['website']), 'Assert that website field was added');
   $end = end(array_keys($viewdefs['Leads']['EditView']['panels']['lbl_description_information']));
   $this->assertTrue(isset($viewdefs['Leads']['EditView']['panels']['lbl_description_information'][$end][0]) && ($viewdefs['Leads']['EditView']['panels']['lbl_description_information'][$end][0] == 'website'), 'Assert that website field was added to new space in row on lbl_description_information panel');
}


}

?>