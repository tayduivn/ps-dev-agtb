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








require_once('modules/EditCustomFields/EditCustomFields.php');

$module_name = empty($_REQUEST['module_name']) ? '' :
	$_REQUEST['module_name'];

$search_form = new XTemplate('modules/EditCustomFields/SearchForm.html');

function get_customizable_modules()
{
	$customizable_modules = array();
	$base_path = 'modules';
	$blocked_modules = array('iFrames', 'Dropdown', 'Feeds');
	$customizable_files = array('EditView.html', 'DetailView.html', 'ListView.html');

	$mod_dir = dir($base_path);

	while(false !== ($mod_dir_entry = $mod_dir->read()))
	{
		if($mod_dir_entry != '.'
			&& $mod_dir_entry != '..'
			&& !in_array($mod_dir_entry, $blocked_modules)
			&& is_dir($base_path . '/' . $mod_dir_entry))
		{
			$mod_sub_dir = dir($base_path . '/' . $mod_dir_entry);
			$add_to_array = false;

			while(false !== ($mod_sub_dir_entry = $mod_sub_dir->read()))
			{
				if(in_array($mod_sub_dir_entry, $customizable_files))
				{
					$add_to_array = true;
					break;
				}
			}

			if($add_to_array)
			{
				$customizable_modules[$mod_dir_entry] = $mod_dir_entry;
			}
		}
	}

	ksort($customizable_modules);
	return $customizable_modules;
}

$customizable_modules = get_customizable_modules();
$module_options_html = get_select_options_with_id($customizable_modules,
	$module_name);

global $current_language;
$mod_strings = return_module_language($current_language,
	'EditCustomFields');
global $app_strings;

// the title label and arrow pointing to the module search form
$header = get_form_header($mod_strings['LBL_SEARCH_FORM_TITLE'], '', false);
$search_form->assign('header', $header);
$search_form->assign('module_options', $module_options_html);
$search_form->assign('mod', $mod_strings);
$search_form->assign('app', $app_strings);

$search_form->parse('main');
$search_form->out('main');

if(!empty($module_name))
{
	require_once('modules/DynamicFields/DynamicField.php');
	$seed_fields_meta_data = new FieldsMetaData();
	$where_clause = "custom_module='$module_name'";
	$listview = new ListView();
	$listview->initNewXTemplate('modules/EditCustomFields/ListView.html', $mod_strings);
	$listview->setHeaderTitle($module_name . ' ' . $mod_strings['LBL_MODULE']);
	$listview->setQuery($where_clause, '', 'data_type', 'FIELDS_META_DATA');
	$listview->xTemplateAssign('DELETE_INLINE_PNG',
		SugarThemeRegistry::current()->getImage("delete_inline", 'align="absmiddle" alt="'
		. $app_strings['LNK_DELETE'] . '" border="0"'));
	$listview->xTemplateAssign('EDIT_INLINE_PNG',
		SugarThemeRegistry::current()->getImage("edit_inline", 'align="absmiddle" alt="'
		. $app_strings['LNK_EDIT'] . '" border="0"'));
	$listview->xTemplateAssign('return_module_name', $module_name);
	$listview->processListView($seed_fields_meta_data,  'main', 'FIELDS_META_DATA');
}

?>
