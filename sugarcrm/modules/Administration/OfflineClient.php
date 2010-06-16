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
//FILE SUGARCRM flav=pro ONLY






global $mod_strings;
global $app_list_strings;
global $app_strings;
global $current_user;
global $sugar_config;
global $theme;

if (!is_admin($current_user)) sugar_die("Unauthorized access to administration.");

$seed = new System();

$admin = new Administration();
$admin->retrieveSettings();
$system_id = $admin->settings['system_system_id'];
$num_lic_oc = $admin->settings['license_num_lic_oc'];
if(!isset($system_id) || empty($system_id)){
   $system_id = 1;
}
$error = '';
$where = 'system_id != '.$system_id;

$row_count = $seed->getEnabledOfflineClients($seed->create_new_list_query("",$where));
if(isset($_POST['reserve']) && isset($_POST['assigned_user_id']) && !empty($_POST['assigned_user_id'])){
     $system = new System();
     if(!$system->doesUserExist($_POST['assigned_user_id'])){
         if($row_count == $num_lic_oc){
            $error = $mod_strings['ERR_NUM_OFFLINE_CLIENTS_MET'];
         }else{
            $system->user_id = $_POST['assigned_user_id'];
            $system->disabled = 0;
            $system->save();
            $row_count++;
         }
     }else{
        $error = $mod_strings['ERR_OC_USER_ALREADY_EXISTS'];  
     }
}

if(isset($_REQUEST['view']) && ($_REQUEST['view'] == 'disable' || $_REQUEST['view'] == 'enable')){
    if(isset($_REQUEST['record'])){
        $system = new System();
        $system->retrieve($_REQUEST['record']);
        if($system != null && $system->deleted != 1){
            if($_REQUEST['view'] == 'disable'){
                $system->disabled = 1;
                 $system->save();
            }else if($_REQUEST['view'] == 'enable'){
                if($row_count == $num_lic_oc){
                    $error = $mod_strings['ERR_NUM_OFFLINE_CLIENTS_MET'];
                }else{
                    $system->disabled = 0;
                    $system->save();
                }   
            } 
            if(!empty($_SESSION['EXCEEDING_OC_LICENSES']) && $_SESSION['EXCEEDING_OC_LICENSES'] == true){
                if(($row_count-1) <= $num_lic_oc){
                    unset($_SESSION['EXCEEDING_OC_LICENSES']);
                    header('Location: index.php?module=Administration&action=OfflineClient');             
                }
            }
        }   
    }   
}

//QUICK SEARCH
require_once('include/QuickSearchDefaults.php');
require_once('include/JSON.php');
$sqs_objects = array('assigned_user_name' => $qsUser);
$quicksearch_js = $qsScripts;
$json = new JSON(JSON_LOOSE_TYPE);
$quicksearch_js .= '<script type="text/javascript" language="javascript">sqs_objects = ' . $json->encode($sqs_objects) . '</script>';
//QUICK SEARCH


$row_count = $seed->getEnabledOfflineClients($seed->create_new_list_query("",$where));
$ListView = new ListView();
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MANAGE_OFFLINE_CLIENT'], true);
$ListView->initNewXTemplate( 'modules/Administration/OfflineClient.html',$mod_strings);
$ListView->xTemplateAssign("RETURN_URL", "&return_module=".$currentModule."&return_action=ListView");
$ListView->xTemplateAssign("DELETE_INLINE_PNG",  SugarThemeRegistry::current()->getImage('delete_inline','align="absmiddle" alt="{SYSTEM.LBL_DISABLE}" border="0"'));
$ListView->setQuery($where, "", "system_key", "SYSTEM");
$ListView->show_export_button = false;
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

//QUICK SEARCH
/// Users Popup
$popup_request_data = array(
    'call_back_function' => 'set_return',
    'form_name' => 'OfflineClient',
    'field_to_name_array' => array(
        'id' => 'assigned_user_id',
        'user_name' => 'assigned_user_name',
        ),
    );
$ListView->xTemplateAssign('encoded_users_popup_request_data', $json->encode($popup_request_data));
$ListView->xTemplateAssign("JAVASCRIPT", $quicksearch_js);
$ListView->xTemplateAssign("RESERVE_OC_HEADER", get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_RESERVE_OC_SPOT'], false));
//QUICK SEARCH

$user_array = get_user_array(TRUE, "Active");
//admin cannot have an offline client
unset($user_array['1']);
$ListView->xTemplateAssign("SEL_USER", get_select_options_with_id($user_array, ""));
$ListView->processListView($seed, "main", "SYSTEM");

?>
