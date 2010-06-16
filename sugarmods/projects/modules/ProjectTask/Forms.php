<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Forms
 *
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: Forms.php 13782 2006-06-06 17:58:55 +0000 (Tue, 06 Jun 2006) majed $

global $theme;
require_once('themes/'.$theme.'/layout_utils.php');

require_once('XTemplate/xtpl.php');
require_once('include/utils.php');

$image_path = 'themes/'.$theme.'/images/';

    /*

function get_new_record_form()
{
	if(!ACLController::checkAccess('ProjectTask', 'edit', true))return '';
	global $app_strings;
	global $mod_strings;
	global $currentModule;
	global $current_user;
	global $sugar_version, $sugar_config;
	

	$the_form = get_left_form_header($mod_strings['LBL_NEW_FORM_TITLE']);
	$form = new XTemplate ('modules/ProjectTask/Forms.html');

	$module_select = empty($_REQUEST['module_select']) ? ''
		: $_REQUEST['module_select'];
	$form->assign('mod', $mod_strings);
	$form->assign('app', $app_strings);
	$form->assign('module', $currentModule);

	$options = get_select_options_with_id(get_user_array(), $current_user->id);
	$form->assign('ASSIGNED_USER_OPTIONS', $options);

	///////////////////////////////////////
	///
	/// SETUP ACCOUNT POPUP
	
	$popup_request_data = array(
		'call_back_function' => 'set_return',
		'form_name' => "quick_save",
		'field_to_name_array' => array(
			'id' => 'parent_id',
			'name' => 'project_name',
			),
		);
	
	$json = getJSONobj();
	$encoded_popup_request_data = $json->encode($popup_request_data);
	
	//
	///////////////////////////////////////
	
	$form->assign('encoded_popup_request_data', $encoded_popup_request_data);


	$form->parse('main');
	$the_form .= $form->text('main');

   require_once('modules/ProjectTask/ProjectTask.php');
   $focus = new ProjectTask();

   require_once('include/javascript/javascript.php');
   $javascript = new javascript();
   $javascript->setFormName('quick_save');
   $javascript->setSugarBean($focus);
   $javascript->addRequiredFields('');
   $jscript = $javascript->getScript();

   $the_form .= $jscript . get_left_form_footer();
	return $the_form;
}
*/
/**
 * Create javascript to validate the data entered into a record.
 */
function get_validate_record_js () {
	return '';
}

?>