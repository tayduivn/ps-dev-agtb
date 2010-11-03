<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
if(isset($_FILES['vcard']['tmp_name'])) {
	if (!empty($_FILES['vcard']['tmp_name']) &&  isset($_FILES['vcard']['size']) && $_FILES['vcard']['size'] > 0) {
		require_once('include/vCard.php');
		$vcard = new vCard();
		$record = $vcard->importVCard($_FILES['vcard']['tmp_name'],'LeadContacts');
		header("Location: index.php?action=DetailView&module=LeadContacts&record=$record");
    	exit(); 
	} else {
		header("Location: index.php?action=ImportVCard&module=LeadContacts");
    	exit(); 
	}
}else{
require_once('XTemplate/xtpl.php');
echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_NAME']." ".$mod_strings['LBL_IMPORT_VCARD'], true); 
echo "\n</p>\n";
global $theme;
$error_msg = '';
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
require_once($theme_path.'layout_utils.php');


global $app_strings;
global $app_list_strings;
global $current_language;
$mod_strings = return_module_language($current_language, 'Leads');

$xtpl=new XTemplate ('modules/Contacts/ImportVCard.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("IMAGE_PATH", $image_path);$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);

$xtpl->assign("HEADER", $mod_strings['LBL_IMPORT_VCARD']);

$xtpl->assign("MODULE", $_REQUEST['module']);
if ($error_msg != '')
{
	$xtpl->assign("ERROR", $error_msg);
	$xtpl->parse("main.error");
}


$xtpl->parse("main");

$xtpl->out("main");
 }?>
