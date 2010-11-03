<?php 
 //WARNING: The contents of this file are auto-generated


$admin_option_defs = array();
$admin_option_defs['Administration']['register_snip']=array('icon_AdminThemes','LBL_REGISTER_SNIP','LBL_REGISTER_SNIP_DESC','./index.php?module=SNIP&action=RegisterForSnip');
$admin_group_header[]= array('LBL_SNIP_TITLE','',false,$admin_option_defs, 'LBL_SNIP_DESC');


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

$admin_option_defs=array();
$admin_option_defs['subinfos']= array($image_path . 'Administration','LBL_SUBINFOS_SETTINGS_TITLE','LBL_SUBINFOS_SETTINGS','./index.php?module=SubInfos&action=index');
$admin_group_header[]= array('LBL_SUBINFOS_SETTINGS_TITLE','',false,$admin_option_defs);




$admin_option_defs=array();
$admin_option_defs['Administration']['dial'] = array('Calls','LBL_DIAL_TITLE','LBL_DIAL_DESCRIPTION','index.php?module=Administration&action=ConfigureDialSettings');
$admin_option_defs['Administration']['call_assistant'] = array('Calls','LBL_CA_TITLE','LBL_CA_DESCRIPTION','index.php?module=Administration&action=ConfigureCASettings');
$admin_option_defs['Administration']['repair_clicktodial_layout'] = array('Repair','LBL_RL_TITLE','LBL_RL_DESCRIPTION','index.php?module=Administration&action=repair_clicktodial_layouts');
$admin_option_defs['Administration']['uae_support'] = array('support_icon','LBL_UAE_SUPPORT_TITLE','LBL_UAE_SUPPORT_DESCRIPTION','index.php?module=Administration&action=uae_support');
$admin_option_defs['Administration']['pbx_settings'] = array('fonuae_PBXSettings','LBL_PBX_SETTINGS_TITLE','LBL_PBX_SETTINGS_DESCRIPTION','index.php?module=fonuae_PBXSettings&action=index');
$admin_group_header[]=array('UAE_ADMIN','',false,$admin_option_defs,'LBL_UAE_ADMIN_DESC');


?>