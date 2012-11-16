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
require_once('modules/UpgradeWizard/UpgradeRemoval.php');

class Bug49955Test extends Sugar_PHPUnit_Framework_TestCase  {

var $merge;

function setUp() {
   SugarTestMergeUtilities::setupFiles(array('Notes'), array('editviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/metadata_files');

    if(file_exists('custom/backup/modules/DocumentRevisions'))
    {
  	   rmdir_recursive('custom/backup/modules/DocumentRevisions');
    }

    file_put_contents('modules/DocumentRevisions/EditView.html', 'test');
    file_put_contents('modules/DocumentRevisions/DetailView.html', 'test');
    file_put_contents('modules/DocumentRevisions/EditView.php', 'test');
    file_put_contents('modules/DocumentRevisions/DetailView.php', 'test');
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();

   if(file_exists('custom/backup/modules/DocumentRevisions'))
   {
  	   rmdir_recursive('custom/backup/modules/DocumentRevisions');
   }

   if(file_exists('modules/DocumentRevisions/EditView.html'))
   {
       SugarAutoLoader::unlink('modules/DocumentRevisions/EditView.html');
   }

   if(file_exists('modules/DocumentRevisions/DetailView.html'))
   {
       SugarAutoLoader::unlink('modules/DocumentRevisions/DetailView.html');
   }

   if(file_exists('modules/DocumentRevisions/EditView.php'))
   {
       SugarAutoLoader::unlink('modules/DocumentRevisions/EditView.php');
   }

   if(file_exists('modules/DocumentRevisions/DetailView.php'))
   {
       SugarAutoLoader::unlink('modules/DocumentRevisions/DetailView.php');
   }
}

function test_filename_convert_merge() {
   require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
   $this->merge = new EditViewMerge();
   $this->merge->merge('Notes', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/610/modules/Notes/metadata/editviewdefs.php','modules/Notes/metadata/editviewdefs.php','custom/modules/Notes/metadata/editviewdefs.php');
   require('custom/modules/Notes/metadata/editviewdefs.php');

   $foundFilename = 0;
   $fileField = '';

   foreach ( $viewdefs['Notes']['EditView']['panels'] as $panel ) {
       foreach ( $panel as $row ) {
           foreach ( $row as $col ) {
               if ( is_array($col) ) {
                   $fieldName = $col['name'];
               } else {
                   $fieldName = $col;
               }

               if ( $fieldName == 'filename' ) {
                   $fileField = $col;
                   break;
               }
           }
       }
   }

   $this->assertNotEmpty($fileField,'Filename field doesn\'t exit, it should');
   $this->assertTrue(is_string($fileField) && $fileField == 'filename', 'Filename field not converted to string');

   if ( file_exists('custom/modules/Notes/metadata/editviewdefs-testback.php') ) {
       copy('custom/modules/Notes/metadata/editviewdefs-testback.php','custom/modules/Notes/metadata/editviewdefs.php');
       unlink('custom/modules/Notes/metadata/editviewdefs-testback.php');
   }

   //Now test the DocumentRevisions cleanup
    $instance = new UpgradeRemoval49955Mock();
  	$instance->processFilesToRemove($instance->getFilesToRemove(624));
    $this->assertTrue(!file_exists('modules/DocumentRevisions/EditView.html'));
    $this->assertTrue(!file_exists('modules/DocumentRevisions/DetaillView.html'));
    $this->assertTrue(!file_exists('modules/DocumentRevisions/EditView.php'));
    $this->assertTrue(!file_exists('modules/DocumentRevisions/DetailView.html'));

}

}

class UpgradeRemoval49955Mock extends UpgradeRemoval
{

public function getFilesToRemove($version)
{
	$files = array();
	$files[] = 'modules/DocumentRevisions/EditView.html';
    $files[] = 'modules/DocumentRevisions/DetailView.html';
    $files[] = 'modules/DocumentRevisions/EditView.php';
    $files[] = 'modules/DocumentRevisions/DetailView.php';
	return $files;
}


}