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
require_once('service/v3_1/SugarWebServiceUtilv3_1.php');

class SugarWebServiceUtilv4 extends SugarWebServiceUtilv3_1
{
    function get_module_view_defs($moduleName, $type, $view)
    {
        require_once('include/MVC/View/SugarView.php');
        $metadataFile = null;
        $results = array();
        if( empty($moduleName) )
            return $results;
   
        $view = strtolower($view);
        switch (strtolower($type)){
            case 'wireless':
                if( $view == 'list'){
                    require_once('include/SugarWireless/SugarWirelessListView.php');
                    $GLOBALS['module'] = $moduleName; //WirelessView keys off global variable not instance variable...
                    $v = new SugarWirelessListView();
                    $results = $v->getMetaDataFile();
                    $results = self::formatWirelessListViewResultsToArray($results);
                    
                }
                elseif ($view == 'subpanel')
                    $results = $this->get_subpanel_defs($moduleName, $type);
                else{
                    require_once('include/SugarWireless/SugarWirelessView.php');
                    $v = new SugarWirelessView();
                    $v->module = $moduleName;
                    $fullView = ucfirst($view) . 'View';
                    $meta = $v->getMetaDataFile('Wireless' . $fullView);
                    $metadataFile = $meta['filename'];
                    require($metadataFile);
                    //Wireless detail metadata may actually be just edit metadata.
                    $results = isset($viewdefs[$meta['module_name']][$fullView] ) ? $viewdefs[$meta['module_name']][$fullView] : $viewdefs[$meta['module_name']]['EditView'];
                }

                break;
            case 'default':
            default:
                if ($view == 'subpanel')
                    $results = $this->get_subpanel_defs($moduleName, $type);
                else
                {
                    $v = new SugarView(null,array());
                    $v->module = $moduleName;
                    $v->type = $view;
                    $fullView = ucfirst($view) . 'View';
                    $metadataFile = $v->getMetaDataFile();
                    require_once($metadataFile);
                    if($view == 'list')
                        $results = $listViewDefs[$moduleName];
                    else
                        $results = $viewdefs[$moduleName][$fullView];
                }
        }
        
        //Add field level acls.
        $results = $this->addFieldLevelACLs($moduleName,$type, $view, $results);
        
        return $results;
    }
    
    /**
     * Format the results for wirless list view metadata from an associative array to a 
     * numerically indexed array.  This conversion will ensure that consumers of the metadata
     * can eval the json response and iterative over the results with the order of the fields
     * preserved.
     *
     * @param array $fields
     * @return array
     */
    function formatWirelessListViewResultsToArray($fields)
    {
        $results = array();
        foreach($fields as $key => $defs)
        {
            $defs['name'] = $key;
            $results[] = $defs;
        }
        
        return $results;
    }
    
    /**
     * Equivalent of get_list function within SugarBean but allows the possibility to pass in an indicator
     * if the list should filter for favorites.  Should eventually update the SugarBean function as well.
     *
     */
    function get_data_list($seed, $order_by = "", $where = "", $row_offset = 0, $limit=-1, $max=-1, $show_deleted = 0, $favorites = false, $singleSelect=false)
	{
		$GLOBALS['log']->debug("get_list:  order_by = '$order_by' and where = '$where' and limit = '$limit'");
		if(isset($_SESSION['show_deleted']))
		{
			$show_deleted = 1;
		}
		$order_by=$seed->process_order_by($order_by, null);

		if($seed->bean_implements('ACL') && ACLController::requireOwner($seed->module_dir, 'list') )
		{
			global $current_user;
			$owner_where = $seed->getOwnerWhere($current_user->id);
			if(!empty($owner_where)){
				if(empty($where)){
					$where = $owner_where;
				}else{
					$where .= ' AND '.  $owner_where;
				}
			}
		}
		$params = array();
		if($favorites === TRUE )
		  $params['favorites'] = true;

		$query = $seed->create_new_list_query($order_by, $where,array(),$params, $show_deleted,'',false,null,$singleSelect);
		return $seed->process_list_query($query, $row_offset, $limit, $max, $where);
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
        	    $acl = self::checkModuleRoleAccess($e);
        	    $fav = self::is_favorites_enabled($label);
        	    $enabled_modules[] = array('module_key' => $e,'module_label' => $label, 'favorite_enabled' => $fav, 'acls' => $acl);
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
        	    $acl = self::checkModuleRoleAccess($key);
        	    $fav = self::is_favorites_enabled($label);
        	    $enabled_modules[] = array('module_key' => $key,'module_label' => $label, 'favorite_enabled' => $fav, 'acls' => $acl);
            }
        }

        return $enabled_modules;
    }
    
    /**
     * Return a boolean indicating if the bean name is favorites enabled.
     *
     * @param string The module name
     * @return bool true indicating bean is favorites enabled
     */
    function is_favorites_enabled($module_name)
    {
        global $beanList, $beanFiles;
        
        $fav = FALSE;
        //BEGIN SUGARCRM flav=pro ONLY
        $class_name = $beanList[$module_name];
        if( file_exists($beanFiles[$class_name]) )
        {
            require_once($beanFiles[$class_name]);
            $mod = new $class_name();
            $fav = $mod->isFavoritesEnabled();
        }
        //END SUGARCRM flav=pro ONLY
        return $fav;
    }
    
   /**
	 * Parse wireless editview metadata and add ACL values.
	 *
	 * @param String $module_name
	 * @param array $metadata
	 * @return array Metadata with acls added
	 */
	function metdataAclParserWirelessEdit($module_name, $metadata)
	{
	    global  $beanList, $beanFiles;
	    $class_name = $beanList[$module_name];
	    require_once($beanFiles[$class_name]);
	    $seed = new $class_name();
	    
	    $results = array();
	    $results['templateMeta'] = $metadata['templateMeta'];
	    $aclRows = array();
	    //Wireless metadata only has a single panel definition.
	    foreach ($metadata['panels'] as $row)
	    {
	        $aclRow = array();
	        foreach ($row as $field)
	        {
	            $aclField = array();
	            if( is_string($field) )
	                $aclField['name'] = $field;
	            else 
	                $aclField = $field;
	            
	            if($seed->bean_implements('ACL'))
	                $aclField['acl'] = $this->getFieldLevelACLValue($seed->module_dir, $aclField['name']); 
	            else
	                $aclField['acl'] = ACL_FIELD_DEFAULT;
	            
	            $aclRow[] = $aclField;
	        }
	        $aclRows[] = $aclRow;
	    }
	    
	    $results['panels'] = $aclRows;
	    return $results;
	}
	
	/**
	 * Parse wireless detailview metadata and add ACL values.
	 *
	 * @param String $module_name
	 * @param array $metadata
	 * @return array Metadata with acls added
	 */
	function metdataAclParserWirelessDetail($module_name, $metadata)
	{
	    return self::metdataAclParserWirelessEdit($module_name, $metadata);
	}
    
    /**
	 * Parse wireless listview metadata and add ACL values.
	 *
	 * @param String $module_name
	 * @param array $metadata
	 * @return array Metadata with acls added
	 */
	function metdataAclParserWirelessList($module_name, $metadata)
	{
	    global  $beanList, $beanFiles;
	    $class_name = $beanList[$module_name];
	    require_once($beanFiles[$class_name]);
	    $seed = new $class_name();

	    $results = array();
	    foreach ($metadata as $entry)
	    {
	        $field_name = $entry['name'];
	        if($seed->bean_implements('ACL'))
	            $entry['acl'] = $this->getFieldLevelACLValue($seed->module_dir, strtolower($field_name));
	        else
	            $entry['acl'] = 99;
	            
	        $results[$field_name] = $entry;
	    }
	    
	    return $results;
	}
}