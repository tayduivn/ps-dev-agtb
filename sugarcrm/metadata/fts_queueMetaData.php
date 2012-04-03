<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
//FILE SUGARCRM flav=pro ONLY

$dictionary['fts_queue'] = array('table' => 'fts_queue',
	'fields' => array(
		array(
			'name'		=> 'bean_id',
			'dbType'	=> 'id',
			'type'		=> 'varchar',
			'len'		=> '36',
			'comment' 	=> 'FK to various beans\'s tables',
		),
		array(
			'name'		=> 'bean_module',
			'type'		=> 'varchar',
			'len'		=> '100',
			'comment' 	=> 'bean\'s Module',
		),
		array(
			'name'		=> 'date_modified',
			'type'		=>	'datetime'
		),
		array(
			'name'		=> 'processed',
			'type'		=> 'bool',
			'len'		=> '1',
			'default'	=> '0',
			'required'	=> false
		)
	),
	'indices' => array(
		array(
			'name'		=> 'idx_beans_bean_id',
			'type'		=> 'index',
			'fields'	=> array('bean_module','bean_id')
		),
	),
	'relationships' => array()
);