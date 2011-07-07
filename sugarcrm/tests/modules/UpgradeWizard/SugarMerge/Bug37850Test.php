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

class Bug37850Test extends Sugar_PHPUnit_Framework_TestCase  {

var $merge;

function setUp() {
   SugarTestMergeUtilities::setupFiles(array('Leads'), array('detailviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/od_metadata_files');
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();
}


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
	
   $this->assertTrue(isset($pre_upgrade_fields['created_by']), 'Assert that the created_by index existed in 551 metadata file');
   $this->assertTrue(!isset($pre_upgrade_fields['date_entered']), 'Assert that the date_entered did not exist in 551 metadata file');
   
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
   
   $this->assertTrue(!isset($new_fields['created_by']), 'Assert that the created_by index does not exists in the merged metadata file');
   $this->assertTrue(isset($new_fields['date_entered']), 'Assert that the date_entered field now exists in the merged metadata file');
}




}

?>