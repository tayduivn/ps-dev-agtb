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
class Bug37917Test extends Sugar_PHPUnit_Framework_TestCase  {

var $merge;

function setUp() {
   SugarTestMergeUtilities::setupFiles(array('Contacts'), array('editviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/od_metadata_files');
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();
}


function test_contacts_editview_merge() {	
   require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
   $this->merge = new EditViewMerge();	
   $this->merge->merge('Contacts', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/551/modules/Contacts/metadata/editviewdefs.php', 'modules/Contacts/metadata/editviewdefs.php', 'custom/modules/Contacts/metadata/editviewdefs.php');
   $this->assertTrue(file_exists('custom/modules/Contacts/metadata/editviewdefs.php.suback.php'));
   require('custom/modules/Contacts/metadata/editviewdefs.php');
   $fields = array();
   $panels = array();
   
   //echo var_export($viewdefs['Contacts']['EditView']['panels'], true);
   foreach($viewdefs['Contacts']['EditView']['panels'] as $panel_key=>$panel) {
   	  $panels[$panel_key] = $panel_key;
   	  foreach($panel as $r=>$row) {
   	  	 $new_row = true;
   	  	 foreach($row as $col_key=>$col) {
   	  	 	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
   	  	 	if(!empty($id) && !is_array($id)) {
   	  	 	   $fields[$id] = $col;
   	  	 	}
   	  	 }
   	  }
   }
   
   $this->assertTrue(isset($fields['alt_address_postalcode']) && isset($fields['alt_address_city']), 'Assert that alt_address_postalcode and alt_address_city are preserved');
   $this->assertTrue(isset($fields['alt_address_street']) && !isset($fields['alt_address_street']['displayParams']), 'Assert that the original alt_address_street field contents were preserved');
   $this->assertTrue(isset($fields['primary_address_postalcode']) && isset($fields['primary_address_city']), 'Assert that primary_address_postalcode and primary_address_city are preserved');
   $this->assertTrue(isset($fields['primary_address_street']) && !isset($fields['primary_address_street']['displayParams']), 'Assert that the original primary_address_street field contents were preserved');
}


}

?>