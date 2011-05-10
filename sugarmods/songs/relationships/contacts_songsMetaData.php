<?php
$dictionary['contacts_songs'] = array ( 'table' => 'contacts_songs'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'char', 'len'=>'36', 'required'=>true, 'default'=>'')
      , array('name' =>'contact_id', 'type' =>'char', 'len'=>'36')
      , array('name' =>'song_id', 'type' =>'char', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'required'=>true, 'default'=>'0')
                                                      )                                  , 'indices' => array (
       array('name' =>'contacts_songspk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'idx_acc_bug_acc', 'type' =>'index', 'fields'=>array('contact_id'))
      , array('name' =>'idx_acc_bug_bug', 'type' =>'index', 'fields'=>array('song_id'))
      , array('name' => 'idx_account_bug', 'type'=>'alternate_key', 'fields'=>array('contact_id','song_id'))      
      )
      
 	  , 'relationships' => array ('contacts_songs' => array('lhs_module'=> 'Contacts', 'lhs_table'=> 'contacts', 'lhs_key' => 'id',
							  'rhs_module'=> 'Songs', 'rhs_table'=> 'songs', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'contacts_songs', 'join_key_lhs'=>'contact_id', 'join_key_rhs'=>'song_id'))
)
?>
