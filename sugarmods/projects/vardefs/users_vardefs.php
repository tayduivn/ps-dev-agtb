<?php
//BEGIN USER VARDEFS 
// adding holiday field
$dictionary['User']['fields']['holidays'] = array(
  	'name' => 'holidays',
  	'type' => 'link',
  	'relationship' => 'users_holidays',
  	'source' => 'non-db',
  	'side' => 'right',
  	'vname' => 'LBL_HOLIDAYS',
  );

//END USER VARDEFS
?>