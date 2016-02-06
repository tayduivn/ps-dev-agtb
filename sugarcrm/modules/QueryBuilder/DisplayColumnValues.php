<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
global $app_list_strings;
global $beanList;


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
