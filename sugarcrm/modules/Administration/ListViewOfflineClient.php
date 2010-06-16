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
//FILE SUGARCRM flav=ent ONLY


require_once('include/SearchForm/SearchForm.php');



global $mod_strings;
global $app_list_strings;
global $app_strings;
global $current_user;
global $sugar_config;
global $theme;

if (!is_admin($current_user)) sugar_die("Unauthorized access to administration.");

$seed = new System();
$searchForm = new SearchForm('Administration', $seed);

$admin = new Administration();
$admin->retrieveSettings();
$system_id = 1;
if(!empty($admin->settings['system_system_id'])){
	$system_id = $admin->settings['system_system_id'];
}else{
	$admin->saveSetting('system', 'system_id', $system_id);
}

$num_lic_oc = $admin->settings['license_num_lic_oc'];
if(!isset($system_id) || empty($system_id)){
   $system_id = 1;
}
$error = '';
$where = 'system_id != '.$system_id;

$row_count = $seed->getEnabledOfflineClients($seed->create_new_list_query("",$where));

if(isset($_REQUEST['view']) && ($_REQUEST['view'] == 'disable' || $_REQUEST['view'] == 'enable')){
    if(isset($_REQUEST['system_id'])){
        $system = new System();
        $system->retrieve($_REQUEST['system_id']);
        if($system != null && $system->deleted != 1){
            if($_REQUEST['view'] == 'disable'){
                $system->status = 'Inactive';
                 $system->save();
            }else if($_REQUEST['view'] == 'enable'){
                if($row_count == $num_lic_oc){
                    $error = $mod_strings['ERR_NUM_OFFLINE_CLIENTS_MET'];
                }else{
                    $system->status = 'Active';
                    $system->save();
                    $row_count++;
                }   
            } 
            if(!empty($_SESSION['EXCEEDING_OC_LICENSES']) && $_SESSION['EXCEEDING_OC_LICENSES'] == true){
                if(($row_count-1) <= $num_lic_oc){
                    unset($_SESSION['EXCEEDING_OC_LICENSES']);
                    header('Location: index.php?module=Administration&action=ListViewOfflineClient');             
                }
            }
        }   
    }   
}

$user_name = '';
if(isset($_REQUEST['query']))
{
    $user_name = (isset($_REQUEST['user_name']) ? $_REQUEST['user_name'] : ''); 
    require_once('modules/Administration/metadata/SearchFields.php');
    $searchForm->setup();
    $user = new User();
    $searchForm->populateFromRequest('advanced_search');
    
    $where_clauses = $searchForm->generateSearchWhere(true, "Administration"); 
    $search_where = "";
    if (count($where_clauses) > 0 )$search_where= implode(' and ', $where_clauses);
}

if(isset($search_where) && !empty($search_where)){
    $where .= ' AND '.$search_where;
}

//SEARCH
if (!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
    $search_form=new XTemplate ('modules/Administration/OfflineClientSearchForm.html');
    $search_form->assign("MOD", $mod_strings);
    $search_form->assign("APP", $app_strings);
    $search_form->assign("BASIC_SEARCH_PNG", SugarThemeRegistry::current()->getImage('basic_search','alt="'.$app_strings['LNK_BASIC_SEARCH'].'"  border="0"'));
    if (isset($user_name)) $search_form->assign("USER_NAME", $user_name);
    $search_form->assign("JAVASCRIPT", get_clear_form_js());
    echo get_form_header($mod_strings['LBL_OC_SEARCH_FORM_TITLE'], '', false);
    $search_form->parse("main");
    $search_form->out("main");
    echo "\n<BR>\n";
}
//SEARCH

$ListView = new ListView();
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MANAGE_OFFLINE_CLIENT'], true);
$ListView->initNewXTemplate( 'modules/Administration/ListViewOfflineClient.html',$mod_strings);
$ListView->xTemplateAssign("RETURN_URL", "&return_module=".$currentModule."&return_action=ListView");
$ListView->xTemplateAssign("DELETE_INLINE_PNG",  SugarThemeRegistry::current()->getImage('delete_inline','align="absmiddle" alt="{SYSTEM.LBL_DISABLE}" border="0"'));
$ListView->setQuery($where, "", "system_key", "SYSTEM");
$ListView->show_export_button = false;
$ListView->show_mass_update_form = true;
$ListView->show_delete_button = false;
$ListView->show_select_menu = false;
$ListView->show_mass_update = false;
//now we want to display how many offline clients are currently in use
if(!empty($error)){
    $ListView->xTemplateAssign("ERROR", $error);
}    
if($row_count == 0){
     $ListView->xTemplateAssign("OFFLINE_CLIENT_COUNT", $mod_strings['NO_ENABLED_OFFLINE_CLIENTS']);
}else{
    $ListView->xTemplateAssign("OFFLINE_CLIENT_COUNT", "$row_count of $num_lic_oc ".$mod_strings['ENABLED_OFFLINE_CLIENTS']);
}

$ListView->processListView($seed, "main", "SYSTEM");

?>
