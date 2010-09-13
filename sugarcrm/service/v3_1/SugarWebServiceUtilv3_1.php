<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/
require_once('service/v3/SugarWebServiceUtilv3.php');
class SugarWebServiceUtilv3_1 extends SugarWebServiceUtilv3 
{
	/**
	 * Track a view for a particular bean.  
	 *
	 * @param SugarBean $seed
	 * @param string $current_view
	 */
    function trackView($seed, $current_view)
    {
        $trackerManager = TrackerManager::getInstance();
		if($monitor = $trackerManager->getMonitor('tracker'))
		{
			//BEGIN SUGARCRM flav=pro ONLY
	        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
			//END SUGARCRM flav=pro ONLY
	        $monitor->setValue('date_modified', gmdate($GLOBALS['timedate']->get_db_date_time_format()));
	        $monitor->setValue('user_id', $GLOBALS['current_user']->id);
	        $monitor->setValue('module_name', $seed->module_dir);
	        $monitor->setValue('action', $current_view);
	        $monitor->setValue('item_id', $seed->id);
	        $monitor->setValue('item_summary', $seed->get_summary_text());
	        $monitor->setValue('visible',true);
	        $trackerManager->saveMonitor($monitor, TRUE, TRUE);
		}
    }
    
    /**
     * Examine the wireless_module_registry to determine which modules have been enabled for the mobile view.
     * 
     * @param array $availModules An array of all the modules the user already has access to.
     * @return array Modules enalbed for mobile view.
     */
    function get_visible_mobile_modules($availModules)
    {
        global $app_list_strings;
        
        $enabled_modules = array();
        $availModulesKey = array_flip($availModules);
        foreach ( array ( '','custom/') as $prefix)
        {
        	if(file_exists($prefix.'include/MVC/Controller/wireless_module_registry.php'))
        		require $prefix.'include/MVC/Controller/wireless_module_registry.php' ;
        }
        
        foreach ( $wireless_module_registry as $e => $def )
        {
        	if( isset($availModulesKey[$e]) )
        	{
                $label = !empty( $app_list_strings['moduleList'][$e] ) ? $app_list_strings['moduleList'][$e] : '';
        	    $enabled_modules[] = array('module_key' => $e,'module_label' => $label);
        	}
        }
        
        return $enabled_modules;
    }
    
    /**
     * Examine the application to determine which modules have been enabled..
     * 
     * @param array $availModules An array of all the modules the user already has access to.
     * @return array Modules enabled within the application.
     */
    function get_visible_modules($availModules) 
    {
        global $app_list_strings;
        
        require_once("modules/MySettings/TabController.php");
        $controller = new TabController();
        $tabs = $controller->get_tabs_system();
        $enabled_modules= array();
        $availModulesKey = array_flip($availModules);
        foreach ($tabs[0] as $key=>$value)
        {
            if( isset($availModulesKey[$key]) )
            {
                $label = !empty( $app_list_strings['moduleList'][$key] ) ? $app_list_strings['moduleList'][$key] : '';
        	    $enabled_modules[] = array('module_key' => $key,'module_label' => $label);
            }
        }
        
        return $enabled_modules;
    }
    
    /**
     * Generate unifed search fields for a particular module even if the module does not participate in the unified search.
     *
     * @param string $moduleName
     * @return array An array of fields to be searched against.
     */
    function generateUnifiedSearchFields($moduleName)
    {
        global $beanList, $beanFiles, $dictionary;

        if(!isset($beanList[$moduleName]))
            return array();
            
        $beanName = $beanList[$moduleName];

        if (!isset($beanFiles[$beanName]))
            return array();

        if($beanName == 'aCase') 
            $beanName = 'Case';
			
        $manager = new VardefManager ( );
        $manager->loadVardef( $moduleName , $beanName ) ;

        // obtain the field definitions used by generateSearchWhere (duplicate code in view.list.php)
        if(file_exists('custom/modules/'.$moduleName.'/metadata/metafiles.php')){
            require('custom/modules/'.$moduleName.'/metadata/metafiles.php');
        }elseif(file_exists('modules/'.$moduleName.'/metadata/metafiles.php')){
            require('modules/'.$moduleName.'/metadata/metafiles.php');
        }
 			
        if(!empty($metafiles[$moduleName]['searchfields']))
            require $metafiles[$moduleName]['searchfields'] ;
        elseif(file_exists("modules/{$moduleName}/metadata/SearchFields.php"))
            require "modules/{$moduleName}/metadata/SearchFields.php" ;

        $fields = array();
        foreach ( $dictionary [ $beanName ][ 'fields' ] as $field => $def )
        {
            if (strpos($field,'email') !== false)
                $field = 'email' ;

            //bug: 38139 - allow phone to be searched through Global Search
            if (strpos($field,'phone') !== false)
                $field = 'phone' ;

            if ( isset($def['unified_search']) && $def['unified_search'] && isset ( $searchFields [ $moduleName ] [ $field ]  ))
            {
                    $fields [ $field ] = $searchFields [ $moduleName ] [ $field ] ;
            }
        }
		return $fields;
    }
}