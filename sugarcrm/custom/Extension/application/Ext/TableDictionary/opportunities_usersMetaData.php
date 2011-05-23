<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$dictionary['opportunities_users'] = array (
	'table' => 'opportunities_users',

	'fields' => array (
	       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
	      , array('name' =>'opportunity_id', 'type' =>'varchar', 'len'=>'36')
	      , array('name' =>'user_id', 'type' =>'varchar', 'len'=>'36')
	      , array ('name' => 'date_modified','type' => 'datetime')
	      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true),
	),

	'indices' => array (
	       array('name' =>'opportunities_userspk', 'type' =>'primary', 'fields'=>array('id'))
	      , array('name' =>'idx_opp_usr_acc', 'type' =>'index', 'fields'=>array('opportunity_id'))
	      , array('name' =>'idx_opp_usr_usr', 'type' =>'index', 'fields'=>array('user_id'))
	      , array('name' => 'idx_opportunity_usr', 'type'=>'alternate_key', 'fields'=>array('opportunity_id','user_id')),

	),

	'relationships' => array (
		'opportunities_users' => array(
			'lhs_module'=> 'Opportunities', 'lhs_table'=> 'opportunities', 'lhs_key' => 'id',
                              'rhs_module'=> 'Users', 'rhs_table'=> 'users', 'rhs_key' => 'id',
                              'relationship_type'=>'many-to-many',
                              'join_table'=> 'opportunities_users', 'join_key_lhs'=>'opportunity_id', 'join_key_rhs'=>'user_id'
			)
		)
);
