<?php
/*
 @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 15044 :: add "user" reference to bugs
** Description: Add user relationship between Bugs and User
*/

$dictionary['bugs_users'] = array ( 'table' => 'bugs_users'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'bug_id', 'type' =>'varchar', 'len'=>'36')
      , array('name' =>'user_id', 'type' =>'varchar', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'default'=>'0', 'required'=>true)
                                                      )                                  , 'indices' => array (
       array('name' =>'bugs_userspk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'idx_bug_usr_bug', 'type' =>'index', 'fields'=>array('bug_id'))
      , array('name' =>'idx_bug_usr_usr', 'type' =>'index', 'fields'=>array('user_id'))
      , array('name' => 'idx_bug_usr', 'type'=>'alternate_key', 'fields'=>array('bug_id','user_id'))            
      
                                                      )
 	  , 'relationships' => array ('bugs_users' => array('lhs_module'=> 'Bugs', 'lhs_table'=> 'bugs', 'lhs_key' => 'id',
							  'rhs_module'=> 'Users', 'rhs_table'=> 'users', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'bugs_users', 'join_key_lhs'=>'bug_id', 'join_key_rhs'=>'user_id'))
                                                      
                                  );

?>
