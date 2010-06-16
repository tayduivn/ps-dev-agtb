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

$dictionary['AddressBook'] = array ('table' => 'address_book',
	'fields' => array (
		'assigned_user_id' => array (
			'name' => 'assigned_user_id',
			'vname' => 'LBL_USER_ID',
			'type' => 'id',
			'required' => true,
			'reportable' => false,
		),
		'bean' => array (
			'name' => 'bean',
			'vname' => 'LBL_BEAN',
			'type' => 'varchar',
			'len' => '50',
			'required' => true,
			'reportable' => false,
		),
		'bean_id' => array (
			'name' => 'bean_id',
			'vname' => 'LBL_BEAN_ID',
			'type' => 'id',
			'required' => true,
			'reportable' => false,
		),
	),
	'indices' => array (
		array(
			'name' => 'ab_user_bean_idx',
			'type' =>'index',
			'fields' => array(
				'assigned_user_id',
				'bean',
			)
		),
	), /* end indices */
);

//BEGIN SUGARCRM flav=pro ONLY
$dictionary['AddressBookMailingList'] = array ('table' => 'address_book_lists',
	'fields' => array (
		'id' => array(
			'name'	=> 'id',
			'type'	=> 'id',
			'required'	=> true,
			'reportable' => false,
		),
		'assigned_user_id' => array (
			'name' => 'assigned_user_id',
			'vname' => 'LBL_USER_ID',
			'type' => 'id',
			'required' => true,
			'reportable' => false,
		),
		'list_name' => array(
			'name'	=> 'list_name',
			'vname'	=> 'LBL_MAILING_LIST',
			'type'	=> 'varchar',
			'len'	=> 100,
			'required'	=> true,
			'reportable'	=> false,
		),
	),
	'indices' => array (
		array(
			'name'	=> 'abl_pk',
			'type'	=> 'primary',
			'fields'	=> array(
				'id',
			),
		),
		array(
			'name' => 'abml_user_bean_idx',
			'type' =>'index',
			'fields' => array(
				'assigned_user_id',
			)
		),
	), /* end indices */
);

$dictionary['AddressBookMailingListItems'] = array ('table' => 'address_book_list_items',
	'fields' => array (
		'list_id' => array(
			'name'	=> 'list_id',
			'type'	=> 'id',
			'required'	=> true,
			'reportable' => false,
		),
		'bean_id' => array(
			'name'	=> 'bean_id',
			'type'	=> 'id',
			'required'	=> true,
			'reportable' => false,
		),
	),
	'indices' => array (
		array(
			'name' => 'abli_list_id_idx',
			'type' =>'index',
			'fields' => array(
				'list_id',
			)
		),
		array(
			'name' => 'abli_list_id_bean_idx',
			'type' =>'index',
			'fields' => array(
				'list_id',
				'bean_id',
			)
		),
	), /* end indices */
);
//END SUGARCRM flav=pro ONLY
