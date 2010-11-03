<?php
$dictionary['cases_kbdocuments'] =
	array ( 'table' => 'cases_kbdocuments',
		'fields' => array (
       		array('name' =>'id', 'type' =>'varchar', 'len'=>'36'),
       		array('name' =>'case_id', 'type' =>'varchar', 'len'=>'36'),
       		array('name' =>'kbdocument_id', 'type' =>'varchar', 'len'=>'36'),
       		array ('name' => 'date_modified','type' => 'datetime'),
       		array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
		),
		'indices' => array (
       		array('name' =>'cases_kbdocumentspk', 'type' =>'primary', 'fields'=>array('id')),
       		array('name' =>'idx_cas_doc_cas', 'type' =>'index', 'fields'=>array('case_id')),
       		array('name' =>'idx_cas_doc_doc', 'type' =>'index', 'fields'=>array('kbdocument_id')),
       		array('name' => 'idx_case_doc', 'type'=>'alternate_key', 'fields'=>array('case_id','kbdocument_id'))
		),
		'relationships' => array (
			'cases_kbdocuments' => array(
				'lhs_module'=> 'Cases',
				'lhs_table'=> 'cases',
				'lhs_key' => 'id',
				'rhs_module'=> 'KBDocuments',
				'rhs_table'=> 'kbdocuments',
				'rhs_key' => 'id',
				'relationship_type'=>'many-to-many',
				'join_table'=> 'cases_kbdocuments',
				'join_key_lhs'=>'case_id',
				'join_key_rhs'=>'kbdocument_id'
			),
		),
	);
?>