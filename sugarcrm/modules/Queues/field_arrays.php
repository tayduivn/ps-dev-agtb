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
//FILE SUGARCRM flav=int ONLY
$fields_array['Queues'] = array (
	'column_fields' => array (
		'id',
		'deleted',
		'date_entered',
		'date_modified',
		'modified_user_id',
		'created_by',
		'name',
		'status',
		'owner_id',
		'queue_type',
		'workflows',
		'persistent_memory',
	),
	'list_fields' => array (
		'id',
		'name_id',
		'owner_id',
		'parent_id',
		'queue_type',
		'status',
		'queuedItems',
		'distribution',
	),
	'required_fields' => array (
		'owner_id' => 1,
		'queue_type' => 1
	),
);
?>
