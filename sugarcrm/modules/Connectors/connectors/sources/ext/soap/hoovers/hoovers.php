<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/
 define('HOOVERS_LOOKUP_MAPPING_FILE', 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers/lookup_mapping.php');
 require_once('include/connectors/sources/ext/soap/soap.php');
 class ext_soap_hoovers extends ext_soap {
 	
 	//Private soapHeader instance
 	private $_soapHeader;
 	private $_lookupMap = array();
 	
 	public function __construct()
 	{
 		parent::__construct();
 		$this->_has_testing_enabled = true;
 		$this->_required_config_fields = array('hoovers_endpoint', 'hoovers_wsdl', 'hoovers_api_key');
		$this->_required_config_fields_for_button = array('hoovers_endpoint', 'hoovers_wsdl');
 	}

  	public function __destruct()
  	{
		parent::__destruct();
	} 	
 	
 	public function init() 
 	{
 		parent::init();
 		try{
	 		$properties = $this->getProperties();
	 		//BEGIN ENCODE
	 		$clientKey = !empty($properties['hoovers_api_key']) ? $properties['hoovers_api_key'] : base64_decode(get_hoovers_api_key());
            //END ENCODE
            	 		
	 		/*
	 		$this->_client = new nusoapclient($properties['hoovers_endpoint'], true);
            $this->_client->setHeaders("<API-KEY xmlns='http://webservice.hoovers.com'>{$clientKey}</API-KEY>");
            */
	 		if (!class_exists('SoapClient') || !class_exists('SoapHeader') ) {
	 			require_once('include/connectors/utils/ConnectorUtils.php');
				$connector_language = ConnectorUtils::getConnectorStrings('ext_soap_hoovers');
	 		    throw new Exception($connector_language['ERROR_MISSING_SOAP_LIBRARIES']);
	 		} 
	 		
	 		$this->_client = new SoapClient($properties['hoovers_wsdl'],
                array('trace' => true, 'exceptions' => true, 'location' => $properties['hoovers_endpoint'])
            );
            $headers[] = new SoapHeader('http://webservice.hoovers.com', 'API-KEY', $clientKey);
            $this->_client->__setSoapHeaders($headers);
            
            
 		}catch(Exception $ex){
 		 	$GLOBALS['log']->error($ex);
			return;
		}
		
 	 	if($this->_client == null)
 		{
 		   $errorMessage = $GLOBALS['mod_strings']['ERROR_NULL_CLIENT'];
	 	   throw new Exception($errorMessage);	
 		}		
		
		if(!file_exists(HOOVERS_LOOKUP_MAPPING_FILE) || ((mktime() - filemtime(HOOVERS_LOOKUP_MAPPING_FILE)) > 2592000)) {
	 		try {
	 		  //$result = $this->_client->call('GetAdvancedSearchLookups', array('parameters'=>array()), $namespace='http://webservice.hoovers.com');
	 		  $result = $this->_client->__soapCall('GetAdvancedSearchLookups', array('parameters'=>array()), NULL);
	 		  
	 		  if(empty($result)) {
	 		  	 return;
	 		  }
	 		  
			  $mapping = $this->obj2array($result);
			  $countries = array();
			  $states = array();

			  if(!empty($mapping['return']['countries']['country'])) {		  
				  foreach($mapping['return']['countries']['country'] as $country) {
				  	 $countries[strtoupper($country['name'])] = $country['id'];
				  }
			  }
			  
			  if(!empty($mapping['return']['states']['stateName'])) {
				  foreach($mapping['return']['states']['stateName'] as $state) {
				  	 $states[strtoupper($state['name'])] = $state['state'];
				  }
			  }
			  
			  $mapping = array();
			  $mapping['countries'] = $countries;
			  $mapping['states'] = $states;
			  
	 	      if(!file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers')) {
	       		  
	 	      	 mkdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
	    	  }			  
			  
		      if(!write_array_to_file('lookup_mapping', $mapping, HOOVERS_LOOKUP_MAPPING_FILE)) {
		         $GLOBALS['log']->fatal("Cannot write file " . HOOVERS_LOOKUP_MAPPING_FILE);
		      }				  
			  
	 		} catch(Exception $ex) {
 		 	   $GLOBALS['log']->error($ex);
	 		}
 	    }	

 	    require(HOOVERS_LOOKUP_MAPPING_FILE);
 	    $this->_lookupMap = $lookup_mapping; 		
 	}
 	
 	/**
 	 * getList
 	 * This is the Hoovers implementation of the getList method
 	 * 
 	 * @param $args Array of input/search parameters
 	 * @param $module String value of the module we are mapping input arguments from
 	 * @return $result Array of results based on the search results from the given arguments
 	 */
 	public function getList($args=array(), $module=null) {
 		//Call the soap method (AdvancedCompanySearch)
 		//$args['bal']['orderBy'] = 'IndustryName';
		$args['bal']['sortDirection'] = 'Ascending';
		
		//If a location field is specified, use the ALL argument to ensure search matches all
		//location arguments
		if(!empty($args['bal']['location'])) {
		   if(!empty($args['bal']['location']['state'])) {
		   	  $args['bal']['location']['state'] = $this->getLookupValue('states', $args['bal']['location']['state']);
		   }
		   
		   if(!empty($args['bal']['location']['country'])) {
		   	  $args['bal']['location']['country'] = $this->getLookupValue('countries', $args['bal']['location']['country']);
		   }
		   
		   $args['bal']['location']['allAny'] = 'all';
		}
		
		
		//Do some conversions for API - change the & and ' characters
		if(!empty($args['bal']['specialtyCriteria']['companyName'])) {
		   $search = array(" & ", "&#039;");
		   $replace = array(" and ", "");
		   $args['bal']['specialtyCriteria']['companyName'] = str_ireplace($search, $replace, $args['bal']['specialtyCriteria']['companyName']);
        }
        
 		$result = $this->AdvancedCompanySearch($args);
 		//Return results after parsing using parseListResults
		return !empty($result) ? $this->parseListResults($result) : array();
 	}
 	
 	
 	/**
 	 * getItem
 	 * This is the Hoovers implementation of the getItem method
 	 * 
 	 * @param $args Array of input/search parameters
 	 * @param $module String value of the module we are mapping input arguments from
 	 * @return $result Array of result based on the search results from the given arguments
 	 */
 	public function getItem($args=array(), $module=null) { 		
 		$result = $this->GetCompanyDetail($args);

 		if(empty($result) || empty($result['return'])) {
 		   return array();
 		}
 		
 		$result = $result['return'];
 		if(isset($result['keyNumbers'][0])) {
 		   $result['keyNumbers'] = $result['keyNumbers'][0];	
 		}
 		
 		$lookup_mapping = array();
 		
 		if(file_exists(HOOVERS_LOOKUP_MAPPING_FILE)) {
 		   require(HOOVERS_LOOKUP_MAPPING_FILE);
 		}
 		
 		$countries = array_flip($lookup_mapping['countries']);
 		$states = array_flip($lookup_mapping['states']);
 		
 	    $data = array();
        $data['id'] = $args['uniqueId'];
        $data['companyname'] = $result['name'];
        $data['duns'] = $args['uniqueId'];
        $data['parent_duns'] = $result['ultimateParentDuns'];
        $data['address1'] = !empty($result['locations']['location']['address1']) ? $result['locations']['location']['address1'] : '';
        $data['address2'] = !empty($result['locations']['location']['address2']) ? $result['locations']['location']['address2'] : '';
        $data['city'] = !empty($result['locations']['location']['city']) ? $result['locations']['location']['city'] : '';
        $data['stateorprovince'] = !empty($result['locations']['location']['state']) ? $result['locations']['location']['state'] : '';
        
        
        if(!empty($data['addrstateprov']) && isset($states[$data['addrstateprov']])) {
           $data['stateorprovince'] = $states[$data['addrstateprov']];
        }
        
        $data['addrcountry'] = !empty($result['locations']['location']['country']) ? $result['locations']['location']['country'] : '';
        if(!empty($data['addrcountry']) && isset($countries[$data['addrcountry']])) {
           $data['country'] = $countries[$data['addrcountry']];
        }
        
        $data['addrzip'] = !empty($result['locations']['location']['zip']) ? $result['locations']['location']['zip'] : '';
        
        if(!empty($result['locations']['location']['zip4']))
        {
           $data['addrzip'] .= '-' . $result['locations']['location']['zip4'];
        }
            
        $data['hqphone'] = '';
 	    if(!empty($result['phones']) && is_array($result['phones'])) {
            foreach($result['phones'] as $phoneEntry) {
		        if(!empty($phoneEntry['countryCode'])) {
		           $data['hqphone'] = $phoneEntry['countryCode'];	
		        }
		        
		        if(!empty($phoneEntry['areaCode'])) {
		           $data['hqphone'] .= "({$phoneEntry['areaCode']})";
		        }
		        
		 	    if(!empty($phoneEntry['phoneNumber'])) {
		           $data['hqphone'] .= "{$phoneEntry['phoneNumber']}";
		        }
		        break;
            }
        } else if(!empty($result['phones'])) {
        		if(!empty($result['phones']['countryCode'])) {
		           $data['hqphone'] = $result['phones']['countryCode'];	
		        }
		        
		        if(!empty($result['phones']['areaCode'])) {
		           $data['hqphone'] .= "({$result['phones']['areaCode']})";
		        }
		        
		 	    if(!empty($result['phones']['phoneNumber'])) {
		           $data['hqphone'] .= "{$result['phones']['phoneNumber']}";
		        }        	
        }

        if(!empty($result['keyNumbersHistory']['annualKeyNumbersHistory']['keyNumbers']))
        {
        	$keyNumbers = $result['keyNumbersHistory']['annualKeyNumbersHistory']['keyNumbers'];
        	if(isset($keyNumbers[0]))
        	{
        	   foreach($keyNumbers as $keyFinancialData)
        	   {
        	   		if(!empty($keyFinancialData['sales'])) 
        	   		{
        	   			$data['sales'] = $keyFinancialData['sales'];
        	   			break;
        	   		}
        	   }
        	} else {
	        	$keyNumbers = $result['keyNumbersHistory']['annualKeyNumbersHistory']['keyNumbers'];
	        	$data['sales'] = !empty($keyNumbers['sales']) ? $keyNumbers['sales'] : '';        
	        	$data['employees'] = !empty($keyNumbers['sales']['employeesTotal']) ? $keyNumbers['employeesTotal'] : '';
	        }
        } 		
        
        if(!empty($result['synopsis']))
        {
        	$data['synopsis'] = $result['synopsis'];
        }

 	    if(!empty($result['full-description']))
        {
        	$data['description'] = $result['full-description'];
        }        
       
        return $data;
 	}
 	
 	
 	/**
 	 * __call
 	 * 
 	 * 
 	 */
  	public function __call($function,  $args) {
 		$result = array();
 		if(empty($args) || !is_array($args) || empty($args[0])) {
 		   return $result;
 		}
 		
  		if (empty($this->_client) || !class_exists('SoapClient') || !class_exists('SoapHeader') ) {
 			require_once('include/connectors/utils/ConnectorUtils.php');
			$connector_language = ConnectorUtils::getConnectorStrings('ext_soap_hoovers');
 		    throw new Exception($connector_language['ERROR_MISSING_SOAP_LIBRARIES']);
	 	} 		
 		
 		try {
 			//$result = $this->_client->call($function, array('parameters'=>$args[0]), $namespace='http://webservice.hoovers.com');
	 		$result = $this->_client->__soapCall($function, array('parameters'=>$args[0]), NULL);
	
 			//if(!is_array($result) && !preg_match('/^HTTP\/\d\.?\d?\s+200\s+OK/', $this->_client->response)) {
	 		if(!is_array($result) && !preg_match('/^HTTP\/\d\.?\d?\s+200\s+OK/', $this->_client->__getLastResponse())) {
 			
	 		   $errorCode = 'Unknown';
	 		   //if(preg_match('/\<h1\>([^\<]+?)\<\/h1\>/', $this->_client->response, $matches)) {
	 		   if(preg_match('/\<h1\>([^\<]+?)\<\/h1\>/', $this->_client->__getLastResponse(), $matches)) {
	 		   
	 		   	  $errorCode = $matches[1];
	 		   }
	 	       $errorMessage = string_format($GLOBALS['app_strings']['ERROR_UNABLE_TO_RETRIEVE_DATA'], array(get_class($this), $errorCode));
	 		   throw new Exception($errorMessage); 		
	 		}
 		} catch (Exception $ex) {
 		 	$GLOBALS['log']->error($ex);
  		}
 		return $this->obj2array($result);
 	}
 	
 	
 	/**
 	 * parseListResults
 	 * Internal private method to handle distinguishing the Hoovers SOAP call results.
 	 * There are subtle differences when one company result is returned versus multiple
 	 * company results.
 	 * 
 	 * @param $result Array of results in list format
 	 * @return $result Formatted results 
 	 */
 	private function parseListResults($result){
 		if($result['return']['companies']['hits'] == 1) {
 		   $single = array();
 		   $data = $result['return']['companies']['hit']['companyResults'];
 		   $id = $data['duns'];
 		   $data['id'] = $id;
 		   $single[$id] = $data;
 		   return $single;
 		} else if($result['return']['companies']['hits'] > 1) {
 		   $multiple = array();
 		   
 		   foreach($result['return']['companies']['hit'] as $result) {
 		   	  $data = $result['companyResults'];
			  $id = $data['duns'];
 		   	  $data['id'] = $id;
 		   	  $multiple[$id] = $data;
 		   }
 		   return $multiple;
 		} else {
 		   return '';
 		}
 	}
	
	/**
	 * test
	 * This method is called from the administration components to make a live test
	 * call to see if the configuration and connections are available
	 * 
	 * @return boolean result of the test call false if failed, true otherwise
	 */
  	public function test() {
	    $item = $this->getItem(array('uniqueId' => '2205698'), 'Leads');
	    return !empty($item['companyname']) && (preg_match('/^Gannett/i', $item['companyname'])); 
	}
	
	
	/**
	 * getLookupValue
	 * This method returns the lookup value used by Hoovers based on the mapping file
	 * of the search parameters created
	 * @param String $category String value of the category (countries, states)
	 * @param String $value String value of the value to lookup
	 * @return String $lookupValue String value that should be used for lookup
	 */
	private function getLookupValue($category='', $value='') {
	   if(empty($category) || empty($value) || empty($this->_lookupMap)) {
	   	  return $value;
	   }
       
       return !empty($this->_lookupMap[$category][strtoupper($value)]) ? $this->_lookupMap[$category][strtoupper($value)] : $value;
	}	
}
 
//BEGIN ENCODE
function get_hoovers_api_key() {
 	//BEGIN SUGARCRM flav!=int ONLY
 	return 'cGF4Yjk0OHRkeGV2YWVmM2NlZzdrMnBj';
 	//END SUGARCRM flav!=int ONLY	
	
	//BEGIN SUGARCRM flav=int ONLY
 	return 'bm12c3U1YnRmcnJqNjZ0M2F5cnNjbWVl'; 	
	//END SUGARCRM flav=int ONLY 
}
//END ENCODE
 
?>
