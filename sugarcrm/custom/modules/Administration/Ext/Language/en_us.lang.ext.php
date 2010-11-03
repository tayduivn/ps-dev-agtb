<?php 
 //WARNING: The contents of this file are auto-generated


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

$mod_strings['LBL_SUBINFOS_SETTINGS_TITLE'] = 'Subscription Information';
$mod_strings['LBL_SUBINFOS_SETTINGS'] = 'View and update the subscription reports';




$sniplang = array(
    'LBL_SNIP_TITLE' => 'SNIP',
    'LBL_SNIP_DESC' => 'Configure offline archiving (SNIP)',
    'LBL_REGISTER_SNIP' => 'Register for SNIP',
	'LBL_REGISTER_SNIP_DESC' => 'Register this application for the SNIP service',
);
$mod_strings = array_merge($mod_strings, $sniplang);

	$mod_strings['ENABLE_CAPTCHA'] = 'Enable Captcha Validations';
	$mod_strings['ERR_UW_ACCEPT_LICENSE'] = 'Before proceeding you must accept the License Agreement';		
	$mod_strings['LBL_ENABLE_MAILMERGE'] = 'Enable Mail Merge?';
	$mod_strings['LBL_LDAP_USER_FILTER_DESC'] = 'Any additional filter params to apply when authenticating users e.g.<em>is_sugar_user=1 or (is_sugar_user=1)(is_sales=1)</em>';
    	$mod_strings['LBL_LDAP_BIND_ATTRIBUTE_DESC'] = 'For Binding the LDAP User Examples:[<b>AD:</b>&nbsp;userPrincipalName] [<b>openLDAP:</b>&nbsp;userPrincipalName] [<b>Mac&nbsp;OS&nbsp;X:</b>&nbsp;uid] ';
    	$mod_strings['LBL_LDAP_LOGIN_ATTRIBUTE_DESC'] = 'For searching for the LDAP User Examples:[<b>AD:</b>&nbsp;userPrincipalName] [<b>openLDAP:</b>&nbsp;dn] [<b>Mac&nbsp;OS&nbsp;X:</b>&nbsp;dn] ';
    	$mod_strings['LBL_LDAP_SERVER_PORT_DESC'] = 'Example: <em>389 or 636 for SSL</em>';
	$mod_strings['LBL_LDAP_GROUP_NAME_DESC'] = 'Example <em>cn=sugarcrm</em>';
    	$mod_strings['LBL_LDAP_USER_DN_DESC'] = 'Example: <em>ou=people,dc=example,dc=com</eM>';
    	$mod_strings['LBL_TAXRATES'] = 'Configure the list of available tax rates';
    	$mod_strings['LBL_SCORE_SETTINGS'] = 'Record Scoring';
	$mod_strings['LBL_SCORE_SETTINGS_DESC'] = 'Enable/Disable the scoring system and set rules for scoring';




$mod_strings['LBL_DEFAULT_COUNTRY_CODE'] = 'Country Code:';
$mod_strings['LBL_DEFAULT_AREA_CODE'] = 'Area Code:';
$mod_strings['LBL_DEFAULT_ITL_CODE'] = 'International Dial Code:';
$mod_strings['LBL_PLANNED_CALL_PERIOD'] = 'Planned Call Period:';
$mod_strings['LBL_OPPORTUNITY_STATUS_EXCLUDE'] = 'Opportunity Sales Stage Filter:';
$mod_strings['LBL_CASE_STATUS_EXCLUDE'] = 'Case Status Filter:';
$mod_strings['LBL_LEAD_STATUS_EXCLUDE'] = 'Lead Status Filter:';
$mod_strings['LBL_SHOW_PLANNED_CALLS'] = 'Show Planned Calls:';
$mod_strings['LBL_SHOW_RELATED_OPPORTUNITIES'] = 'Show Related Opportunities:';
$mod_strings['LBL_SHOW_RELATED_CASES'] = 'Show Related Cases:';
$mod_strings['LBL_SHOW_RELATED_ACCOUNT_CONTACTS'] = 'Show Related Account Contacts:';
$mod_strings['LBL_CA_PHONE'] = 'Phone Number:';
$mod_strings['LBL_CA_DIRECTION'] = 'Direction:';
$mod_strings['UAE_ADMIN'] = "Fonality";
$mod_strings['LBL_UAE_ADMIN_DESC'] = "Configure the features and settings of the Fonality module.";
$mod_strings['LBL_DIAL_TITLE'] = "Click to Dial Settings";
$mod_strings['LBL_DIAL_DESCRIPTION'] = "Configure click to dial settings.";
$mod_strings['LBL_CA_TITLE'] = "Call Assistant Settings";
$mod_strings['LBL_CA_DESCRIPTION'] = "Configure call assistant settings.";
$mod_strings['LBL_RL_TITLE'] = "Repair Layouts";
$mod_strings['LBL_RL_DESCRIPTION'] = "Repair the layouts of Accounts, Contacts, Leads and Targets to enable Click to Dial on all phone fields.";
$mod_strings['LBL_UAE_SUPPORT_TITLE'] = "Support";
$mod_strings['LBL_UAE_SUPPORT_DESCRIPTION'] = "Send log files to Fonality to troubleshoot problems.";
$mod_strings['LBL_PBX_SETTINGS_TITLE'] = "PBX Settings";
$mod_strings['LBL_PBX_SETTINGS_DESCRIPTION'] = "Manage PBX Settings used for click to dial for all users.";
$mod_strings['LBL_FON_LICENSE_TITLE'] = "Fonality License";
$mod_strings['LBL_FON_LICENSE_DESCRIPTION'] = "Verify Fonality License.";

?>