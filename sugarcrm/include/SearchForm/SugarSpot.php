<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

class SugarSpot 
{	
	/**
	 * Performs the search and returns the HTML widget containing the results
	 *
	 * @param  $query   string what we are searching for
	 * @param  $modules array  modules we are searching in
	 * @param  $offset  int    search result offset
	 * @return string HTML widget
	 */
	public function searchAndDisplay(
	    $query, 
	    $modules, 
	    $offset = -1
	    )
	{
		$query_encoded = urlencode($query);
	    $results = $this->_performSearch($query, $modules, $offset);
		$str = '<div id="SpotResults">';
		$actions=0;
		$foundData = false;
		foreach($results as $m=>$data){ 
			if(empty($data['data'])){
				continue;
			}
			$foundData = true;
			
			$countRemaining = $data['pageData']['offsets']['total'] - count($data['data']);
			if($offset > 0) $countRemaining -= $offset;
			$more = '';
			$data['pageData']['offsets']['next']++;
			if($countRemaining > 0){
				$more = <<<EOHTML
<small class='more' onclick="DCMenu.spotZoom('$query', '$m','{$data['pageData']['offsets']['next']}' )">($countRemaining more)</small>
EOHTML;
			}
			
			$modDisplayString = $m;
			if(isset($GLOBALS['app_list_strings']['moduleList'][$m]))
			    $modDisplayString = $GLOBALS['app_list_strings']['moduleList'][$m];
			
			$str.= "<div>{$modDisplayString} $more</div>";
			$str.= '<ul>';
			foreach($data['data'] as $row){
				$name = '';
				if(!empty($row['NAME'])){
					$name = $row['NAME'];
				}else{
					foreach($row as $k=>$v){
						if(strpos($k, 'NAME') !== false){
							$name = $v;
							break;
						}
					}
				}
			
				//BEGIN SUGARCRM flav=spotactions ONLY
				if ($m == 'Actions') {
					$actions++;
					$str .=  <<<EOHTML
<li><a id='sugaraction{$actions}' href="{$row['VALUE']}">$name</a></li>
EOHTML;
				} 
				else
				//END SUGARCRM flav=spotactions ONLY
				    $str .= <<<EOHTML
<li><a href="index.php?module={$data['pageData']['bean']['moduleDir']}&action=DetailView&record={$row['ID']}">$name</a></li>
EOHTML;
			}
			$str.= '</ul>';
		}
		$str .= <<<EOHTML
<button onclick="document.location.href='index.php?module=Home&action=UnifiedSearch&search_form=false&advanced=false&query_string={$query_encoded}'">{$GLOBALS['app_strings']['LBL_EMAIL_SHOW_READ']}</button>
</div>
EOHTML;
		return $str;
	}
	
	/**
	 * Returns the array containing the $searchFields for a module
	 *
	 * @param  $moduleName string
	 * @return array
	 */
	protected function getSearchFields(
	    $moduleName
	    )
	{
		if(file_exists("modules/{$moduleName}/metadata/SearchFields.php")) {
			$searchFields = array();
		    require "modules/{$moduleName}/metadata/SearchFields.php" ;
			return $searchFields;
		}
		else {
			return array();
		}
	}
	//BEGIN SUGARCRM flav=spotactions ONLY
	/**
	 * Performs a search for actions based upon the query string
	 *
	 * @param  $query           string what we are searching for
	 * @param  $offset          int    search result offset
	 * @param  $max             int    max number of search results returned
	 * @param  $primary_module  string module to search in
	 * @return array
	 */
	protected function _searchActions(
	    $query,
	    $offset = -1,
	    $max,
	    $primary_module
	    ) 
	{
		$action_list = $this->_buildActionCache();
		
		$GLOBALS['matching_keys']=array();
		array_walk($action_list, array($this, '_searchKeys'),array($query,$primary_module));
		$data=array_slice($GLOBALS['matching_keys'],(($offset == -1)? 0 :$offset),$max);

		$pageData['bean'] = array('objectName' => 'Action', 'moduleDir' => 'modules/Action');
		$pageData['offsets'] = array( 'current'=>$offset, 'next'=>$offset, 'prev'=>$offset, 'end'=>$offset, 'total'=>count($GLOBALS['matching_keys']), 'totalCounted'=>count($GLOBALS['matching_keys']));
		
		
		return array('data'=>$data , 'pageData'=>$pageData);;
	}
	//END SUGARCRM flav=spotactions ONLY
	/**
	 * Performs the search
	 *
	 * @param  $query   string what we are searching for
	 * @param  $modules array  modules we are searching in
	 * @param  $offset  int    search result offset
	 * @return array
	 */
	protected function _performSearch(
	    $query, 
	    $modules, 
	    $offset = -1
	    )
	{
		$primary_module='';
		$results = array();
		require_once 'include/SearchForm/SearchForm2.php' ;
		$where = '';
		
		$searchEmail = preg_match('/^([^\%]|\%)*@([^\%]|\%)*$/', $query);
		
		foreach($modules as $moduleName){ 
			if (empty($primary_module)) $primary_module=$moduleName;
			
			$searchFields = SugarSpot::getSearchFields($moduleName);
			$class = $GLOBALS['beanList'][$moduleName];
			$return_fields = array();
			$seed = new $class();
			if (empty($searchFields[$moduleName]))
			    continue;
			    
				if ($class == 'aCase') {
			            $class = 'Case';
				}
				foreach($searchFields[$moduleName] as $k=>$v){
					$keep = false;
					$searchFields[$moduleName][$k]['value'] = $query;

					if(!empty($GLOBALS['dictionary'][$class]['unified_search'])){  
						if(empty($GLOBALS['dictionary'][$class]['fields'][$k]['unified_search'])){
							
							if(isset($searchFields[$moduleName][$k]['db_field'])){
								foreach($searchFields[$moduleName][$k]['db_field'] as $field){
									if(!empty($GLOBALS['dictionary'][$class]['fields'][$field]['unified_search'])){
										$return_fields[] = $field;
										$keep = true;
									}
								}
							}
							if(!$keep){
								if(strpos($k,'email') === false || !$searchEmail) {
									unset($searchFields[$moduleName][$k]);
								}
							}
						}else{
							$return_fields[] = $k;
						}
					}else if(empty($GLOBALS['dictionary'][$class]['fields'][$k]) ){;
						unset($searchFields[$moduleName][$k]);
					}else{
						switch($GLOBALS['dictionary'][$class]['fields'][$k]['type']){
							case 'id':
							case 'date':
							case 'datetime':
							case 'bool':
								unset($searchFields[$moduleName][$k]);
							default:
								$return_fields[] = $k;
								
						}
						
					}
					
				}

		
			$searchForm = new SearchForm ( $seed, $moduleName ) ;
			$searchForm->setup (array ( $moduleName => array() ) , $searchFields , '' , 'saved_views' /* hack to avoid setup doing further unwanted processing */ ) ;
			$where_clauses = $searchForm->generateSearchWhere() ;
			$where = "";
	 		if (count($where_clauses) > 0){
                $where = '(('. implode(' ) OR ( ', $where_clauses) . '))';
            }
			
			$lvd = new ListViewData();
			$lvd->additionalDetails = false;
			$max = ( !empty($sugar_config['max_spotresults_initial']) ? $sugar_config['max_spotresults_initial'] : 5 );
			if($offset !== -1){
				$max = ( !empty($sugar_config['max_spotresults_more']) ? $sugar_config['max_spotresults_more'] : 20 );
			}
			$params = array();
			if ( $moduleName == 'Reports') {
			    $params['overrideOrder'] = true;
			    $params['orderBy'] = 'name';
			}
			$results[$moduleName]= $lvd->getListViewData($seed, $where, $offset,  $max, $return_fields,$params,'id') ;
			
		}
        //BEGIN SUGARCRM flav=spotactions ONLY
        //Search actions...
        $results['Actions'] = $this->_searchActions($query,$offset,$max,$primary_module);
        //END SUGARCRM flav=spotactions ONLY		
        return $results;
	}	
	
	//BEGIN SUGARCRM flav=spotactions ONLY
	/**
	 * Builds the search action cache
	 */
	protected function _buildActionCache() 
	{
	    $action_list = array();
	    $all_menu_files=array();
	    $all_module_menu=array();
	    
		global $current_user, $current_language, $app_list_strings, $mod_list_strings;
		$current_language= (empty($current_language)? "en_us": $current_language);
		
		 $user_action_map_filename = 'cache/modules/'. $current_language . '_sugar_actions_' . $current_user->id . ".php";
		 
		 if (!file_exists($user_action_map_filename)) {
			 $all_menu_files=findAllFiles(getcwd(). "/modules",$all_menu_files,false,"Menu.php");
			 if (!empty($all_menu_files) and is_array($all_menu_files)) {
			     foreach ($all_menu_files as $menu_file) {
			 		
			 		//skip over the import module for now. but we will need a way to add
			 		//that option everywhere....
			 		if (strpos($menu_file,'/Import/') !== false) {
			 			continue;
			 		}
			 		
			 		
					$lang_file_name=dirname($menu_file). '/language/' . $current_language. ".lang.php";
			 		if (!file_exists($lang_file_name)) {
			 			//try the english lang file.
			 			$lang_file_name=dirname($menu_file). '/language/' . "en_us.lang.php";
			 		}
			 		
			 		if(file_exists($lang_file_name) && file_exists($menu_file)){ 
			 			global $mod_strings;
			 			require($lang_file_name);	
			 			
			 			$module_menu = array();
		 				require($menu_file);
		 				$all_module_menu=array_merge($all_module_menu,$module_menu);
					}
                }
			 }

			 foreach ($all_module_menu as $menu_entry )	{
				 	//0: action //1: Label //2: action name //3: Module
					$action_list[$menu_entry[1]]=$menu_entry[0];
			 }
			 
			 
			 //process the admin actions now..
			 if (is_admin($current_user) or is_admin_for_any_module($current_user)) {
			 	global $admin_group_header;
			 	require("modules/Administration/metadata/adminpaneldefs.php");		
				
			 	global $mod_strings;
 				require("modules/Administration/language/". $current_language . ".lang.php");
			 						 	
			 	//access to the menu option is decided in the adminpaneldes.php
			 	foreach ($admin_group_header as $key=>$values) {
			 		//this will be tue for Module level admins only..
			 		if (count($values[3]) == 0) {
			 			continue;
			 		}
			 		foreach ($values[3] as $link_key=>$link_value) {
			 			foreach ($link_value as $def) {
				 			$action_list[$mod_strings[$def[1]]]=$def[3];
			 			}
			 		}
			 	}
			 }
			 
			 file_put_contents($user_action_map_filename,'<?php $action_list='.var_export($action_list,true). '; ?>');
		 } 
		 else {
		 	require ($user_action_map_filename);
		 }
		 
		 return $action_list;
	}
	//END SUGARCRM flav=spotactions ONLY
	/**
     * Function used to walk the array and find keys that map the queried string.
     * if both the pattern and module name is found the promote the string to thet top.
     */
    protected function _searchKeys(
        $item1, 
        $key, 
        $patterns
        ) 
    {
        //make the module name singular....
        if ($patterns[1][strlen($patterns[1])-1] == 's') {
            $patterns[1]=substr($patterns[1],0,(strlen($patterns[1])-1));
        }
        
        $module_exists = stripos($key,$patterns[1]); //primary module name.
        $pattern_exists = stripos($key,$patterns[0]); //pattern provided by the user.
        if ($module_exists !== false and $pattern_exists !== false)  {
            $GLOBALS['matching_keys']= array_merge(array(array('NAME'=>$key, 'ID'=>$key, 'VALUE'=>$item1)),$GLOBALS['matching_keys']);
        } 
        else {
            if ($pattern_exists !== false) {
                $GLOBALS['matching_keys'][]=array('NAME'=>$key, 'ID'=>$key, 'VALUE'=>$item1);
            }
        }
    }
}