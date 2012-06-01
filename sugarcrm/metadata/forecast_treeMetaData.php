<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/

$dictionary['forecast_tree'] = array(
	'table' => 'forecast_tree',
	'fields' => array(
		array(
			'name'			=> 'id',
			'type'			=> 'id',
			'required'		=> true,
		),
		array(
			'name'			=> 'name',
			'type'			=> 'varchar',
			'len'			=> 50,
			'required'		=> true,
		),
		array(
			'name'			=> 'hierarchy_type',
			'type'			=> 'varchar',
			'len'			=> 25,
			'required'      => true,
		),
		array(
			'name'			=> 'user_id',
			'type'			=> 'id',
            'default'       => NULL,
			'required'		=> false,
		),
        array(
      	    'name'			=> 'parent_id',
      		'type'			=> 'id',
            'default'       => NULL,
      		'required'		=> false,
      		),
	),
	'indices' => array(
		array(
			'name'			=> 'forecast_tree_pk',
			'type'			=> 'primary',
			'fields'		=> array('id')
		),
		array(
			'name'			=> 'forecast_tree_idx_user_id',
			'type'			=> 'index',
			'fields'		=> array('user_id')
		),
	),
);
