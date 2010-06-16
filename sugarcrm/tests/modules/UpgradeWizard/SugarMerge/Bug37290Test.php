<?php
//FILE SUGARCRM flav=pro ONLY
require_once 'include/dir_inc.php';

class Bug37290Test extends Sugar_PHPUnit_Framework_TestCase  {

var $merge;
var $has_dir;
var $modules;

function setUp() {
   $this->modules = array('Opportunities');
   $this->has_dir = array();
   
   foreach($this->modules as $module) {
	   if(!file_exists("custom/modules/{$module}/metadata")){
		  mkdir_recursive("custom/modules/{$module}/metadata", true);
	   }
	   
	   if(file_exists("custom/modules/{$module}")) {
	   	  $this->has_dir[$module] = true;
	   }
	   
	   $files = array('detailviewdefs', 'editviewdefs', 'searchdefs', 'listviewdefs');
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
	   	   $files = array('detailviewdefs', 'editviewdefs', 'searchdefs', 'listviewdefs');
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


function test_opportunities_editview_merge() {		
   require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
   $this->merge = new EditViewMerge();	
   $this->merge->merge('Opportunities', 'tests/modules/UpgradeWizard/SugarMerge/siupgrade_metadata_files/551/modules/Opportunities/metadata/editviewdefs.php', 'modules/Opportunities/metadata/editviewdefs.php', 'custom/modules/Opportunities/metadata/editviewdefs.php');
   $this->assertTrue(file_exists('custom/modules/Opportunities/metadata/editviewdefs.php.suback.php'));
   require('custom/modules/Opportunities/metadata/editviewdefs.php');
   $fields = array();
   $panels = array();
   
   foreach($viewdefs['Opportunities']['EditView']['panels'] as $panel_key=>$panel) {
   	  $panels[$panel_key] = $panel_key;
   	  foreach($panel as $row) {
   	  	 foreach($row as $col_key=>$col) {
   	  	 	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
   	  	 	if(!empty($id) && !is_array($id)) {
   	  	 	   $fields[$id] = $col;
   	  	 	}
   	  	 }
   	  }
   }
   
   
   //echo var_export($viewdefs['Opportunities']['EditView']['panels'], true);
   
/*
   $this->assertTrue(count($panels) == 9, "Assert that there are 9 panels matching the custom Opportunities EditView layout");
   $this->assertTrue(isset($panels['default']), "Assert that 'default' panel id is present");
   $this->assertTrue(isset($panels['lbl_address_information']), "Assert that 'lbl_address_information' panel id is present");
   $this->assertTrue(isset($panels['lbl_email_addresses']), "Assert that 'lbl_email_addresses' panel id is present");
   $this->assertTrue(isset($panels['lbl_description_information']), "Assert that 'lbl_description_information' panel id is present");
*/

   $custom_fields = array('discount_code_c', 'additional_support_cases_c', 'additional_training_credits_c', 'Term_c', 'Revenue_Type_c',
                          'renewal_date_c', 'order_type_c', 'true_up_c', 'competitor_expiration_c', 'demo_c', 'top20deal_c', 'demo_date_c',
                          'closed_lost_reason_c', 'closed_lost_reason_detail_c', 'Evaluation_Close_Date_c', 'primary_reason_competitor_c',
                          'partner_assigned_to_c', 'accepted_by_partner_c', 'partner_contact_c', 'associated_rep_c', 'competitor_1', 'competitor_2',
   );
   
   foreach($custom_fields as $c_field) {
   		$this->assertTrue(isset($fields["{$c_field}"]), "Assert that custom field {$c_field} is present");
   }
   
  
   $found_team_name = false;
   foreach($viewdefs['Opportunities']['EditView']['panels']['default'] as $row) {
      	foreach($row as $col_key=>$col) {
   	  	 	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
            if($id == 'team_name') {
               $found_team_name = true;
            } 
   	  	 }
   }
   
   $this->assertTrue($found_team_name, "Assert that team_name is present in default panel");  
}


}
?>