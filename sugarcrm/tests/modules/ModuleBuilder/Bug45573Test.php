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
//FILE SUGARCRM flav!=sales ONLY

class Bug45573Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $hasCustomSearchFields;
	
	public function setUp()
	{
	    require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;
	    $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
	    
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	    $GLOBALS['current_user']->is_admin = true;
		
		if(file_exists('custom/modules/Cases/metadata/SearchFields.php'))
		{			
			$this->hasCustomSearchFields = true;
            copy('custom/modules/Cases/metadata/SearchFields.php', 'custom/modules/Cases/metadata/SearchFields.php.bak');
            unlink('custom/modules/Cases/metadata/SearchFields.php');			
		}
	}
	
	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		
		if($this->hasCustomSearchFields && file_exists('custom/modules/Cases/metadata/SearchFields.php.bak'))
		{
		   copy('custom/modules/Cases/metadata/SearchFields.php.bak', 'custom/modules/Cases/metadata/SearchFields.php');
		   unlink('custom/modules/Cases/metadata/SearchFields.php.bak');
		} else if(!$this->hasCustomSearchFields && file_exists('custom/modules/Cases/metadata/SearchFields.php')) {
		   unlink('custom/modules/Cases/metadata/SearchFields.php');
		}
		
		//Refresh vardefs for Cases to reset
		VardefManager::loadVardef('Cases', 'aCase', true); 
	}
	
	/**
	 * testActionAdvancedSearchViewSave
	 * This method tests to ensure that custom SearchFields are created or updated when a search layout change is made
	 */
	public function testActionAdvancedSearchViewSave()
	{
		require_once('modules/ModuleBuilder/controller.php');
		$mbController = new ModuleBuilderController();
		$_REQUEST['view_module'] = 'Cases';
		$_REQUEST['view'] = 'advanced_search';
		$mbController->action_searchViewSave();
		$this->assertTrue(file_exists('custom/modules/Cases/metadata/SearchFields.php'));
		
		require('custom/modules/Cases/metadata/SearchFields.php');
		$this->assertTrue(isset($searchFields['Cases']['range_date_entered']));
		$this->assertTrue(isset($searchFields['Cases']['range_date_entered']['enable_range_search']));
		$this->assertTrue(isset($searchFields['Cases']['range_date_modified']));
		$this->assertTrue(isset($searchFields['Cases']['range_date_modified']['enable_range_search']));
	}
	
	/**
	 * testActionBasicSearchViewSave
	 * This method tests to ensure that custom SearchFields are created or updated when a search layout change is made
	 */
	public function testActionBasicSearchViewSave()
	{
		require_once('modules/ModuleBuilder/controller.php');
		$mbController = new ModuleBuilderController();
		$_REQUEST['view_module'] = 'Cases';
		$_REQUEST['view'] = 'basic_search';
		$mbController->action_searchViewSave();
		$this->assertTrue(file_exists('custom/modules/Cases/metadata/SearchFields.php'));
		
		require('custom/modules/Cases/metadata/SearchFields.php');
		$this->assertTrue(isset($searchFields['Cases']['range_date_entered']));
		$this->assertTrue(isset($searchFields['Cases']['range_date_entered']['enable_range_search']));
		$this->assertTrue(isset($searchFields['Cases']['range_date_modified']));
		$this->assertTrue(isset($searchFields['Cases']['range_date_modified']['enable_range_search']));
	}	
	
	
	/**
	 * testActionAdvancedSearchSaveWithoutAnyRangeSearchFields
	 * One last test to check what would happen if we had a module that did not have any range search fields enabled
	 */
	public function testActionAdvancedSearchSaveWithoutAnyRangeSearchFields()
	{
        //Load the vardefs for the module to pass to TemplateRange
        VardefManager::loadVardef('Cases', 'aCase', true); 
        global $dictionary;      
        $vardefs = $dictionary['Case']['fields'];
        foreach($vardefs as $key=>$def)
        {
        	if(!empty($def['enable_range_search']))
        	{
        		unset($vardefs[$key]['enable_range_search']);
        	}
        }
        
        require_once('modules/DynamicFields/templates/Fields/TemplateRange.php');
        TemplateRange::repairCustomSearchFields($vardefs, 'Cases');	
		
        //In this case there would be no custom SearchFields.php file created
		$this->assertTrue(!file_exists('custom/modules/Cases/metadata/SearchFields.php'));
		
		//Yet we have the defaults set still in out of box settings
		require('modules/Cases/metadata/SearchFields.php');
		$this->assertTrue(isset($searchFields['Cases']['range_date_entered']));
		$this->assertTrue(isset($searchFields['Cases']['range_date_entered']['enable_range_search']));
		$this->assertTrue(isset($searchFields['Cases']['range_date_modified']));
		$this->assertTrue(isset($searchFields['Cases']['range_date_modified']['enable_range_search']));
	}
		
}

?>