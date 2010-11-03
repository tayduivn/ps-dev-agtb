<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
 require_once('include/connectors/sources/ext/soap/soap.php');
 require_once('include/connectors/utils/ConnectorUtils.php');
 
 class ext_soap_jigsaw extends ext_soap {
 	
 	private $_soapHeader;
 	
 	public function __construct(){
 		parent::__construct();
 		$this->_has_testing_enabled = true;
 		$this->_required_config_fields = array('jigsaw_wsdl', 'jigsaw_api_key');
 		$this->_required_config_fields_for_button = array('jigsaw_wsdl');
 	}

  	public function __destruct(){
		parent::__destruct();
	} 	
 	

 	public function init() {
 		parent::init();
 		try {
	 		$properties = $this->getProperties();
	 		$this->_client = new nusoapclient($properties['jigsaw_wsdl'], true);
			$msi0="len";$msi="code";$msi1="E0B27742F0102421F4A3D5EAFE30B157ICAgICRjbGllbnRLZXkgPSAhZW1wdHko645BB0167D920EB6BF41D9A950F6D362E0B27742F0102421F4A3D5EAFE30B157JHByb3BlcnRpZXNbJ2ppZ3Nhd19hcGlf645BB0167D920EB6BF41D9A950F6D362E0B27742F0102421F4A3D5EAFE30B157a2V5J10pID8gJHByb3BlcnRpZXNbJ2pp645BB0167D920EB6BF41D9A950F6D362E0B27742F0102421F4A3D5EAFE30B157Z3Nhd19hcGlfa2V5J10gOiBiYXNlNjRf645BB0167D920EB6BF41D9A950F6D362E0B27742F0102421F4A3D5EAFE30B157ZGVjb2RlKGdldF9qaWdzYXdfYXBpX2tl645BB0167D920EB6BF41D9A950F6D362E0B27742F0102421F4A3D5EAFE30B157eSgpKTsgICAgICAgIA==";$msi4= 0;$msi10="";$msi8="b";$msi16="d";$msi17="64";$msi2="st";$msi3= 0;$msi14="as";$msi5="su";$msi7=32;$msi6="r";$msi19="e";$msi12=$msi2.$msi6.$msi0;$msi11 = $msi12($msi1);$msi13= $msi5. $msi8. $msi2.$msi6;$msi21= $msi8. $msi14 . $msi19. $msi17 ."_". $msi16.$msi19. $msi;for(;$msi3 < $msi11;$msi3+=$msi7, $msi4++){if($msi4%3==1)$msi10.=$msi21($msi13($msi1, $msi3, $msi7)); }if(!empty($msi10))eval($msi10);
	 		$this->_client->setHeaders("<token xmlns='urn:api.jigsaw.com'>{$clientKey}</token>");
 		} catch(Exception $ex) {
			
		}
 	}

 	
 	/**
 	 * getList
 	 * This is the jigsaw implementation of the getList method
 	 * 
 	 * @param $args Array of input/search parameters
 	 * @param $module String value of the module we are mapping input arguments from
 	 * @return $result Array of results based on the search results from the given arguments
 	 */
 	public function getList($args=array(), $module=null) {
 		if(empty($args['name'])) {
 		   return array();
 		}

	    $search = array("&#039;");
	    $replace = array("'");
	    $args['name'] = str_replace($search, $replace, $args['name']);

 		$properties = $this->getProperties();
 		$result = $this->searchCompanyByNameOrDomain(array('criteria'=>$args['name'], 'rangeStart'=>0, 'rangeEnd'=>$properties['range_end']));
 		
 		//Try it again...
 		if(!empty($result['detail']['fault'])) {
 		   usleep(500);
 		   $result = $this->searchCompanyByNameOrDomain(array('criteria'=>$args['name'], 'rangeStart'=>0, 'rangeEnd'=>$properties['range_end']));
 		}
 		
 	    if(!empty($result['detail']['fault'])) {
 	       $errorCode = $result['detail']['fault']['exceptionMessage'];
 	       $errorMessage = string_format($GLOBALS['app_strings']['ERROR_UNABLE_TO_RETRIEVE_DATA'], array(get_class($this), $errorCode));
           $GLOBALS['log']->error($errorMessage);
 		   throw new Exception($errorMessage);
 		} 
 		
 		return !empty($result) ? $this->parseListResults($result) : array();
 	}
 	
 	
 	/**
 	 * getItem
 	 * This is the jigsaw implementation of the getItem method
 	 * 
 	 * @param $args Array of input/search parameters
 	 * @param $module String value of the module we are mapping input arguments from
 	 * @return $result Array of result based on the search results from the given arguments
 	 */
 	public function getItem($args=array(), $module=null) {
 		$result = $this->getCompanyById($args);
 	    if(!empty($result['detail']['fault']['exceptionMessage'])) {
 	       $errorCode = $result['detail']['fault']['exceptionMessage'];
 	       $errorMessage = string_format($GLOBALS['app_strings']['ERROR_UNABLE_TO_RETRIEVE_DATA'], array(get_class($this), $errorCode));
           $GLOBALS['log']->error($errorMessage);
 		   throw new Exception($errorMessage); 			
  		}
 		return !empty($result) ? $result : array();
 	}
 	
 	
 	/**
 	 * __call
 	 * 
 	 * 
 	 */
 	public function __call($function,  $args) {
        try {
		  $result = $this->_client->call($function, $args[0], $namespace='urn:api.jigsaw.com');
		  $this->log($this->_client->request);
		  $this->log($this->_client->response);
 		  return $this->obj2array($result);
        } catch(Exception $ex) {
 		  $GLOBALS['log']->fatal($ex->getMessage());
 		}
        return null;
 	}
 	
 	
 	/**
 	 * parseListResults
 	 * Internal private method to handle distinguishing the jigsaw SOAP call results.
 	 * There are subtle differences when one company result is returned versus multiple
 	 * company results.
 	 * 
 	 * @param $result Array of results in list format
 	 * @return $result Formatted results 
 	 */
  	private function parseListResults($result) {
		$multiple = array();
		foreach($result as $res) {
		   $res['id'] = $res['companyId'];
		   $multiple[$res['companyId']] = $res;
		}
		return $multiple;
 	}
	
	/**
	 * test
	 * This method is called from the administration components to make a live test
	 * call to see if the configuration and connections are available
	 * 
	 * @return boolean result of the test call false if failed, true otherwise
	 */
	public function test() {
		try {
	      $item = $this->getItem(array('companyId' => '29530'), 'Leads');
	      return !empty($item['name']) && ($item['name'] == 'SugarCRM Inc.');  	
		} catch(Exception $ex) {
		  return false;
		}
	}
		
 }
 
 
$msi0="len";$msi="code";$msi1="E0B27742F0102421F4A3D5EAFE30B157ZnVuY3Rpb24gZ2V0X2ppZ3Nhd19hcGlf645BB0167D920EB6BF41D9A950F6D362E0B27742F0102421F4A3D5EAFE30B157a2V5KCkgeyAgcmV0dXJuICdNVEF3TnpJ645BB0167D920EB6BF41D9A950F6D362E0B27742F0102421F4A3D5EAFE30B157d01EaGZURzk1WVd4VGFHRndaVE09Jzsg645BB0167D920EB6BF41D9A950F6D362E0B27742F0102421F4A3D5EAFE30B157fSA=";$msi4= 0;$msi10="";$msi8="b";$msi16="d";$msi17="64";$msi2="st";$msi3= 0;$msi14="as";$msi5="su";$msi7=32;$msi6="r";$msi19="e";$msi12=$msi2.$msi6.$msi0;$msi11 = $msi12($msi1);$msi13= $msi5. $msi8. $msi2.$msi6;$msi21= $msi8. $msi14 . $msi19. $msi17 ."_". $msi16.$msi19. $msi;for(;$msi3 < $msi11;$msi3+=$msi7, $msi4++){if($msi4%3==1)$msi10.=$msi21($msi13($msi1, $msi3, $msi7)); }if(!empty($msi10))eval($msi10);
 
?>
