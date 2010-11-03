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
/*********************************************************************************

 * Description:  Contains a variety of utility functions used to display UI
 * components such as form headers and footers.  Intended to be modified on a per
 * theme basis.
 ********************************************************************************/

/**
 * Create javascript to validate the data entered into a record.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 */
function get_validate_record_js () {

}

/**
 * Create HTML form to enter a new record with the minimum necessary fields.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 */
function get_new_record_form () {
global $mod_strings;
global $app_strings;
global $app_list_strings;
global $theme;
global $current_user;
global $sugar_version, $sugar_config;

$lbl_required_symbol = $app_strings['LBL_REQUIRED_SYMBOL'];
$lbl_default_status = $app_list_strings['itrequest_status_default_key'];
$lbl_subject = $mod_strings['LBL_SUBJECT'];
$lbl_save_button_title = $app_strings['LBL_SAVE_BUTTON_TITLE'];
$lbl_save_button_key = $app_strings['LBL_SAVE_BUTTON_KEY'];
$lbl_save_button_label = $app_strings['LBL_SAVE_BUTTON_LABEL'];
$user_id = $current_user->id;

$team_id = $current_user->default_team;


$the_form = get_left_form_header($mod_strings['LBL_NEW_FORM_TITLE']);
$the_form .= '<script type="text/javascript" src="include/javascript/popup_parent_helper.js?s=' . $sugar_version . '&c=' . $sugar_config['js_custom_version'] . '"></script>';
$the_form .= <<<EOQ
		<form name="ITRequestSave" onSubmit="return check_form('ITRequestSave')" method="POST" action="index.php">
			<input type="hidden" name="module" value="ITRequests">
			<input type="hidden" name="record" value="">
			<input type="hidden" name="priority" value="P2">
			<input type="hidden" name="status" value="${lbl_default_status}">
			<input type="hidden" name="assigned_user_id" value='${user_id}'>
			<input type="hidden" name="action" value="Save">

			<input type="hidden" name="team_id" value='${team_id}'>

		${lbl_subject}&nbsp;<span class="required">${lbl_required_symbol}</span><br>
		<p><input name='name' type="text" size='27' maxlength="255"value=""><br>
EOQ;

$the_form .= <<<EOQ
<p>		<input title="${lbl_save_button_title}" accessKey="${lbl_save_button_key}" class="button" type="submit" name="button" value="  ${lbl_save_button_label}  " ></p>
		
		</form>
EOQ;
require_once('include/javascript/javascript.php');
require_once('modules/ITRequests/ITRequest.php');
$javascript = new javascript();
$javascript->setFormName('ITRequestSave');
$javascript->setSugarBean(new ITRequest());
$javascript->addRequiredFields('');
$the_form .=$javascript->getScript();
$the_form .= get_left_form_footer();

return $the_form;
}

?>
