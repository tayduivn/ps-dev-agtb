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

        parent::DashletGeneric($id, $def);

        if(empty($def['title'])) $this->title = translate('LBL_HOMEPAGE_TITLE', 'SugarFavorites');

        $this->searchFields = $dashletData['SugarFavoritesDashlet']['searchFields'];
        $this->columns = $dashletData['SugarFavoritesDashlet']['columns'];
        $this->isConfigurable = false;
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
}