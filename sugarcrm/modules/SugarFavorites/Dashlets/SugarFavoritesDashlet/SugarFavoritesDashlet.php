<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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
/*********************************************************************************
 * $Id$
 * Description:  Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/SugarFavorites/SugarFavorites.php');

class SugarFavoritesDashlet extends DashletGeneric 
{ 
    public function __construct(
        $id, 
        $def = null
        ) 
    {
		global $current_user, $app_strings;
		require('modules/SugarFavorites/metadata/dashletviewdefs.php');
		$this->loadLanguage('SugarFavoritesDashlet', 'modules/SugarFavorites/Dashlets/');
        parent::DashletGeneric($id, $def);

        if(empty($def['title'])) $this->title = translate('LBL_HOMEPAGE_TITLE', 'SugarFavorites');

        $this->searchFields = $dashletData['SugarFavoritesDashlet']['searchFields'];
        $this->columns = $dashletData['SugarFavoritesDashlet']['columns'];
        $this->isConfigurable = true;
        $this->seedBean = new SugarFavorites();   
        $this->filters = array();
    }
    
    public function process() 
    {
        $this->lvs->quickViewLinks = false;

        //create query to get the module types for this user favorites
        global $current_user;
        $lvsParams = array();
        $module_type_query = "select distinct module from sugarfavorites where sugarfavorites.assigned_user_id = '".$current_user->id."' and sugarfavorites.deleted = 0";
        $result = $GLOBALS['db']->query($module_type_query);
        
        
        $lvsParams['custom_from'] = $custom_where  = '';
        //itirate through each retrieved type
        while($row = $GLOBALS['db']->fetchByAssoc($result)) {
        	$module = strtolower($row['module']);
        	//fill in the joins for each retrieved type
	        $lvsParams['custom_from'] .= " LEFT JOIN $module on $module.id = sugarfavorites.record_id ";
	        //add the deleted clause
	        if(empty($custom_where)){
	        	$custom_where .= " $module.deleted =0 ";
	        }else {
	        	$custom_where .=  " OR $module.deleted =0 ";
	        }
        }
        
        //finalize the where deleted clause
        if(!empty($custom_where)){
        	$lvsParams['custom_where'] = " AND ($custom_where) ";
        }
        //pass in query params for processing	
        parent::process($lvsParams);    
    }
    
    /**
     * Displays the configuration form for the dashlet
     * 
     * @return string html to display form
     */
    public function displayOptions() 
    {
        global $app_strings;
        
        $ss = new Sugar_Smarty();
        $this->dashletStrings['LBL_SAVE'] = $app_strings['LBL_SAVE_BUTTON_LABEL'];
        $ss->assign('lang', $this->dashletStrings);
        $ss->assign('id', $this->id);
        $ss->assign('title', $this->title);
        $ss->assign('titleLbl', $this->dashletStrings['LBL_CONFIGURE_TITLE']);
        if($this->isAutoRefreshable()) {
       		$ss->assign('isRefreshable', true);
			$ss->assign('autoRefresh', $GLOBALS['app_strings']['LBL_DASHLET_CONFIGURE_AUTOREFRESH']);
			$ss->assign('autoRefreshOptions', $this->getAutoRefreshOptions());
			$ss->assign('autoRefreshSelect', $this->autoRefresh);
		}
       
        $str = $ss->fetch('modules/SugarFavorites/Dashlets/SugarFavoritesDashlet/SugarFavoritesDashletOptions.tpl');  
        return Dashlet::displayOptions() . $str;
    }  

    /**
     * called to filter out $_REQUEST object when the user submits the configure dropdown
     * 
     * @param array $req $_REQUEST
     * @return array filtered options to save
     */  
    public function saveOptions($req) 
    {
        $options = array();
        $options['title'] = $req['title'];
        $options['autoRefresh'] = empty($req['autoRefresh']) ? '0' : $req['autoRefresh'];
        
        return $options;
    }
}