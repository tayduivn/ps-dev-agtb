<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/



    $moduleDefs = array();
    $fileName = 'field_arrays.php';

    /************************************************
    * LoadCachedArray
    * PARAMS
    * module_dir - the module directory
    * module - the name of the module
    * key - the type of field array we are referencing, i.e. list_fields,
    *       column_fields, required_fields
    * DESCRIPTION
    * This function is designed to cache references
    * to field arrays that were previously stored in the bean files
    * and have since been moved to seperate files.
    *************************************************/
	function LoadCachedArray($module_dir, $module, $key)
	{
        global $moduleDefs, $fileName;
        
        $cache_key = "load_cached_array.$module_dir.$module.$key";
        $result = sugar_cache_retrieve($cache_key);
        if(!empty($result))
        {
        	// Use EXTERNAL_CACHE_NULL_VALUE to store null values in the cache.
        	if($result == EXTERNAL_CACHE_NULL_VALUE)
        	{
        		return null;
        	}
        	
        	return $result;
        }
        
        if(file_exists('modules/'.$module_dir.'/'.$fileName))
        {
            // If the data was not loaded, try loading again....
            if(!isset($moduleDefs[$module]))
            {
            	include('modules/'.$module_dir.'/'.$fileName);
                $moduleDefs[$module] = $fields_array;
		    }
		    // Now that we have tried loading, make sure it was loaded
            if(empty($moduleDefs[$module]) || empty($moduleDefs[$module][$module][$key]))
            {
                // It was not loaded....  Fail.  Cache null to prevent future repeats of this calculation
				sugar_cache_put($cache_key, EXTERNAL_CACHE_NULL_VALUE);
                return  null;
            }
            
            // It has been loaded, cache the result.
            sugar_cache_put($cache_key, $moduleDefs[$module][$module][$key]);
            return $moduleDefs[$module][$module][$key];
        }
        
        // It was not loaded....  Fail.  Cache null to prevent future repeats of this calculation
        sugar_cache_put($cache_key, EXTERNAL_CACHE_NULL_VALUE);
		return null;
	}
?>