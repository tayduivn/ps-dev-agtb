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

$dictionary['RecordList'] = array(
    'table' => 'record_list',
	'fields' => array(
		'id' => array(
			'name'	=> 'id',
			'type'	=> 'id',
			'required'	=> true,
			'reportable' => false,
		),
		'assigned_user_id' => array(
			'name' => 'assigned_user_id',
			'vname' => 'LBL_USER_ID',
			'type' => 'id',
			'required' => true,
			'reportable' => false,
		),
		'module_name' => array(
			'name' => 'module_name',
			'vname' => 'LBL_MODULE',
			'type' => 'varchar',
			'len' => '50',
			'required' => true,
			'reportable' => false,
		),
		'records' => array(
			'name' => 'records',
			'vname' => 'LBL_RECORD_LIST',
			'type' => 'text',
			'required' => true,
			'reportable' => false,
		),
        'date_modified' => array(
            'name' => 'date_modified',
            'vname' => 'LBL_DATE_MODIFIED',
            'type' => 'datetime',
        ),
	),
	'indices' => array(
		array(
            'name' => 'record_list_id',
			'type' => 'primary',
			'fields' => array(
				'id',
            )
        ),
	), /* end indices */
);
