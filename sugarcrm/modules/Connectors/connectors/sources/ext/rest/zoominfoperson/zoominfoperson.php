<?php
//FILE SUGARCRM flav=pro
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
require_once('include/connectors/sources/ext/rest/rest.php');
class ext_rest_zoominfoperson extends ext_rest {

	var $xml_parser;
	var $entry;
	var $currentTag;
	var $results;
	var $new_record;
	var $process_record;
 	var $recordTag;
 	var $idTag;
 	var $industrySet;
 	var $skipTags = array();
 	var $inSkipTag = false;
 	var $inAffiliation = false;
 	var $email;
 	var $operation;

 	private $properties;
 	private $partnerCode;
 	private $clientKey;

 	public function __construct(){
 		parent::__construct();
 		$this->_has_testing_enabled = true;
 		$this->_required_config_fields = array('person_search_url', 'person_detail_url', 'api_key', 'partner_code');
 		$this->_required_config_fields_for_button = array('person_search_url', 'person_detail_url');
		$this->properties = $this->getProperties();
		//BEGIN ENCODE
        $this->clientKey = !empty($this->properties['api_key']) ? $this->properties['api_key'] : base64_decode( get_zoominfoperson_api_key());
        $this->partnerCode = !empty($this->properties['partner_code']) ? $this->properties['partner_code'] : base64_decode( get_zoominfoperson_partner_code());
 		//END ENCODE
 	}

 	public function getList($args=array(), $module=null){
 		$this->operation = 'getList';
 		$this->email = !empty($args['EmailAddress']) ? $args['EmailAddress'] : '';
 		$this->recordTag = "PERSONRECORD";
 		$this->idTag = "PERSONID";
        $url = $this->properties['person_search_url'] . $this->partnerCode;
        $this->results = array();
        $argValues = '';
        if($args) {
           foreach($args as $searchKey=>$value) {
           	   if($searchKey != 'companyName' && !empty($value)) {
           	   	   $val =  urlencode($value);
           	   	   $argValues .= substr($val, 0, 2);
	           	   $url .= "&{$searchKey}=" . $val;
           	   }
           }
        } else {
           return $this->results;
        }

        //BEGIN ENCODE
        $queryKey = md5($argValues . $this->clientKey . date("jnY", mktime()));
        //END ENCODE
        $url .= "&key={$queryKey}";

        $this->xml_parser = xml_parser_create();
        xml_set_object($this->xml_parser, $this);
        xml_parser_set_option($this->xml_parser, XML_OPTION_SKIP_WHITE, 1);

		xml_set_element_handler($this->xml_parser, "startReadListData", "endReadListData");
		xml_set_character_data_handler($this->xml_parser, "characterData");

		$fp = @fopen($url, "r");
		if(!empty($fp)) {
			while ($data = fread($fp, 4096)) {
			   xml_parse($this->xml_parser, $data, feof($fp))
			       // Handle errors in parsing
			       or die(sprintf("XML error: %s at line %d",
			           xml_error_string(xml_get_error_code($this->xml_parser)),
			           xml_get_current_line_number($this->xml_parser)));
			}
			fclose($fp);

			$account_name = !empty($args['companyName']) ? $args['companyName'] : '';
			if(!empty($account_name)) {
			   $filtered_results = array();
			   foreach($this->results as $result) {
			   	       if(!empty($result['companyname']) && stripos($result['companyname'], $account_name) !== false) {
			   	       	  $filtered_results[] = $result;
			   	       }
			   }
			   return $filtered_results;
			}
		} else {
			require_once('include/connectors/utils/ConnectorUtils.php');
			$language_strings = ConnectorUtils::getConnectorStrings('ext_rest_zoominfocompany');
			$errorCode = $language_strings['ERROR_LBL_CONNECTION_PROBLEM'];
	 	    $errorMessage = string_format($GLOBALS['app_strings']['ERROR_UNABLE_TO_RETRIEVE_DATA'], array(get_class($this), $errorCode));
	        $GLOBALS['log']->error($errorMessage);
	 		throw new Exception($errorMessage);
		}

		xml_parser_free($this->xml_parser);
		return $this->results;
 	}

  	public function getItem($args=array(), $module = null) {
  		$this->operation = 'getItem';
  		$this->results = array();
  		$this->skipTags = array("WEBREFERENCE", "SUMMARYSTATISTICS", "PASTEMPLOYMENT");
        $this->recordTag = "PERSONDETAILREQUEST";
        $this->idTag = "PERSONID";

        if(empty($args['id'])) {
           return null;
        }

        $url = $this->properties['person_detail_url'] . $this->partnerCode . "&PersonID=" . $args['id'];
        //BEGIN ENCODE
        $queryKey = md5(substr($args['id'],0,2) . $this->clientKey . date("jnY", mktime()));
        //END ENCODE
        $url .= "&key={$queryKey}";

        $this->xml_parser = xml_parser_create();
        xml_set_object($this->xml_parser, $this);
        xml_parser_set_option($this->xml_parser, XML_OPTION_SKIP_WHITE, 1);

		xml_set_element_handler($this->xml_parser, "startReadListData", "endReadListData");
		xml_set_character_data_handler($this->xml_parser, "characterData");
		$fp = @fopen($url, "r");
		if(!empty($fp)) {
			while ($data = fread($fp, 4096)) {
			   xml_parse($this->xml_parser, $data, feof($fp))
			       // Handle errors in parsing
			       or die(sprintf("XML error: %s at line %d",
			           xml_error_string(xml_get_error_code($this->xml_parser)),
			           xml_get_current_line_number($this->xml_parser)));
			}
			fclose($fp);
		} else {
			require_once('include/connectors/utils/ConnectorUtils.php');
			$language_strings = ConnectorUtils::getConnectorStrings('ext_rest_zoominfocompany');
			$errorCode = $language_strings['ERROR_LBL_CONNECTION_PROBLEM'];
	 	    $errorMessage = string_format($GLOBALS['app_strings']['ERROR_UNABLE_TO_RETRIEVE_DATA'], array(get_class($this), $errorCode));
	        $GLOBALS['log']->error($errorMessage);
	 		throw new Exception($errorMessage);
		}

		xml_parser_free($this->xml_parser);
		return !empty($this->results) ? $this->results[0] : null;
  	}

	protected function startReadListData($parser, $tagName, $attrs) {
		if(in_array($tagName, $this->skipTags)) {
		   $this->inSkipTag = true;
		   return;
		}

		$this->currentTag = $tagName;
		if($tagName == $this->recordTag) {
		   $this->entry = array();
		   if($this->operation == 'getList') {
		   	 $this->skipTags = array();
		   } else if($this->operation == 'getItem') {
		   	 $this->skipTags = array("WEBREFERENCE", "SUMMARYSTATISTICS", "PASTEMPLOYMENT");
		   }
		}

		if($this->currentTag == 'AFFILIATION' && $this->operation == 'getItem') {
		   $this->inAffiliation = true;
		}
	}

	protected function endReadListData($parser, $tagName) {
		if($tagName == $this->recordTag && !$this->inSkipTag && !empty($this->entry)) {
			$this->entry['id'] = $this->entry[strtolower($this->idTag)];
			$this->results[] = $this->entry;
		} else if($tagName == 'CURRENTEMPLOYMENT' && !empty($this->entry['companyname'])) {
		   $this->skipTags[] = 'CURRENTEMPLOYMENT';
		} else if($tagName == 'EDUCATION' && !empty($this->entry['school'])) {
		   $this->skipTags[] = 'EDUCATION';
		} else if($tagName == 'AFFILIATION' && !empty($this->entry['affiliation_title'])) {
		   $this->skipTags[] = 'AFFILIATION';
		   $this->inAffiliation = false;
		}

		if(in_array($tagName, $this->skipTags)) {
		   $this->inSkipTag = false;
		}
	}

	protected function characterData($parser, $data) {
		if(!$this->inSkipTag) {
		   if($this->currentTag == 'IMAGEURL') {
			 if(stripos($data, 'http') > 0) {
			   	$data = substr($data, stripos($data, 'http'));
			 }
		   } else if($this->currentTag == 'INDUSTRY' && !empty($this->entry['industry'])) {
		   	 return;
		   } else if($this->inAffiliation) {
			    switch($this->currentTag) {
	                case "JOBTITLE":
	                    $this->entry['affiliation_title'] = $data;
	                    break;
	                case "COMPANYNAME":
	                    $this->entry['affiliation_company_name'] = $data;
	                    break;
	                case "WEBSITE":
	                    $this->entry['affiliation_company_website'] = $data;
	                    break;
	                case "PHONE":
	                    $this->entry['affiliation_company_phone'] = $data;
	                    break;
	            }
		   } else {
		     $this->entry[strtolower($this->currentTag)] = $data;
		   }
		}
	}

	public function test() {
		try {
	    	$listArgs = array('firstName'=>'John');
	    	$results = $this->getList($listArgs, 'Leads');
            return empty($results) ? false : true;
		} catch (Exception $ex) {
			return false;
		}
	}

}


//BEGIN ENCODE
 function get_zoominfoperson_api_key() {
 	return 'emloZWwyMG45';
 }

 function get_zoominfoperson_partner_code() {
 	return 'U3VnYXJjcm0=';
 }
//END ENCODE
?>
