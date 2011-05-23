<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$dictionary['accounts_users'] = array (
	'table' => 'accounts_users',

	'fields' => array (
	       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
	      , array('name' =>'account_id', 'type' =>'varchar', 'len'=>'36')
	      , array('name' =>'user_id', 'type' =>'varchar', 'len'=>'36')
	      , array ('name' => 'date_modified','type' => 'datetime')
	      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true),
	),

	'indices' => array (
	       array('name' =>'accounts_userspk', 'type' =>'primary', 'fields'=>array('id'))
	      , array('name' =>'idx_acc_usr_acc', 'type' =>'index', 'fields'=>array('account_id'))
	      , array('name' =>'idx_acc_usr_usr', 'type' =>'index', 'fields'=>array('user_id'))
	      , array('name' => 'idx_account_usr', 'type'=>'alternate_key', 'fields'=>array('account_id','user_id')),

	),

	'relationships' => array (
		'accounts_users' => array(
			'lhs_module'=> 'Accounts', 'lhs_table'=> 'accounts', 'lhs_key' => 'id',
                              'rhs_module'=> 'Users', 'rhs_table'=> 'users', 'rhs_key' => 'id',
                              'relationship_type'=>'many-to-many',
                              'join_table'=> 'accounts_users', 'join_key_lhs'=>'account_id', 'join_key_rhs'=>'user_id'
			)
		)
);
