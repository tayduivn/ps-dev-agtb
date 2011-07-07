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
 
require_once('modules/Home/views/view.additionaldetailsretrieve.php');

/**
 * @ticket bug40216
 */
class Bug40216Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $moduleName;
    
    public function setUp() 
    {
    	   $this->moduleName = 'TestModule'.mt_rand();
        
        sugar_mkdir("modules/{$this->moduleName}/metadata",null,true);
        sugar_mkdir("custom/modules/{$this->moduleName}/metadata",null,true);
    }
    
    public function tearDown() 
    {
        rmdir_recursive("modules/{$this->moduleName}");
        rmdir_recursive("custom/modules/{$this->moduleName}");
    }
	
    public function testAdditionalDetailsMetadataFileIsFound()
    {
    	   sugar_touch("modules/{$this->moduleName}/metadata/additionalDetails.php");
    	
    	   $viewObject = new Bug40216Mock;
    	
    	   $this->assertEquals(
    	       "modules/{$this->moduleName}/metadata/additionalDetails.php",
    	       $viewObject->getAdditionalDetailsMetadataFile($this->moduleName)
    	       );
    }
    
    public function testCustomAdditionalDetailsMetadataFileIsFound()
    {
    	   sugar_touch("custom/modules/{$this->moduleName}/metadata/additionalDetails.php");
    	
    	   $viewObject = new Bug40216Mock;
    	
    	   $this->assertEquals(
    	       "custom/modules/{$this->moduleName}/metadata/additionalDetails.php",
    	       $viewObject->getAdditionalDetailsMetadataFile($this->moduleName)
    	       );
    }
    
    public function testCustomAdditionalDetailsMetadataFileIsUsedBeforeNonCustomOne()
    {
    	   sugar_touch("modules/{$this->moduleName}/metadata/additionalDetails.php");
    	   sugar_touch("custom/modules/{$this->moduleName}/metadata/additionalDetails.php");
    	
    	   $viewObject = new Bug40216Mock;
    	
    	   $this->assertEquals(
    	       "custom/modules/{$this->moduleName}/metadata/additionalDetails.php",
    	       $viewObject->getAdditionalDetailsMetadataFile($this->moduleName)
    	       );
    }
}

class Bug40216Mock extends HomeViewAdditionaldetailsretrieve
{
    public function getAdditionalDetailsMetadataFile(
        $moduleName
        )
    {
        return parent::getAdditionalDetailsMetadataFile($moduleName);
    }
}