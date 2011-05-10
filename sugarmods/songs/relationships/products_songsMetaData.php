<?php
$dictionary['products_songs'] = array ( 'table' => 'products_songs'
                                  , 'fields' => array (
       array('name' =>'id', 'type' =>'char', 'len'=>'36', 'required'=>true, 'default'=>'')
      , array('name' =>'product_id', 'type' =>'char', 'len'=>'36')
      , array('name' =>'song_id', 'type' =>'char', 'len'=>'36')
      , array ('name' => 'date_modified','type' => 'datetime')
      , array('name' =>'deleted', 'type' =>'bool', 'len'=>'1', 'required'=>true, 'default'=>'0')
                                                      )                                  , 'indices' => array (
       array('name' =>'products_songspk', 'type' =>'primary', 'fields'=>array('id'))
      , array('name' =>'idx_acc_bug_acc', 'type' =>'index', 'fields'=>array('product_id'))
      , array('name' =>'idx_acc_bug_bug', 'type' =>'index', 'fields'=>array('song_id'))
      , array('name' => 'idx_account_bug', 'type'=>'alternate_key', 'fields'=>array('product_id','song_id'))      
      )
      
 	  , 'relationships' => array ('products_songs' => array('lhs_module'=> 'Products', 'lhs_table'=> 'products', 'lhs_key' => 'id',
							  'rhs_module'=> 'Songs', 'rhs_table'=> 'songs', 'rhs_key' => 'id',
							  'relationship_type'=>'many-to-many',
							  'join_table'=> 'products_songs', 'join_key_lhs'=>'product_id', 'join_key_rhs'=>'song_id'))
)
?>
