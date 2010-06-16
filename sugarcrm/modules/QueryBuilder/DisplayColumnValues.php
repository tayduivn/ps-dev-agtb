<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point'); 
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
global $app_list_strings;
global $beanList;

require_once('modules/QueryBuilder/QueryBuilder.php');

//echo "MODULE NAME:".$_REQUEST['col_module_name'];
$dropdown_module = $_REQUEST['column_module'];
if(!empty($_REQUEST['column_name'])){
	$column_name = $_REQUEST['column_name'];	
} else {
	$column_name = "";
}	

$seed_object = new QueryBuilder();
$column_option_list = $seed_object->get_column_select($dropdown_module);  

$column_select = get_select_options_with_id($column_option_list, $column_name);

echo "<form name=\"dropdownview\">";

echo "<select id='column_name' name='column_name' tabindex='2'>".$column_select."</select>";

echo "</form>";



?>
