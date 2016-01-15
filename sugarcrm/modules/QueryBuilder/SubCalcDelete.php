<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once('modules/QueryBuilder/QueryFilter.php');
global $mod_strings;



$focus = new QueryFilter();

if(!isset($_REQUEST['filter_id']))
	sugar_die($mod_strings['ERR_DELETE_RECORD']);

$focus->clear_deleted($_REQUEST['filter_id']);

header("Location: index.php?module=".$_REQUEST['return_module']."&action=".$_REQUEST['return_action']."&column_record=".$_REQUEST['column_record']."&record=".$_REQUEST['query_id']."&component=Column&to_pdf=true");
?>