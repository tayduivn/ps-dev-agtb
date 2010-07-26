<?php
if(!defined('sugarEntry') || !sugarEntry)
	die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement("License") which can be viewed at
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
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright(C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: commit.php 56510 2010-05-17 18:54:49Z jenny $
 * Description:
 * Portions created by SugarCRM are Copyright(C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 * *******************************************************************************/


logThis('Upgrade Wizard At Layout Commits');

global $mod_strings;
$curr_lang = 'en_us';
if(isset($GLOBALS['current_language']) && ($GLOBALS['current_language'] != null))
	$curr_lang = $GLOBALS['current_language'];

return_module_language($curr_lang, 'UpgradeWizard');
error_reporting(E_ERROR);
set_time_limit(0);
set_upgrade_progress('layouts','in_progress');

//If the user has seleceted which modules they want to merge, perform the filtering and 
//execute the merge.
if( isset($_POST['layoutSelectedModules']) )
{
    logThis('Layout Commits about to merge metadata');
    
    $availableModulesForMerge = $_SESSION['sugarMergeDryRunResults'];
    $selectedModules  = explode("^,^",$_POST['layoutSelectedModules']);
    $filteredModules = array();
    foreach ( $selectedModules as $moduleKey)
    {
        if(array_key_exists($moduleKey , $availableModulesForMerge))
        {
            logThis("Adding $moduleKey module to filtered layout module list for merge.");
            $filteredModules[] = $moduleKey;
        } 
    }
    
    if(file_exists('modules/UpgradeWizard/SugarMerge/SugarMerge.php'))
    {
        require_once('modules/UpgradeWizard/SugarMerge/SugarMerge.php');
        if(isset($_SESSION['unzip_dir']) && isset($_SESSION['zip_from_dir']))
        {
            logThis('Layout Commits starting three way merge with filtered list ' . print_r($filteredModules, TRUE));
            $merger = new SugarMerge($_SESSION['unzip_dir'].'/'.$_SESSION['zip_from_dir']);
            $layoutMergeData = $merger->mergeAll($filteredModules);
            logThis('Layout Commits finished merged');
        }
    }
	
    $stepBack = $_REQUEST['step'] - 1;
    $stepNext = $_REQUEST['step'] + 1;
    $stepCancel = -1;
    $stepRecheck = $_REQUEST['step'];
    $_SESSION['step'][$steps['files'][$_REQUEST['step']]] = 'success';
    
    logThis('Layout Commits completed successfully');
    $smarty->assign("CONFIRM_LAYOUT_HEADER", $mod_strings['LBL_UW_CONFIRM_LAYOUT_RESULTS']);
    $smarty->assign("CONFIRM_LAYOUT_DESC", $mod_strings['LBL_UW_CONFIRM_LAYOUT_RESULTS_DESC']);
    $showCheckBoxes = FALSE;
}
else 
{
    //Fist visit to the commit layout page.  Display the selection table to the user.
    logThis('Layout Commits about to show selection table');
    $smarty->assign("CONFIRM_LAYOUT_HEADER", $mod_strings['LBL_UW_CONFIRM_LAYOUTS']);
    $smarty->assign("CONFIRM_LAYOUT_DESC", $mod_strings['LBL_LAYOUT_MERGE_DESC']);
    $layoutMergeData = $_SESSION['sugarMergeDryRunResults'];
    $showCheckBoxes = TRUE;
}

$smarty->assign("APP", $app_strings);
$smarty->assign("APP_LIST", $app_list_strings);
$smarty->assign("MOD", $mod_strings);
$smarty->assign("showCheckboxes", $showCheckBoxes);
$layoutMergeData = formatLayoutMergeDataForDisplay($layoutMergeData);
$smarty->assign("METADATA_DATA", $layoutMergeData);
$uwMain = $smarty->fetch('modules/UpgradeWizard/tpls/layoutsMerge.tpl');
    
$showBack = FALSE;
$showCancel = FALSE;
$showRecheck = FALSE;
$showNext = TRUE;

set_upgrade_progress('layouts','done');

/**
 * Format dry run results from SugarMerge output to be used in the selection table.
 *
 * @param array $layoutMergeData
 * @return array
 */
function formatLayoutMergeDataForDisplay($layoutMergeData)
{
    global $mod_strings,$app_list_strings;
    
    $curr_lang = 'en_us';
    if(isset($GLOBALS['current_language']) && ($GLOBALS['current_language'] != null))
    	$curr_lang = $GLOBALS['current_language'];

    $module_builder_language = return_module_language($curr_lang, 'ModuleBuilder');

    $results = array();
    foreach ($layoutMergeData as $k => $v)
    {
        $layouts = array();
        foreach ($v as $layoutPath => $isMerge)
        {
            if( preg_match('/listviewdefs.php/i', $layoutPath) )
                $label = $module_builder_language['LBL_LISTVIEW'];
            else if( preg_match('/detailviewdefs.php/i', $layoutPath) )
                $label = $module_builder_language['LBL_DETAILVIEW'];
            else if( preg_match('/editviewdefs.php/i', $layoutPath) )
                $label = $module_builder_language['LBL_EDITVIEW'];
            else if( preg_match('/quickcreatedefs.php/i', $layoutPath) )
                $label = $module_builder_language['LBL_QUICKCREATE'];
            else if( preg_match('/searchdefs.php/i', $layoutPath) )
                $label = $module_builder_language['LBL_SEARCH'];
            else 
                continue;

            $layouts[] = array('path' => $layoutPath, 'label' => $label);
        }

        $results[$k]['layouts'] = $layouts; 
        $results[$k]['moduleName'] = $app_list_strings['moduleList'][$k]; 
    }

    return $results;
}