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

/*
ARGS:
 $_REQUEST['method']; : options: 'SaveRelationship','Save','DeleteRelationship','Delete'
 $_REQUEST['module']; : the module associated with this Bean instance (will be used to get the class name)
 $_REQUEST['record']; : the id of the Bean instance
// $_REQUEST['related_field']; : the field name on the Bean instance that contains the relationship
// $_REQUEST['related_record']; : the id of the related record
// $_REQUEST['related_']; : the
// $_REQUEST['return_url']; : the URL to redirect to
//$_REQUEST['return_type']; : when set the results of a report will be linked with the parent.
*/


require_once('include/formbase.php');

$refreshsubpanel=true;
if (isset($_REQUEST['return_type'])  && $_REQUEST['return_type'] == 'report') {
	save_from_report($_REQUEST['subpanel_id'] //report_id
					 ,$_REQUEST['record'] //parent_id
					 ,$_REQUEST['module'] //module_name
					 ,$_REQUEST['subpanel_field_name'] //link attribute name
	);
} else if (isset($_REQUEST['return_type'])  && $_REQUEST['return_type'] == 'addtoprospectlist') {

	$GLOBALS['log']->debug(print_r($_REQUEST,true));
	if(!empty($_REQUEST['prospect_list_id']) and !empty($_REQUEST['prospect_ids']))
	{
	    add_prospects_to_prospect_list(
	        $_REQUEST['prospect_list_id'],
	        $_REQUEST['prospect_ids']
	    );
	}
	else
	{
	    $parent = BeanFactory::getBean($_REQUEST['module'], $_REQUEST['record']);
	    add_to_prospect_list(urldecode($_REQUEST['subpanel_module_name']),$_REQUEST['parent_module'],$_REQUEST['parent_type'],$_REQUEST['subpanel_id'],
	        $_REQUEST['child_id'],$_REQUEST['link_attribute'],$_REQUEST['link_type'], $parent);
	}

	$refreshsubpanel=false;
}else if (isset($_REQUEST['return_type'])  && $_REQUEST['return_type'] == 'addcampaignlog') {
    //if param is set to "addcampaignlog", then we need to create a campaign log entry
    //for each campaign id passed in.

    // Get a list of campaigns selected.
    if (isset($_REQUEST['subpanel_id'])  && !empty($_REQUEST['subpanel_id'])) {
        $campaign_ids = $_REQUEST['subpanel_id'];
        $focus = BeanFactory::getBean($_REQUEST['module'], $_REQUEST['record']);
        require_once('modules/Campaigns/utils.php');
        //call util function to create the campaign log entry
        foreach($campaign_ids as $id){
            create_campaign_log_entry($id, $focus, $focus->module_dir,$focus, $focus->id);
        }
        $refreshsubpanel=true;
    }
}
else {
    $focus = BeanFactory::getBean($_REQUEST['module'], $_REQUEST['record']);

 	// If the user selected "All records" from the selection menu, we pull up the list
 	// based on the query they used on that popup to relate them to the parent record
 	if(!empty($_REQUEST['select_entire_list']) &&  $_REQUEST['select_entire_list'] != 'undefined' && isset($_REQUEST['current_query_by_page'])){
		$order_by = '';
		$current_query_by_page = $_REQUEST['current_query_by_page'];
 		$current_query_by_page_array = unserialize(base64_decode($current_query_by_page));

        $module = $current_query_by_page_array['module'];
        $seed = BeanFactory::getBean($module);
        if(empty($seed)) sugar_die($GLOBALS['app_strings']['ERROR_NO_BEAN']);
 		$where_clauses = '';
 		require_once('include/SearchForm/SearchForm2.php');

 		$searchdefs_file = SugarAutoLoader::loadWithMetafiles($module, 'searchdefs');
 		if($searchdefs_file) {
 			require $searchdefs_file;
 		}

 		$searchFields = SugarAutoLoader::loadSearchFields($module);

        if(!empty($searchdefs) && !empty($searchFields)) {
        	$searchForm = new SearchForm($seed, $module);
	        $searchForm->setup($searchdefs, $searchFields, 'SearchFormGeneric.tpl');
	        $searchForm->populateFromArray($current_query_by_page_array, 'advanced');
	        $where_clauses_arr = $searchForm->generateSearchWhere(true, $module);
	        if (count($where_clauses_arr) > 0 ) {
	            $where_clauses = '('. implode(' ) AND ( ', $where_clauses_arr) . ')';
	        }
        }
        
        $query = $seed->create_new_list_query($order_by, $where_clauses);
		$result = $GLOBALS['db']->query($query,true);
		$uids = array();
		while($val = $GLOBALS['db']->fetchByAssoc($result,false))
		{
			array_push($uids, $val['id']);
		}
		$_REQUEST['subpanel_id'] = $uids;
 	}

 	if($bean_name == 'Team'){
 		$subpanel_id = $_REQUEST['subpanel_id'];
 		if(is_array($subpanel_id)){
 			foreach($subpanel_id as $id){
 				$focus->add_user_to_team($id);
 			}
 		}
 		else{
 			$focus->add_user_to_team($subpanel_id);
 		}
 	} else{
 		//find request paramters with with prefix of REL_ATTRIBUTE_
 		//convert them into an array of name value pairs add pass them as
 		//parameters to the add metod.
 		$add_values =array();
 		foreach ($_REQUEST as $key=>$value) {
 			if (strpos($key,"REL_ATTRIBUTE_") !== false) {
 				$add_values[substr($key,14)]=$value;
 			}
 		}
 		$focus->load_relationship($_REQUEST['subpanel_field_name']);
 		$focus->$_REQUEST['subpanel_field_name']->add($_REQUEST['subpanel_id'],$add_values);
        $focus->save();
 	}
}

if ($refreshsubpanel) {
	//refresh contents of the sub-panel.
	$GLOBALS['log']->debug("Location: index.php?sugar_body_only=1&module=".$_REQUEST['module']."&subpanel=".$_REQUEST['subpanel_module_name']."&action=SubPanelViewer&inline=1&record=".$_REQUEST['record']);
	if( empty($_REQUEST['refresh_page']) || $_REQUEST['refresh_page'] != 1){
		$inline = isset($_REQUEST['inline'])?$_REQUEST['inline']: $inline;
		header("Location: index.php?sugar_body_only=1&module=".$_REQUEST['module']."&subpanel=".$_REQUEST['subpanel_module_name']."&action=SubPanelViewer&inline=$inline&record=".$_REQUEST['record']);
	}
	exit;
}
