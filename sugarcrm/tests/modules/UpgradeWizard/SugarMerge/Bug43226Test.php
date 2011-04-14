<?php
require_once 'include/dir_inc.php';

class Bug43226Test extends Sugar_PHPUnit_Framework_TestCase  {
	
var $merge;

function setUp() {
   $this->useOutputBuffering = false;
   SugarTestMergeUtilities::setupFiles(array('Documents', 'Notes'), array('editviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/metadata_files');
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();
}

function test_uploadfile_convert_merge() {
    mkdir_recursive('custom/modules/Documents/metadata');
    if ( file_exists('custom/modules/Documents/metadata/editviewdefs.php') ) {
        copy('custom/modules/Documents/metadata/editviewdefs.php','custom/modules/Documents/metadata/editviewdefs-testback.php');
    }
    copy('tests/modules/UpgradeWizard/SugarMerge/metadata_files/610/modules/Documents/metadata/editviewdefs.php','custom/modules/Documents/metadata/editviewdefs.php');

   require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
   $this->merge = new EditViewMerge();
   $this->merge->merge('Documents', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/610/modules/Documents/metadata/editviewdefs.php','modules/Documents/metadata/editviewdefs.php','custom/modules/Documents/metadata/editviewdefs.php');

   require('custom/modules/Documents/metadata/editviewdefs.php');

   print_r($viewdefs['Documents']['EditView']['panels']);

   $foundUploadFile = 0;
   $foundFilename = 0;

   foreach ( $viewdefs['Documents']['EditView']['panels'] as $panel ) {
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

   if ( file_exists('custom/modules/Documents/metadata/editviewdefs-testback.php') ) {
       copy('custom/modules/Documents/metadata/editviewdefs-testback.php','custom/modules/Documents/metadata/editviewdefs.php');
       unlink('custom/modules/Documents/metadata/editviewdefs-testback.php');
   }
}

}