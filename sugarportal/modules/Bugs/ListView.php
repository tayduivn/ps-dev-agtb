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
/*********************************************************************************
 * $Id: ListView.php,v 1.71 2006/06/06 17:57:56 majed Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

// setup listview smarty
require_once('include/ListView/ListViewSmarty.php');
if(is_file('custom/portal/modules/Bugs/metadata/listviewdefs.php')) {
    require_once('custom/portal/modules/Bugs/metadata/listviewdefs.php');
}
else {  
    require_once('modules/Bugs/metadata/listviewdefs.php');
}
require_once('include/SearchForm/SearchForm.php');

global $portal, $app_strings;

echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_TITLE'], false) . '</br>'; 


$searchForm = new SearchForm('Bugs');
// handle query
if($searchForm->useGenerateSearch('Bugs')) {
    $where = $searchForm->generateSearchWhere('Bugs', false);
} else {
	$where = array();
}
// BEGIN SUGARCRM flav=pro ONLY 
//Safety, always check for portal_viewable flag
$where[] = array('name'=>'portal_viewable', 'value'=>'1', 'operator'=>'=');	
// END SUGARCRM flav=pro ONLY 

// search form
echo $searchForm->display();
echo '<br><br>';

// listview
$lv = new ListViewSmarty();
$lv->displayColumns = $viewdefs['Bugs']['listview'];
$lv->show_export_button = false;
$lv->multi_select_popup = false;
$lv->setup('Bugs', 'include/ListView/ListViewGeneric.tpl', $where, array());

echo $lv->display();

?>
