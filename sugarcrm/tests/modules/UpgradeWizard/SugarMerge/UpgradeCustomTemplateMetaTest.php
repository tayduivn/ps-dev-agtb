<?php
require_once 'include/dir_inc.php';

class UpgradeCustomTemplateMetaTest extends Sugar_PHPUnit_Framework_TestCase  {
	
var $merge;

function setUp() {
   $this->setOutputBuffering = false;
   SugarTestMergeUtilities::setupFiles(array('Calls'), array('editviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/metadata_files');
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();
}

function testMegreCallsEditviewdefsFor611() {
   require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
   $this->merge = new EditViewMerge();
   $this->merge->merge('Calls', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/611/modules/Calls/metadata/editviewdefs.php','modules/Calls/metadata/editviewdefs.php','custom/modules/Calls/metadata/editviewdefs.php');

   //Load file
   require('custom/modules/Calls/metadata/editviewdefs.php');

   //If we comment out EdtiViewMerge mergeTemplateMeta, then we don't see the forms[0] anymore
   //echo var_export($viewdefs['Calls'], true);
   

}


}