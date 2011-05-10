<?php
require_once('include/connectors/sources/ext/rest/rest.php');
class ext_rest_crunchbase extends ext_rest {
	public function __construct(){
		parent::__construct();
		$this->_has_testing_enabled = true;
		$this->_enable_in_hover = true;
		$this->_required_config_fields = array('company_url');
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
 	public function getList($args=array(), $module=null){
 		$result = array();
 		if(!empty($args['name'])) {
	 		$company = $args['name'];
	 		$company = trim(strtolower($company));
	 		
	 		//Squeeze spaces out of string just in case
	 		if(preg_match('/[\s]+/', $company)) {
	 		   $company = preg_replace('/[\s]+/', '-', $company);
	 		}
	 		
	 		$properties = $this->getProperties();
	 		$data = $this->fetchData($properties['company_url'] . "{$company}.js");
	 		
	        if(!empty($data)) {
	           $data['id'] = $company;
	           $result = array();
	           $result[] = $data;
	           return $result;
	        } 
	        
	        //One last ditch effort to check again in case company name had a space to
	        //just use the first part of the string (i.e. "Oracle Corporation" becomes "oracle"
	        if(strpos($company, '-')) {
	           $company = substr($company, 0, strpos($company, '-'));
               $data = $this->fetchData($properties['company_url'] . "{$company}.js");
               if(!empty($data)) {
	 	           $result = array();
	 	           $data['id'] = $company;
		           $result[] = $data;
		           return $result;              	
               }
	        }
 		}
 		
 		$GLOBALS['log']->error("No argument ['name'] supplied in crunchbase->getList");
        return $result;		
 	}
 	
 	public function getItem($args=array(), $module=null) {
 		$args['name'] = $args['id'];
 		$data = $this->getList($args, $module);
 		return $data[0];
 	}	
 	
 	public function test() {
    	$listArgs = array('id'=>'SugarCRM');
    	$item = $this->getItem($listArgs, 'Leads');
        return $item['name'] == 'SugarCRM';    		
 	}
 	
 	private function fetchData($url) {
	 	$result = $this->fetchURL($url);
	 	$json_obj = getJSONobj();
	    $data = $json_obj->decode($result);
	    return $data;	
 	}
}
 
?>