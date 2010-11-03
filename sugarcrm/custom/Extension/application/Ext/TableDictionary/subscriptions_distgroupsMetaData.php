<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$dictionary['subscriptions_distgroups'] = array (
	'table' => 'subscriptions_distgroups',
	'fields' => array (
		array('name' => 'id', 'type' =>'varchar', 'len'=>'36'),
		array('name' => 'subscription_id', 'type' =>'varchar', 'len'=>'36', ),
		array('name' => 'distgroup_id', 'type' =>'varchar', 'len'=>'36', ),
		array('name' => 'quantity', 'type' =>'int', 'len'=>'20', ),
		array('name' => 'date_modified','type' => 'datetime'),
		array('name' => 'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0','required'=>true),
	),
	'indices' => array (
		array('name' => 'subscriptions_distgroups_pk', 'type' =>'primary', 'fields'=>array('id')),
		array('name' => 'idx_sub_dist_sub', 'type' =>'index', 'fields'=>array('subscription_id')),
		array('name' => 'idx_sub_dist_dist', 'type' =>'index', 'fields'=>array('distgroup_id')),
		array('name' => 'idx_sub_dist', 'type'=>'alternate_key', 'fields'=>array('subscription_id','distgroup_id')),
	),
	'relationships' => array (
		'subscriptions_distgroups' => array(
			'lhs_module' => 'Subscriptions', 'lhs_table'=> 'subscriptions', 'lhs_key' => 'id',
			'rhs_module' => 'DistGroups', 'rhs_table'=> 'distgroups', 'rhs_key' => 'id',
			'relationship_type' => 'many-to-many',
			'join_table' => 'subscriptions_distgroups', 'join_key_lhs'=>'subscription_id', 'join_key_rhs'=>'distgroup_id'
		),
	),
);

?>
