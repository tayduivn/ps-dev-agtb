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

class Bug39057Test extends Sugar_PHPUnit_Framework_TestCase  {

var $merge;

function setUp() {
   SugarTestMergeUtilities::setupFiles(array('Opportunities'), array('listviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/od_metadata_files');
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();
}


function test_listviewdefs_merge() {			
   require('custom/modules/Opportunities/metadata/listviewdefs.php');
   $original_fields = array();
   $original_displayed_fields = array();
   foreach($listViewDefs['Opportunities'] as $col_key=>$col) {
   	  	$original_fields[$col_key] = $col;
   	  	if(isset($col['default']) && $col['default']) {
   	  	   $original_displayed_fields[$col_key] = $col;
   	  	}
   }

   require_once 'modules/UpgradeWizard/SugarMerge/ListViewMerge.php';		
   $this->merge = new ListViewMerge();	
   $this->merge->merge('Opportunities', 'tests/modules/UpgradeWizard/SugarMerge/od_metadata_files/554/modules/Opportunities/metadata/listviewdefs.php', 'modules/Opportunities/metadata/listviewdefs.php', 'custom/modules/Opportunities/metadata/listviewdefs.php');
   $this->assertTrue(file_exists('custom/modules/Opportunities/metadata/listviewdefs.php.suback.php'));
   require('custom/modules/Opportunities/metadata/listviewdefs.php');
   $fields = array();
   $displayed_fields = array();
   foreach($listViewDefs['Opportunities'] as $col_key=>$col) {
   	  	$fields[$col_key] = $col;
   	  	if(isset($col['default']) && $col['default']) {
   	  	   $displayed_fields[$col_key] = $col;
   	  	}
   } 
   
   //echo var_export($displayed_fields, true);
   
   $this->assertTrue(isset($original_displayed_fields['AMOUNT_USDOLLAR']['label']));
   $this->assertTrue(isset($displayed_fields['AMOUNT_USDOLLAR']['label']));
   //This tests to ensure that the label value is the same from the custom file even though in the new
   //file we changed the label value, we should preserve the custom value
   if(isset($original_displayed_fields['AMOUNT_USDOLLAR']['label']) && isset($displayed_fields['AMOUNT_USDOLLAR']['label']))
   {
   	  $this->assertNotEquals($original_displayed_fields['AMOUNT_USDOLLAR']['label'], $displayed_fields['AMOUNT_USDOLLAR']['label']);
   }
}


}
?>