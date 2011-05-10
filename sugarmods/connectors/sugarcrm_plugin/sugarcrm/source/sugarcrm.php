<?php
require_once('include/connectors/sources/ext/rest/rest.php');
class ext_rest_sugarcrm extends ext_rest {
 		
	var $proxy_object;
	
 	public function __construct(){
 		parent::__construct();
		$this->_enable_in_hover = false;
		$this->_required_config_fields = array('proxy_url', 'proxy_user');
		$json = getJSONobj();
		$properties = $this->getProperties();
	 	$proxy_user = $properties['proxy_user'];
	 	$user_password = $properties['proxy_user_password'];
 		$proxy_url = $properties['proxy_url'];
 		$max_results = !empty($properties['max_results']) ? $properties['max_results'] : 20;
	 	
	 	//First get md5 of password
	 	$url = $proxy_url . "&method=md5&json_data=" . $json->encode($user_password);
	 	$md5 = $json->decode($this->fetchURL($url));
	 	
	 	if(empty($md5)) {
	 	   $GLOBALS['log']->fatal("Empty md5 value in SugarCRM Connector constructor");
	 	   return;
	 	}
	 	
	 	//Now Login
		$user_data[] = array('password'=>$md5, 'user_name'=>$proxy_user);			 	
	 	$url = $proxy_url . "&method=login&json_data=" . urlencode($json->encode($user_data));
	 	$this->proxy_object = $json->decode($this->fetchURL($url));		 		
 	}

 	public function getList($args=array(), $module=null) {
 		$result = array();
		$json = getJSONobj();
		$properties = $this->getProperties();
 		$proxy_url = $properties['proxy_url'];
 		$max_results = $properties['max_results'];

	 	$lead_fields = array('id','do_not_call', 'first_name', 'last_name', 'status', 'phone_work', 'lead_source', 'salutation', 'primary_address_country', 'primary_address_city','primary_address_state', 'primary_address_postalcode', 'department', 'title', 'account_name');	 	
	 	$json_data = array();
	 	$json_data['session'] = $this->proxy_object['id'];
	 	$json_data['module_name'] = 'Leads';
	 	$json_data['query'] = " leads.first_name like '{$args['firstName']}%' and leads.last_name like '{$args['lastName']}%' ";
	 	$json_data['order_by'] = '';
	 	$json_data['offset'] = 0;
	 	$json_data['select_fields'] = $lead_fields;
	 	$json_data['link_name_to_fields_array'] = array('name'=>'email_addresses', 'value'=>array('id', 'email_address', 'opt_out', 'primary_address'));
	 	$json_data['max_results'] = $max_results;
	 	$json_data['deleted'] = 0;
	 	$url = $proxy_url . "&method=get_entry_list&json_data=" . urlencode($json->encode($json_data));
	 	$results = $json->decode($this->fetchURL($url));
	 	$data = array();
        if(!empty($results['entry_list'])) {
           foreach($results['entry_list'] as $entry_id=>$entry) {
           	       $data[] = $entry['name_value_list'];
           }
        }
        return $data;
 	}

  	public function getItem($args=array(), $module = null) {
 		if(empty($args['id'])) {
 		   $GLOBALS['log']->error("Error: Missing id argument in SugarCRM getItem constructor");
 		   return null;
 		}
 		
		$json = getJSONobj();
		$properties = $this->getProperties();
 		$proxy_url = $properties['proxy_url'];

	 	$lead_fields = array('id', 'do_not_call', 'first_name', 'last_name', 'status', 'phone_work', 'lead_source', 'salutation', 'primary_address_country', 'primary_address_city','primary_address_state', 'primary_address_postalcode', 'department', 'title', 'account_name');	 	
	 	$json_data = array();
	 	$json_data['session'] = $this->proxy_object['id'];
	 	$json_data['module_name'] = 'Leads';
	 	$json_data['query'] = " leads.id = '{$args['id']}' ";
	 	$json_data['order_by'] = '';
	 	$json_data['offset'] = 0;
	 	$json_data['select_fields'] = $lead_fields;
	 	$json_data['link_name_to_fields_array'] = array('name'=>'email_addresses', 'value'=>array('id', 'email_address', 'opt_out', 'primary_address'));
	 	$json_data['max_results'] = 1;
	 	$json_data['deleted'] = 0;
	 	$url = $proxy_url . "&method=get_entry_list&json_data=" . urlencode($json->encode($json_data));
	 	$results = $json->decode($this->fetchURL($url));
        if(!empty($results['entry_list'][0]['name_value_list'])) {
           return $results['entry_list'][0]['name_value_list'];
        }
        return null;	  
  	}
	
 	public function __destruct(){
		parent::__destruct();
	}	
}
?>