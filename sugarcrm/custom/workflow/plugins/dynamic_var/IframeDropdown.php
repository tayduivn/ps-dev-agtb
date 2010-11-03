<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point'); 
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
global $app_list_strings;
global $beanList;
global $theme;

require_once('include/workflow/workflow_utils.php');

	
	if(!empty($_REQUEST['target_module']) && $_REQUEST['target_module']!=""){
		$target_module = $_REQUEST['target_module'];
	} else {
		sugar_die("Target_module required");
	}

	if(!empty($_REQUEST['field_type']) && $_REQUEST['field_type']!=""){
		$field_type = $_REQUEST['field_type'];
	} else {
		$field_type = "";	
	}	

	if(!empty($_REQUEST['start_value']) && $_REQUEST['start_value']!=""){
		$start_value = $_REQUEST['start_value'];
	} else {
		$start_value = "";	
	}	
	
	if(!empty($_REQUEST['base_module']) && $_REQUEST['base_module']!=""){
		$base_module = $_REQUEST['base_module'];
	} else {
		$base_module = "";	
	}	

	
//	echo "field_type".$field_type."<BR>";
//	echo "target_module".$target_module."<BR>";
//	echo "base_module".$base_module."<BR>";
//

//based on field type and also the difference in base vs. target

	
	if($field_type=='enum'){
		
		//use the dyn_var_enum vardef_handler filter
		$vardef_filter = 'dynamic_var_enum';
		$start_value = $start_value;
		$process_jscript = false;
		
	} else {
		
		//use the dyn_var_text vardef_handler_filter
		$vardef_filter = 'template_filter';	
		$start_value = '';
		$process_jscript = true;
	}		

	if($target_module!=$base_module){

		$temp_module = get_module_info($base_module);
		$rel_attribute_name = $temp_module->field_defs[$target_module]['relationship'];
		$rel_module = get_rel_module_name($base_module, $rel_attribute_name, $temp_module->db);
		
		$temp_module = get_module_info($rel_module);
		$temp_module->call_vardef_handler($vardef_filter);
		$target_dropdown = get_select_options_with_id($temp_module->vardef_handler->get_vardef_array(true),$start_value);
		if($process_jscript == true){
			$select_jscript = "onchange=\"window.parent.copy_text_dyn_var()";
			$on_start ="window.parent.copy_text_dyn_var();";
		} else {
			$select_jscript = '';
			$on_start = '';	
		}	
	//end if rel needed
	} else {

		$temp_module = get_module_info($target_module);
		$temp_module->call_vardef_handler($vardef_filter);
		$target_dropdown = get_select_options_with_id($temp_module->vardef_handler->get_vardef_array(true),$start_value);		
		
		
		//$ext_value = $target_module;
		if($process_jscript == true){	
			$select_jscript = "onchange=\"window.parent.copy_text_dyn_var()";
			$on_start ="window.parent.copy_text_dyn_var();";
		} else {
			$select_jscript = '';
			$on_start = '';	
		}
	//end if else use rel or not
	}	



////////////HTML DISPLAY AREA////////////////////	
	echo "<html><head>";
	echo "<style type=\"text/css\">@import url(\"themes/$theme/style.css?s=" . $sugar_version . '&c=' . $sugar_config['js_custom_version'] . "\");";
	echo "</style><style type='text/css'> body {background-color: transparent}</style></head><body>";
	echo "<form name=\"EditView\">";
	echo "<select id='target_dropdown' name='target_dropdown' tabindex='2' ".$select_jscript."\">".$target_dropdown."</select>";
	echo "</form>";	
	echo "<script>";
 	echo $on_start;
	echo "</script>";
	echo "</body></html>";

	
?>
