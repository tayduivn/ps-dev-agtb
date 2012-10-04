<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
/*********************************************************************************
 * $Id: Delete.php,v 1.22 2006/01/17 22:50:52 majed Exp $
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 *********************************************************************************/

$dictionary['folders'] = array(
	'table' => 'folders',
	'fields' => array(
		array(
			'name'			=> 'id',
			'type'			=> 'id',
			'required'		=> true,
		),
		array(
			'name'			=> 'name',
			'type'			=> 'varchar',
			'len'			=> 25,
			'required'		=> true,
		),
		array(
			'name'			=> 'folder_type',
			'type'			=> 'varchar',
			'len'			=> 25,
			'default'		=> NULL,
		),
		array(
			'name'			=> 'parent_folder',
			'type'			=> 'id',
			'required'		=> false,
		),
		array(
			'name'			=> 'has_child',
			'type'			=> 'bool',
			'default'		=> '0',
		),
		array(
			'name'			=> 'is_group',
			'type'			=> 'bool',
			'default'		=> '0',
		),
		array(
			'name'			=> 'is_dynamic',
			'type'			=> 'bool',
			'default'		=> '0',
		),
		array(
			'name'			=> 'dynamic_query',
			'type'			=> 'text',
		),
		array(
			'name'			=> 'assign_to_id',
			'type'			=> 'id',
			'required'		=> false,
		),
		//BEGIN SUGARCRM flav=pro ONLY
		array(
			'name'			=> 'team_id',
			'type'			=> 'id',
			'required'		=> false,
		),
		array(
			'name'			=> 'team_set_id',
			'type'			=> 'id',
			'required'		=> false,
		),
		//END SUGARCRM flav=pro ONLY

		array(
			'name'			=> 'created_by',
			'type'			=> 'id',
			'required'		=> true,
		),
		array(
			'name'			=> 'modified_by',
			'type'			=> 'id',
			'required'		=> true,
		),
		array(
			'name'			=> 'deleted',
			'type'			=> 'bool',
			'default'		=> '0',
		),
	),
	'indices' => array(
		array(
			'name'			=> 'folderspk',
			'type'			=> 'primary',
			'fields'		=> array('id')
		),
		array(
			'name'			=> 'idx_parent_folder',
			'type'			=> 'index',
			'fields'		=> array('parent_folder')
		),
	),
);

$dictionary['folders_subscriptions'] = array(
	'table' => 'folders_subscriptions',
	'fields' => array(
		array(
			'name'			=> 'id',
			'type'			=> 'id',
			'required'		=> true,
		),
		array(
			'name'			=> 'folder_id',
			'type'			=> 'id',
			'required'		=> true,
		),
		array(
			'name'			=> 'assigned_user_id',
			'type'			=> 'id',
			'required'		=> true,
		),
	),
	'indices' => array(
		array(
			'name'			=> 'folders_subscriptionspk',
			'type'			=> 'primary',
			'fields'		=> array('id')
		),
		array(
			'name'			=> 'idx_folder_id_assigned_user_id',
			'type'			=> 'index',
			'fields'		=> array('folder_id', 'assigned_user_id')
		),
	),
);

$dictionary['folders_rel'] = array(
	'table' => 'folders_rel',
	'fields' => array(
		array(
			'name'			=> 'id',
			'type'			=> 'id',
			'required'		=> true,
		),
		array(
			'name'			=> 'folder_id',
			'type'			=> 'id',
			'required'		=> true,
		),
		array(
			'name'			=> 'polymorphic_module',
			'type'			=> 'varchar',
			'len'			=> 25,
			'required'		=> true,
		),
		array(
			'name'			=> 'polymorphic_id',
			'type'			=> 'id',
			'required'		=> true,
		),
		array(
			'name'			=> 'deleted',
			'type'			=> 'bool',
			'default'		=> '0',
		),
	),
	'indices' => array(
		array(
			'name'			=> 'folders_relpk',
			'type'			=> 'primary',
			'fields'		=> array('id'),
		),
		array(
			'name'			=> 'idx_poly_module_poly_id',
			'type'			=> 'index',
			'fields'		=> array('polymorphic_module', 'polymorphic_id'),
		),
		array(
		    'name'			=> 'idx_fr_id_deleted_poly',
		    'type'			=> 'index',
		    'fields'		=> array('folder_id','deleted','polymorphic_id'),
		),
	),
);