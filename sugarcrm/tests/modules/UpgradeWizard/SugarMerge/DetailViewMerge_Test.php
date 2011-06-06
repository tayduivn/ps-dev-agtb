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
require_once 'modules/UpgradeWizard/SugarMerge/DetailViewMerge.php';
require_once 'include/dir_inc.php';

class DetailViewMerge_Test extends Sugar_PHPUnit_Framework_TestCase  {

var $dv_merge;
var $has_quotes_dir = false;
var $has_suback_file = false;

function setUp() {
   global $current_user;
   if(!isset($current_user)) {
   	  $current_user = SugarTestUserUtilities::createAnonymousUser();
   }
   $this->dv_merge = new DetailViewMerge();
   if(!file_exists("custom/modules/Quotes/metadata")){
	  mkdir_recursive("custom/modules/Quotes/metadata", true);
   }
   
   if(file_exists('custom/modules/Quotes/metadata/detailviewdefs.php')) {
   	  $this->has_quotes_dir = true;
   	  copy('custom/modules/Quotes/metadata/detailviewdefs.php', 'custom/modules/Quotes/metadata/detailviewdefs.php.bak');
   }
   
   $this->has_suback_file = file_exists('custom/modules/Quotes/metadata/detailviewdefs.php.suback.php');
   
   copy('tests/modules/UpgradeWizard/SugarMerge/metadata_files/custom/modules/Quotes/metadata/detailviewdefs.php', 'custom/modules/Quotes/metadata/detailviewdefs.php');
}

function tearDown() {
   if(!$this->has_quotes_dir) {
   	  rmdir_recursive('custom/modules/Quotes');
   }  else if(file_exists('custom/modules/Quotes/metadata/detailviewdefs.php.bak')) {
   	  copy('custom/modules/Quotes/metadata/detailviewdefs.php.bak', 'custom/modules/Quotes/metadata/detailviewdefs.php');
      unlink('custom/modules/Quotes/metadata/detailviewdefs.php.bak');
      
      if(!$this->has_suback_file) {
   	     unlink('custom/modules/Quotes/metadata/detailviewdefs.php.suback.php');
   	  }
   }
   

}

function test_520_quotes_detailview_merge() {		
   $this->dv_merge->merge('Quotes', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/520/modules/Quotes/metadata/detailviewdefs.php', 'modules/Quotes/metadata/detailviewdefs.php', 'custom/modules/Quotes/metadata/detailviewdefs.php');
   $this->assertTrue(file_exists('custom/modules/Quotes/metadata/detailviewdefs.php.suback.php'));
   require('custom/modules/Quotes/metadata/detailviewdefs.php');
   $fields = array();
   foreach($viewdefs['Quotes']['DetailView']['panels'] as $panel) {
   	  foreach($panel as $row) {
   	  	 foreach($row as $col_key=>$col) {
   	  	 	$id = is_array($col) && isset($col['name']) ? $col['name'] : $col;
   	  	 	$fields[$id] = $col;
   	  	 }
   	  }
   }
   
   $this->assertTrue(isset($fields['contacts_quotes_1_name']), 'Assert that contacts_quotes_1_name field exists');
   $this->assertTrue(isset($fields['contacts_quotes_2_name']), 'Assert that contacts_quotes_2_name field exists');
}


}

?>