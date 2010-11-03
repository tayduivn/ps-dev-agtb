<?php
/*
 @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 17275
** Description: Add database relationship between Cases and User
*/

$dictionary['cases_users'] = array ( 'table' => 'cases_users'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'case_id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'user_id', 'type' =>'varchar', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
                                                      )                                  , 'indices' => array (
       array('name' =>'cases_users', 'type' =>'primary', 'fields'=>array('id'))          
      
                                                      )
 	  , 'relationships' => array ('cases_users' => array('lhs_module'=> 'cases', 'lhs_table'=> 'cases', 'lhs_key' => 'id',
							  'rhs_module'=> 'Users', 'rhs_table'=> 'users', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'cases_users', 'join_key_lhs'=>'case_id', 'join_key_rhs'=>'user_id'))
                                                      
                                  );

?>
