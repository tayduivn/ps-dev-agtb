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
/*********************************************************************************

 * Description:  
 ********************************************************************************/

require_once('modules/QueryBuilder/QueryGroupBy.php');
require_once('modules/QueryBuilder/QueryColumn.php');
global $mod_strings;



$focus = new QueryGroupBy();

if(!isset($_REQUEST['groupby_record']))
	sugar_die($mod_strings['ERR_DELETE_RECORD']);

$focus->retrieve($_REQUEST['groupby_record']);

if($focus->groupby_axis=="Columns"){
//This is a column group by, so delete the column as well

	$column_object = new QueryColumn();
	$column_object->retrieve($focus->parent_id);
	$column_object->clear_deleted();
}	

$focus->clear_deleted();

header("Location: index.php?module=".$_REQUEST['return_module']."&action=".$_REQUEST['return_action']."&record=".$_REQUEST['return_id']);
?>
