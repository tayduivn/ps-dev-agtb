<?php
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

///////////////////////////////////////////////////////////////////////////////
////	HELPER FUNCTIONS
function json_retrieve() {
	global $beanFiles,$beanList;
	require_once($beanFiles[$beanList[$_REQUEST['module']]]);

	$json = getJSONobj();
	
	$focus = new $beanList[$_REQUEST['module']];
	$focus->retrieve($_REQUEST['record']);

	$all_fields = array_merge($focus->column_fields,$focus->additional_column_fields);

	$obj = array();
	$ret = array();

	foreach($all_fields as $field) {
		if(isset($focus->$field)) {
			$obj[$field] = $focus->$field;
		}
	}

	// cn: bug 12274 - defend against CSRF
	$ret['fields'] = $obj;
	print $json->encode($ret, true);
}

function json_get_full_list() {
	global $beanFiles;
	global $beanList;

	require_once('include/utils.php');
	require_once($beanFiles[$beanList[$_REQUEST['module']]]);

	$json = getJSONobj();

	$where = str_replace('\\','', rawurldecode($_REQUEST['where']));
	$order = str_replace('\\','', rawurldecode($_REQUEST['order']));
	$focus = new $beanList[$_REQUEST['module']];
	$fullList = $focus->get_full_list($order, $where, '');
	$all_fields = array_merge($focus->column_fields,$focus->additional_column_fields);

	$js_fields_arr = array();
	
	$i=1; // js doesn't like 0 index?
	foreach($fullList as $note) {
		$js_fields_arr[$i] = array();
		
		foreach($all_fields as $field) {
			if(isset($note->$field)) {
				$note->$field = from_html($note->$field);
				$note->$field = preg_replace('/\r\n/','<BR>',$note->$field);
				$note->$field = preg_replace('/\n/','<BR>',$note->$field);
				$js_fields_arr[$i][$field] = addslashes($note->$field);
			}
		}
		$i++;
	}
	
	$out = $json->encode($js_fields_arr, true);
	print($out);
}
////	END HELPER FUNCTIONS
///////////////////////////////////////////////////////////////////////////////

// called from another file
$GLOBALS['log'] = LoggerManager::getLogger('json.php');

$supported_functions = array('retrieve','get_full_list');
if(in_array($_REQUEST['action'],$supported_functions)) {
	call_user_func('json_'.$_REQUEST['action']);
}

?>
