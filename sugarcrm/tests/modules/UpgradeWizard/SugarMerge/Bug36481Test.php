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

class Bug36481Test extends Sugar_PHPUnit_Framework_TestCase  {

var $ev_merge;
var $has_contacts_dir = false;
var $has_suback_file = false;

function setUp() {
   global $current_user;
   if(!isset($current_user)) {
   	  $current_user = SugarTestUserUtilities::createAnonymousUser();
   }
   if(!file_exists("custom/modules/Contacts/metadata")){
	  mkdir_recursive("custom/modules/Contacts/metadata", true);
   }
   
   if(file_exists('custom/modules/Contacts/metadata/editviewdefs.php')) {
   	  $this->has_contacts_dir = true;
   	  copy('custom/modules/Contacts/metadata/editviewdefs.php', 'custom/modules/Contacts/metadata/editviewdefs.php.bak');
   }
   
   $this->has_suback_file = file_exists('custom/modules/Contacts/metadata/editviewdefs.php.suback.php');
   
   copy('tests/modules/UpgradeWizard/SugarMerge/metadata_files/custom/modules/Contacts/metadata/editviewdefs.php', 'custom/modules/Contacts/metadata/editviewdefs.php');
}

function tearDown() {
	return;
   if(!$this->has_contacts_dir) {
   	  rmdir_recursive('custom/modules/Contacts');
   }  else if(file_exists('custom/modules/Contacts/metadata/editviewdefs.php.bak')) {
   	  copy('custom/modules/Contacts/metadata/editviewdefs.php.bak', 'custom/modules/Contacts/metadata/editviewdefs.php');
      unlink('custom/modules/Contacts/metadata/editviewdefs.php.bak');
      
      if(!$this->has_suback_file) {
   	     unlink('custom/modules/Contacts/metadata/editviewdefs.php.suback.php');
   	  }
   }
   

}

function test_contacts_editview_merge() {
   require_once('modules/UpgradeWizard/SugarMerge/EditViewMerge.php');	
   $this->ev_merge = new EditViewMerge();	
   $this->ev_merge->merge('Contacts', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/550/modules/Contacts/metadata/editviewdefs.php', 'modules/Contacts/metadata/editviewdefs.php', 'custom/modules/Contacts/metadata/editviewdefs.php');
   $this->assertTrue(file_exists('custom/modules/Contacts/metadata/editviewdefs.php.suback.php'));
   require('custom/modules/Contacts/metadata/editviewdefs.php');
   $fields = array();
   foreach($viewdefs['Contacts']['EditView']['panels'] as $panel) {
   	  foreach($panel as $row) {
   	  	 foreach($row as $col_key=>$col) {
   	  	 	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
   	  	 	$fields[$id] = $col;
   	  	 }
   	  }
   }
   
   $this->assertTrue(isset($fields['test_c']), 'Assert that test_c custom field exists');
}


}

?>