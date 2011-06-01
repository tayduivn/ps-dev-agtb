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

require_once 'modules/DynamicFields/templates/Fields/TemplateInt.php';
require_once 'modules/DynamicFields/templates/Fields/TemplateDate.php';

class TemplateDateTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $hasExistingCustomSearchFields = false;
    
    public function setUp()
    {	
		if(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'))
		{
		   $this->hasExistingCustomSearchFields = true;
		   copy('custom/modules/Opportunities/metadata/SearchFields.php', 'custom/modules/Opportunities/metadata/SearchFields.php.bak');
		   unlink('custom/modules/Opportunities/metadata/SearchFields.php');
		} else if(!file_exists('custom/modules/Opportunities/metadata')) {
		   mkdir_recursive('custom/modules/Opportunities/metadata');
		}
    }
    
    public function tearDown()
    {		

    	if(!$this->hasExistingCustomSearchFields)
		{
		   unlink('custom/modules/Opportunities/metadata/SearchFields.php');
		}    	
    	
		if(file_exists('custom/modules/Opportunities/metadata/SearchFields.php.bak')) {
		   copy('custom/modules/Opportunities/metadata/SearchFields.php.bak', 'custom/modules/Opportunities/metadata/SearchFields.php');
		   unlink('custom/modules/Opportunities/metadata/SearchFields.php.bak');
		}

    }
    
    public function testEnableRangeSearchInt()
    {
		$_REQUEST['view_module'] = 'Opportunities';
		$_REQUEST['name'] = 'probability';
		$templateDate = new TemplateInt();
		$templateDate->enable_range_search = true;
		$templateDate->populateFromPost();
		$this->assertTrue(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'));
		include('custom/modules/Opportunities/metadata/SearchFields.php');
		$this->assertTrue(isset($searchFields['Opportunities']['range_probability']));
		$this->assertTrue(isset($searchFields['Opportunities']['start_range_probability']));
		$this->assertTrue(isset($searchFields['Opportunities']['end_range_probability']));
		$this->assertTrue(!isset($searchFields['Opportunities']['range_probability']['is_date_field']));
		$this->assertTrue(!isset($searchFields['Opportunities']['start_range_probability']['is_date_field']));
		$this->assertTrue(!isset($searchFields['Opportunities']['end_range_probability']['is_date_field']));			
    }
    
    public function testEnableRangeSearchDate()
    {
		$_REQUEST['view_module'] = 'Opportunities';
		$_REQUEST['name'] = 'date_closed';
		$templateDate = new TemplateDate();
		$templateDate->enable_range_search = true;
		$templateDate->populateFromPost();
		$this->assertTrue(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'));
		include('custom/modules/Opportunities/metadata/SearchFields.php');
		$this->assertTrue(isset($searchFields['Opportunities']['range_date_closed']));
		$this->assertTrue(isset($searchFields['Opportunities']['start_range_date_closed']));
		$this->assertTrue(isset($searchFields['Opportunities']['end_range_date_closed']));
		$this->assertTrue(isset($searchFields['Opportunities']['range_date_closed']['is_date_field']));
		$this->assertTrue(isset($searchFields['Opportunities']['start_range_date_closed']['is_date_field']));
		$this->assertTrue(isset($searchFields['Opportunities']['end_range_date_closed']['is_date_field']));		
    }    
    
}
?>