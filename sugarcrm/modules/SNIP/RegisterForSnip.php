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
 * $Id: ConfigureTabs.php 51995 2009-10-28 21:55:55Z clee $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once 'modules/SNIP/SugarSNIP.php';

if (!is_admin($current_user)) {
    sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']);
}
global $sugar_config;

$snip = SugarSNIP::getInstance();
$title = get_module_title("", translate('LBL_REGISTER_SNIP').":", true);
$sugar_smarty = new Sugar_Smarty();

$sugar_smarty->assign('APP', $GLOBALS['app_strings']);
$sugar_smarty->assign('MOD', $GLOBALS['mod_strings']);
$sugar_smarty->assign('SUGAR_URL', $snip->getURL());
$snip_active = $snip->isActive();
$sugar_smarty->assign('SNIP_ACTIVE', $snip_active);
if($snip_active) {
    $status = $snip->getStatus();
    if(empty($status)) {
	    $sugar_smarty->assign('SNIP_STATUS_OK', false);
	    $sugar_smarty->assign('SNIP_STATUS', translate('LBL_SNIP_STATUS_FAIL'));
    } else {
	    $ok = $status->status == 'success' || $status->status == 'reset';
	    $sugar_smarty->assign('SNIP_STATUS_OK', $ok);
	    $sugar_smarty->assign('SNIP_STATUS', snipStatusToText($status->status));
	    $accts = array();
	    foreach($status->accounts as $acct) {
	        $accts[] = array("ok" => $acct->status == 'success', "name" => $acct->name,
	        	"status" => snipStatusToText($acct->status), "last" => snipTimeToDisplay($acct->lastcheck));
	    }
	    $sugar_smarty->assign('SNIP_ACCTS', $accts);
    }
}

$sugar_smarty->assign('APP', $GLOBALS['app_strings']);
$sugar_smarty->assign('MOD', $GLOBALS['mod_strings']);
$status=$snip->getStatus();

if ($status=='notpurchased'){
    $snipuser = $snip->getSnipUser();
    $sugar_smarty->assign('SNIP_PURCHASEURL',createPurchaseURL($snipuser));
}
echo $sugar_smarty->fetch('modules/SNIP/RegisterForSnip.tpl');

function createPurchaseURL($snipuser){
    global $sugar_config;
    return "localhost:1337/purchaseSnip?uniquekey={$sugar_config['unique_key']}&snipuser={$snipuser->user_name}&pass={$snipuser->user_hash}";
}

/**
 * Register or unregister this instance with SNIP server
 * @param SugarSNIP $snip SNIP service objects
 */
function registerApplication($snip)
{
    if($_REQUEST['save_config'] == 'disable') {
        return $snip->unregister();
    } else {
        return $snip->register(array(
            "url" => $_REQUEST['snip_url']
	    ));
    }
}

/**
 * Convert status string from SNIP server into display string
 * @param string $status
 */
function snipStatusToText($status)
{
    if($status == 'success') {
        return translate('LBL_SNIP_STATUS_OK');
    }
    if($status == 'reset') {
        return translate('LBL_SNIP_STATUS_RESET');
    }
    if($status == 'down') {
        return translate('LBL_SNIP_STATUS_DOWN');
    }
    return sprintf(translate('LBL_SNIP_STATUS_PROBLEM'), $status);
}

/**
 * Convert timestamp from SNIP server into display string
 * @param int $time
 */
function snipTimeToDisplay($time)
{
    if($time) {
        return $GLOBALS['timedate']->to_display_date_time(gmdate($GLOBALS['timedate']->get_db_date_time_format(),$time));
    } else {
        return translate('LBL_SNIP_NEVER');
    }
}