<?php
require_once('include/connectors/sources/ext/rest/rest.php');
class ext_rest_test extends ext_rest {

public function __construct(){
   parent::__construct();
   $this->_has_testing_enabled = true;
   $this->_enable_in_hover = true;
}

public function test() {
   $item = $this->getItem(array('id'=>'1'));
   return !empty($item['firstname']) && ($item['firstname'] == 'John');  		
}
	
public function getList($args=array(), $module=null) {
	$results = array();
	
	if(!empty($args['name']['last']) && strtolower($args['name']['last']) == 'doe') {
	   $results[1] = array('id'=>1, 'firstname'=>'John', 'lastname'=>'Doe', 'website'=>'www.johndoe.com');
	   $results[2] = array('id'=>1, 'firstname'=>'Jane', 'lastname'=>'Doe', 'website'=>'www.janedoe.com');
	}
	
	return $results;
}		
	

public function getItem($args=array(), $module=null) {
  $result = null;
  if($args['id'] == 1) {
     $result = array();
     $result['id'] = '1'; //Unique record identifier
     $result['firstname'] = 'John';
     $result['lastname'] = 'Doe';
     $result['website'] = 'http://www.johndoe.com';
  } else if($args['id'] == 2) {
     $result = array();
     $result['id'] = '2'; //Unique record identifier
     $result['firstname'] = 'Jane';
     $result['lastname'] = 'Doe';
     $result['website'] = 'http://www.janedoe.com';
  }
  return $result;  
} 	

}
?>