<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
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
 *
 ********************************************************************************/

require_once('service/core/REST/SugarRest.php');

/**
 * This class is a serialize implementation of REST protocol
 *
 */
class SugarRestRSS extends SugarRest{
	
	/**
	 * It will serialize the input object and echo's it
	 * 
	 * @param array $input - assoc array of input values: key = param name, value = param type
	 * @return String - echos serialize string of $input
	 */
	function generateResponse($input){
		$method = !empty($_REQUEST['method'])? $_REQUEST['method']: '';
		if($method != 'get_entry_list')$this->fault('RSS currently only supports the get_entry_list method');
		ob_clean();
		$this->generateResponseHeader($input['result_count']);
		$this->generateItems($input);
		$this->generateResponseFooter();
	} // fn
	
	function generateResponseHeader($count){
		$date = gmdate("D, d M Y H:i:s") . " GMT";
echo'<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
<title>SugarCRM  RSS Feed</title>
<link>http://cnn.com</link>
<description>' . $count. ' records found</description>
<pubDate>' . $date . '</pubDate>
<generator>SugarCRM</generator>
<ttl>' . $count . '</ttl>
';
	}
	
function generateItems($input){
	if(!empty($input['entry_list'])){
		foreach($input['entry_list'] as $item){
			$this->generateItem($item);
		}
		
	}
}

function generateItem($item){
echo "<item>\n";
$name  = !empty($item['name_value_list']['name'])?htmlentities( $item['name_value_list']['name']): '';
echo "<title>$name</title>\n";
echo "<link>". $GLOBALS['sugar_config']['site_url']  . htmlentities('/index.php?module=' . $item['module_name']. '&record=' . $item['id']) .  "</link>\n";
echo "<description><![CDATA[";
$displayFieldNames = true;
if(count($item['name_value_list']) == 2 &&isset($item['name_value_list']['name']))$displayFieldNames = false;
foreach($item['name_value_list'] as $k=>$v){
	if($k =='name')continue;
	if($k == 'date_modified')continue;
	if($displayFieldNames) echo '<b>' .htmlentities( $k) . ':<b>&nbsp;';
	echo htmlentities( $v) . "\n<br>";
}
echo "]]></description>\n";
if(!empty($item['name_value_list']['date_modified'])){
	$date = date("D, d M Y H:i:s", strtotime($item['name_value_list']['date_modified'])) . " GMT";
	echo "<pubDate>$date</pubDate>";
}

echo "<guid>" . $item['id']. "</guid>\n";
echo "</item>\n";
}
function generateResponseFooter(){
		echo'</channel></rss>';
	}
	
	/**
	 * This method calls functions on the implementation class and returns the output or Fault object in case of error to client
	 *
	 * @return unknown
	 */
	function serve(){
		$this->fault('RSS is not a valid input_type');
	} // fn
	
	function fault($faultObject){
		ob_clean();
		$this->generateResponseHeader();
		echo '<item><name>';
		if(is_object($errorObject)){
			$error = $errorObject->number . ': ' . $errorObject->name . '<br>' . $errorObject->description;
			$GLOBALS['log']->error($error);
		}else{
			$GLOBALS['log']->error(var_export($errorObject, true));
			$error = var_export($errorObject, true);
		} // else
		echo $error;
		echo '</name></item>';
		$this->generateResponseFooter();
		
	}
	
	
} // clazz