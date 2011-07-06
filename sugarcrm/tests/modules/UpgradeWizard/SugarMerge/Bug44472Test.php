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
require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
require_once 'include/dir_inc.php';

class Bug44472Test extends Sugar_PHPUnit_Framework_TestCase  {

function setUp() {
   SugarTestMergeUtilities::setupFiles(array('Cases'), array('editviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/od_metadata_files/610');
   $this->useOutputBuffering = false;
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();
}


function test620TemplateMetaMergeOnCases() 
{		
   require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
   $this->merge = new EditViewMerge();	
   $this->merge->merge('Cases', 'tests/modules/UpgradeWizard/SugarMerge/od_metadata_files/610/oob/modules/Cases/metadata/editviewdefs.php', 'modules/Cases/metadata/editviewdefs.php', 'custom/modules/Cases/metadata/editviewdefs.php');
   $this->assertTrue(file_exists('custom/modules/Cases/metadata/editviewdefs.php.suback.php'));
   require('custom/modules/Cases/metadata/editviewdefs.php');
   $this->assertTrue(isset($viewdefs['Cases']['EditView']['templateMeta']['form']), 'Assert that the form key is kept on the customized templateMeta section for Cases');
}

function test620TemplateMetaMergeOnMeetings() 
{		
   require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
   $this->merge = new EditViewMergeMock();	
   $this->merge->setModule('Meetings');
   $data = array();
   $data['Meetings'] = array('EditView'=>array('templateMeta'=>array('form')));
   $this->merge->setCustomData($data);
   $newData = array();
   $newData['Meetings'] = array('EditView'=>array('templateMeta'=>array()));
   $this->merge->setNewData($newData);
   $this->merge->testMergeTemplateMeta();
   $newData = $this->merge->getNewData();   
   $this->assertTrue(!isset($newData['Meetings']['EditView']['templateMeta']['form']), 'Assert that we do not take customized templateMeta section for Meetings');
}

function test620TemplateMetaMergeOnCalls() 
{		
   require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
   $this->merge = new EditViewMergeMock();	
   $this->merge->setModule('Calls');
   $data = array();
   $data['Calls'] = array('EditView'=>array('templateMeta'=>array('form')));
   $this->merge->setCustomData($data);   
   $newData = array();
   $newData['Calls'] = array('EditView'=>array('templateMeta'=>array()));
   $this->merge->setNewData($newData);
   $this->merge->testMergeTemplateMeta();
   
   $newData = $this->merge->getNewData();
   $this->assertTrue(!isset($newData['Calls']['EditView']['templateMeta']['form']), 'Assert that we do not take customized templateMeta section for Calls');
}

}

class EditViewMergeMock extends EditViewMerge
{
    function setModule($module)
    {
    	$this->module = $module;
    }
    
    function setCustomData($data)
    {
        $this->customData = $data;	
    }
    
    function setNewData($data)
    {
    	$this->newData = $data;
    }
    
    function getNewData()
    {
    	return $this->newData;
    }
    
    function testMergeTemplateMeta()
    {
    	$this->mergeTemplateMeta();
    }
}

?>