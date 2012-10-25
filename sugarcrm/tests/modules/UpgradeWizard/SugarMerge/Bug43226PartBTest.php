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

class Bug43226PartBTest extends Sugar_PHPUnit_Framework_TestCase  {
	
var $merge;

function setUp() {
    SugarTestMergeUtilities::setupFiles(array('Documents'), array('detailviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/metadata_files');
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();
}
function test_uploadfile_convert_merge_610() {
   require_once 'modules/UpgradeWizard/SugarMerge/DetailViewMerge.php';
   $this->merge = new DetailViewMerge();
   $this->merge->merge('Documents', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/610/modules/Documents/metadata/detailviewdefs.php','modules/Documents/metadata/detailviewdefs.php','custom/modules/Documents/metadata/detailviewdefs.php');

   require('custom/modules/Documents/metadata/detailviewdefs.php');

   $foundUploadFile = 0;
   $foundFilename = 0;

   foreach ( $viewdefs['Documents']['DetailView']['panels'] as $panel ) {
       foreach ( $panel as $row ) {
           foreach ( $row as $col ) {
               if ( is_array($col) ) {
                   $fieldName = $col['name'];
               } else {
                   $fieldName = $col;
               }
               
               if ( $fieldName == 'filename' ) {
                   $foundFilename++;
               } else if ( $fieldName == 'uploadfile' ) {
                   $foundUploadFile++;
               }
           }
       }
   }
   
   $this->assertTrue($foundUploadFile==0,'Uploadfile field still exists, should be filename');
   $this->assertTrue($foundFilename>0,'Filename field doesn\'t exit, it should');

   if ( file_exists('custom/modules/Documents/metadata/detailviewdefs-testback.php') ) {
       copy('custom/modules/Documents/metadata/detailviewdefs-testback.php','custom/modules/Documents/metadata/detailviewdefs.php');
       unlink('custom/modules/Documents/metadata/detailviewdefs-testback.php');
   }
}

function test_uploadfile_convert_merge_600() {
   require_once 'modules/UpgradeWizard/SugarMerge/DetailViewMerge.php';
   $this->merge = new DetailViewMerge();
   $this->merge->merge('Documents', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/600/modules/Documents/metadata/detailviewdefs.php','modules/Documents/metadata/detailviewdefs.php','custom/modules/Documents/metadata/detailviewdefs.php');

   require('custom/modules/Documents/metadata/detailviewdefs.php');

   $foundUploadFile = 0;
   $foundFilename = 0;

   foreach ( $viewdefs['Documents']['DetailView']['panels'] as $panel ) {
       foreach ( $panel as $row ) {
           foreach ( $row as $col ) {
               if ( is_array($col) ) {
                   $fieldName = $col['name'];
               } else {
                   $fieldName = $col;
               }
               
               if ( $fieldName == 'filename' ) {
                   $foundFilename++;
               } else if ( $fieldName == 'uploadfile' ) {
                   $foundUploadFile++;
               }
           }
       }
   }
   
   $this->assertTrue($foundUploadFile==0,'Uploadfile field still exists, should be filename');
   $this->assertTrue($foundFilename>0,'Filename field doesn\'t exit, it should');

   if ( file_exists('custom/modules/Documents/metadata/detailviewdefs-testback.php') ) {
       copy('custom/modules/Documents/metadata/detailviewdefs-testback.php','custom/modules/Documents/metadata/detailviewdefs.php');
       unlink('custom/modules/Documents/metadata/detailviewdefs-testback.php');
   }
}

}
