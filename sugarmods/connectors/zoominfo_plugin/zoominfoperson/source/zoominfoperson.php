<?php
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
 	var $skipTags = array();
 	var $inSkipTag = false;		
	
 	public function __construct(){
 		parent::__construct();
 		$this->_has_testing_enabled = true;
 		$this->_required_config_fields = array('api_key');
 	}

 	public function getList($args=array(), $module=null){
 		$this->recordTag = "PERSONRECORD";
 		$this->idTag = "PERSONID";  
        $properties = $this->getProperties();
        $url = $properties['url'] . $properties['api_key'];        
        $this->results = array();
 	   // $args = $this->mapInput($args, $module);
        if($args) {
           foreach($args as $searchKey=>$value) {
           	   if($searchKey != 'companyName' && !empty($value)) {
	           	   $url .= "&{$searchKey}=" . urlencode($value);
           	   }
           }
        } else {
           return $this->results;
        }

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
			$language_strings = ConnectorUtils::getConnectorStrings('ext_rest_zoominfoperson');
			$GLOBALS['log']->fatal($language_strings['ERROR_LBL_CONNECTION_PROBLEM']);
		}

		xml_parser_free($this->xml_parser);

		return $this->results;    
 	}

  	public function getItem($args=array(), $module = null) {
  		$this->results = array();
        $this->recordTag = "PERSONRECORD";
        $this->idTag = "PERSONID";
        
        $args = preg_split('/\|/', $args['id']);
        if(count($args) != 3) {
           return null;
        }

        $properties = $this->getProperties();
        $url = $properties['url'] . $properties['api_key'] . "&firstName=" . $args[0] . "&lastName=" . $args[1];        
        $this->xml_parser = xml_parser_create(); 
        xml_set_object($this->xml_parser, $this);
        xml_parser_set_option($this->xml_parser, XML_OPTION_SKIP_WHITE, 1);

		xml_set_element_handler($this->xml_parser, "startReadListData", "endReadListData");
		xml_set_character_data_handler($this->xml_parser, "characterData");

		$result = '';
		$fp = @fopen($url, "r");
		if(!empty($fp)) {		
			while ($data = fread($fp, 4096)) {
			   $result .= $data;
			   xml_parse($this->xml_parser, $data, feof($fp))
			       // Handle errors in parsing
			       or die(sprintf("XML error: %s at line %d",  
			           xml_error_string(xml_get_error_code($this->xml_parser)),  
			           xml_get_current_line_number($this->xml_parser)));
			}
			fclose($fp);
		} else {
			require_once('include/connectors/utils/ConnectorUtils.php');
			$language_strings = ConnectorUtils::getConnectorStrings('ext_rest_zoominfoperson');
			$GLOBALS['log']->fatal($language_strings['ERROR_LBL_CONNECTION_PROBLEM']);
		}

		xml_parser_free($this->xml_parser);		
		
		if(!empty($this->results)) {
		   foreach($this->results as $result) {
		   	  if($result['personid'] == $args[2]) {
		   	  	 return $result;
		   	  }
		   }
		} 
		return null;
		
		  
  	} 	

	protected function startReadListData($parser, $tagName, $attrs) {
		if(in_array($tagName, $this->skipTags)) {
		   $this->inSkipTag = true;
		   return;
		}
		
		$this->currentTag = $tagName;
		if($tagName == $this->recordTag) {
		   $this->entry = array();
		}
	}
	
	protected function endReadListData($parser, $tagName) {
		if($tagName == $this->recordTag) {
			$this->entry['id'] = htmlentities($this->entry['firstname'] . "|" . $this->entry['lastname'] . "|" . $this->entry[strtolower($this->idTag)]);
			$this->results[] = $this->entry;
		}
	}

	protected function characterData($parser, $data) {
		if(!$this->inSkipTag) {
		   $this->entry[strtolower($this->currentTag)] = $data;
		}
	} 	 	
	
	public function test() {
    	$listArgs = array('id'=>'John|Roberts|7438435');
    	$item = $this->getItem($listArgs, 'Leads');
        return $item['companyname'] == 'SugarCRM Inc.';					
	}
	
 	public function __destruct(){
		parent::__destruct();
	}	
}
?>
