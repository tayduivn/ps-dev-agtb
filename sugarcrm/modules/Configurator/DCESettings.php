<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

 // $Id: EditView.php 30785 2008-01-07 22:53:16Z build $

//FILE SUGARCRM flav=dce ONLY

if(!is_admin($current_user)){
    sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']); 
}

require_once('modules/Administration/Forms.php');
echo getClassicModuleTitle($mod_strings['LBL_MODULE_ID'], array($mod_strings['LBL_MODULE_NAME'],$mod_strings['LBL_DCEMODULE_NAME']), false);
require_once('modules/Configurator/Configurator.php');

$configurator = new Configurator();
$focus = new Administration();

if(!empty($_POST['save'])){
    if(isset($_POST['dce_licensing_password'])){
        $_POST['dce_licensing_password'] = blowfishEncode(blowfishGetKey('dce_licensing_password'), $_POST['dce_licensing_password']);
    }
    $configurator->saveConfig();    
    $focus->saveConfig();
    header('Location: index.php?module=Administration&action=index');
}

$focus->retrieveSettings();
if(isset($focus->settings['dce_licensing_password'])){
    $focus->settings['dce_licensing_password'] = blowfishDecode(blowfishGetKey('dce_licensing_password'), $focus->settings['dce_licensing_password']);
}

$sugar_smarty = new Sugar_Smarty();


$sugar_smarty->assign('MOD', $mod_strings);
$sugar_smarty->assign('APP', $app_strings);
$sugar_smarty->assign('APP_LIST', $app_list_strings);
//$sugar_smarty->assign('config', $configurator->config);
$sugar_smarty->assign('error', $configurator->errors);
//$sugar_smarty->assign('THEMES', SugarThemeRegistry::availableThemes());
//$sugar_smarty->assign('LANGUAGES', get_languages());
$sugar_smarty->assign("JAVASCRIPT",get_set_focus_js());
$sugar_smarty->assign("settings", $focus->settings);


$sugar_smarty->assign("UNIQUE_KEY", $sugar_config['unique_key']);

//assign the message template dropdowns
$CREATE_DRPDWN = return_email_templates ($focus->db, $focus->settings['dce_create_tmpl']);
$sugar_smarty->assign("CREATE_DRPDWN", $CREATE_DRPDWN);

$EVAL_DRPDWN = return_email_templates ($focus->db, $focus->settings['dce_eval_tmpl']);
$sugar_smarty->assign("EVAL_DRPDWN", $EVAL_DRPDWN);

$ARCHIVE_DRPDWN = return_email_templates ($focus->db, $focus->settings['dce_archive_tmpl']);
$sugar_smarty->assign("ARCHIVE_DRPDWN", $ARCHIVE_DRPDWN);

$SUPPORT_USER_DRPDWN = return_email_templates ($focus->db, $focus->settings['dce_toggle_tmpl']);
$sugar_smarty->assign("SUPPORT_USER_DRPDWN", $SUPPORT_USER_DRPDWN);

$UPGRADE_LIVE_DRPDWN = return_email_templates ($focus->db, $focus->settings['dce_upgrade_live_tmpl']);
$sugar_smarty->assign("UPGRADE_LIVE_DRPDWN", $UPGRADE_LIVE_DRPDWN);

$UPGRADE_TEST_DRPDWN = return_email_templates ($focus->db, $focus->settings['dce_upgrade_test_tmpl']);
$sugar_smarty->assign("UPGRADE_TEST_DRPDWN", $UPGRADE_TEST_DRPDWN);

$ERROR_DRPDWN = return_email_templates ($focus->db, $focus->settings['dce_failed_tmpl']);
$sugar_smarty->assign("ERROR_DRPDWN", $ERROR_DRPDWN);


$sugar_smarty->display('modules/Configurator/DCESettings.tpl');


$javascript = new javascript();
$javascript->setFormName("ConfigureDCESettings");
$javascript->addFieldGeneric("dce_templates_dir", "varchar", $mod_strings['DCE_TEMPLATES_DIR'], TRUE, "");
$javascript->addFieldGeneric("dce_primary_it_email", "varchar", $mod_strings['DCE_PRIMARY_IT_EMAIL'], TRUE, "");
$javascript->addFieldGeneric("dce_support_user_time_limit", "int", $mod_strings['DCE_SUPPORT_USER_TIME_LIMIT'], TRUE, "");

echo $javascript->getScript();




function return_email_templates ($db, $select = ''){
    //get list of available message_templates
    $retrieve_sql = 'select name, id from email_templates';
    $returnTmpl = $db->query($retrieve_sql);
    $select_options = "\n<OPTION  value= ''> --- </OPTION>";

    //cycle through and create html
    while ($et = $db->fetchByAssoc($returnTmpl)){
    //if select is passed in
        if(!empty($select) && $select == $et['id']){ 
           //set to selected if it matches passed in select value
            $select_options .= "\n<OPTION  value='".$et['id']."' selected='selected'>".$et['name']."</OPTION>";
        }else{      
           $select_options .= "\n<OPTION  value='".$et['id']."' >".$et['name']."</OPTION>";
        }
    }
   return $select_options;     
}

?>