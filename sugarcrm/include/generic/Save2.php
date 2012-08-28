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
/*********************************************************************************
 * $Id: Save2.php 56952 2010-06-14 21:44:26Z sadek $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
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

function add_prospects_to_prospect_list($parent_id,$child_id)
{
    $focus=BeanFactory::getBean('Prospects');
    if(is_array($child_id)){
        $uids = $child_id;
    }
    else{
        $uids = array($child_id);
    }

    $relationship = '';
    foreach($focus->get_linked_fields() as $field => $def) {
        if ($focus->load_relationship($field)) {
            if ( $focus->$field->getRelatedModuleName() == 'ProspectLists' ) {
                $relationship = $field;
                break;
            }
        }
    }

    if ( $relationship != '' ) {
        foreach ( $uids as $id) {
            $focus->retrieve($id);
            $focus->load_relationship($relationship);
            $focus->prospect_lists->add( $parent_id );
        }
    }
}

//Link rows returned by a report to parent record.
function save_from_report($report_id,$parent_id, $module_name, $relationship_attr_name) {
	global $beanFiles;
	global $beanList;

	$GLOBALS['log']->debug("Save2: Linking with report output");
	$GLOBALS['log']->debug("Save2:Report ID=".$report_id);
	$GLOBALS['log']->debug("Save2:Parent ID=".$parent_id);
	$GLOBALS['log']->debug("Save2:Module Name=".$module_name);
	$GLOBALS['log']->debug("Save2:Relationship Attribute Name=".$relationship_attr_name);

 	$bean_name = $beanList[$module_name];
	$GLOBALS['log']->debug("Save2:Bean Name=".$bean_name);
	require_once($beanFiles[$bean_name]);
 	$focus = new $bean_name();

	$focus->retrieve($parent_id);
	$focus->load_relationship($relationship_attr_name);

	//fetch report definition.
global $current_language, $report_modules, $modules_report;

$mod_strings = return_module_language($current_language,"Reports");


	$saved = new SavedReport();
	$saved->disable_row_level_security = true;
	$saved->retrieve($report_id, false);

	//initiailize reports engine with the report definition.
	require_once('modules/Reports/SubpanelFromReports.php');
	$report = new SubpanelFromReports($saved);
	$report->run_query();

	$sql = $report->query_list[0];
	$GLOBALS['log']->debug("Save2:Report Query=".$sql);
	$result = $report->db->query($sql);
	while($row = $report->db->fetchByAssoc($result))
	{
		$focus->$relationship_attr_name->add($row['primaryid']);
	}
}

$refreshsubpanel=true;
if (isset($_REQUEST['return_type'])  && $_REQUEST['return_type'] == 'report') {
	save_from_report($_REQUEST['subpanel_id'] //report_id
					 ,$_REQUEST['record'] //parent_id
					 ,$_REQUEST['module'] //module_name
					 ,$_REQUEST['subpanel_field_name'] //link attribute name
	);
//BEGIN SUGARCRM flav!=sales ONLY
} else if (isset($_REQUEST['return_type'])  && $_REQUEST['return_type'] == 'addtoprospectlist') {

	$GLOBALS['log']->debug(print_r($_REQUEST,true));
    if(!empty($_REQUEST['prospect_list_id']) and !empty($_REQUEST['prospect_list_id'])){
        add_prospects_to_prospect_list(
            $_REQUEST['prospect_list_id'],
            $_REQUEST['prospect_id']
        );
    }
	$refreshsubpanel=false;
}else if (isset($_REQUEST['return_type'])  && $_REQUEST['return_type'] == 'addcampaignlog') {
    //if param is set to "addcampaignlog", then we need to create a campaign log entry
    //for each campaign id passed in.

    // Get a list of campaigns selected.
    if (isset($_REQUEST['subpanel_id'])  && !empty($_REQUEST['subpanel_id'])) {
        $campaign_ids = $_REQUEST['subpanel_id'];
        global $beanFiles;
        global $beanList;
        //retrieve current bean
        $bean_name = $beanList[$_REQUEST['module']];
        require_once($beanFiles[$bean_name]);
        $focus = new $bean_name();
        $focus->retrieve($_REQUEST['record']);

        require_once('modules/Campaigns/utils.php');
        //call util function to create the campaign log entry
        foreach($campaign_ids as $id){
            create_campaign_log_entry($id, $focus, $focus->module_dir,$focus, $focus->id);
        }
        $refreshsubpanel=true;
    }
//END SUGARCRM flav!=sales ONLY
}
//BEGIN SUGARCRM flav=dce ONLY
else if(isset($_REQUEST['return_module']) && isset($_REQUEST['subpanel_module_name']) &&
        $_REQUEST['return_module'] == 'DCEInstances' && $_REQUEST['subpanel_module_name'] == 'Contacts'){
    global $beanFiles,$beanList;
    $bean_name = $beanList[$_REQUEST['module']];
    require_once($beanFiles[$bean_name]);
    $focus = new $bean_name();
    $focus->retrieve($_REQUEST['record']);
    $find=false;
    $query = "SELECT contact_id, contact_role FROM dceinstances_contacts where deleted = 0 AND instance_id='{$_REQUEST['record']}'";
    $rows = array();
    global $db;
    $result = $db->query($query);
    while (($row = $db->fetchByAssoc($result)) != null) {
       if(isset($row["contact_role"]) && $row["contact_role"]=='Primary Decision Maker'){
           $find=true;
       }
    }
    $add_values =array();
    if(!$find){
        $add_values['contact_role']='Primary Decision Maker';
    }
    // Find request parameters with with prefix of REL_ATTRIBUTE_,
    // convert them into an array of name-value pairs and pass them as
    // parameters to the add method.
    foreach ($_REQUEST as $key=>$value) {
        if (strpos($key,"REL_ATTRIBUTE_") !== false) {
            $add_values[substr($key,14)]=$value;
        }
    }
    $focus->load_relationship($_REQUEST['subpanel_field_name']);
    $focus->$_REQUEST['subpanel_field_name']->add($_REQUEST['subpanel_id'],$add_values);
}
//END SUGARCRM flav=dce ONLY
else {

	global $beanFiles,$beanList;
 	$bean_name = $beanList[$_REQUEST['module']];
 	require_once($beanFiles[$bean_name]);
 	$focus = new $bean_name();

 	$focus->retrieve($_REQUEST['record']);

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

 		if(file_exists('custom/modules/'.$module.'/metadata/metafiles.php')){
            require('custom/modules/'.$module.'/metadata/metafiles.php');
        }elseif(file_exists('modules/'.$module.'/metadata/metafiles.php')){
            require('modules/'.$module.'/metadata/metafiles.php');
        }

        if (file_exists('custom/modules/'.$module.'/metadata/searchdefs.php'))
        {
        	require_once('custom/modules/'.$module.'/metadata/searchdefs.php');
        }
        elseif (!empty($metafiles[$module]['searchdefs']))
        {
        	require_once($metafiles[$module]['searchdefs']);
        }
        elseif (file_exists('modules/'.$module.'/metadata/searchdefs.php'))
        {
        	require_once('modules/'.$module.'/metadata/searchdefs.php');
        }

        if(!empty($metafiles[$module]['searchfields'])){
        	require_once($metafiles[$module]['searchfields']);
        }
        elseif(file_exists('modules/'.$module.'/metadata/SearchFields.php')){
        	require_once('modules/'.$module.'/metadata/SearchFields.php');
        }
        if(!empty($searchdefs) && !empty($searchFields)) {
        	$searchForm = new SearchForm($seed, $module);
	        $searchForm->setup($searchdefs, $searchFields, 'SearchFormGeneric.tpl');
	        $searchForm->populateFromArray($current_query_by_page_array, 'advanced');
	        $where_clauses_arr = $searchForm->generateSearchWhere(true, $module);
	        if (count($where_clauses_arr) > 0 ) {
	            $where_clauses = '('. implode(' ) AND ( ', $where_clauses_arr) . ')';
	        }
        }

		$ret_array = create_export_query_relate_link_patch($module, $searchFields, $where_clauses);
		$query = $seed->create_export_query($order_by, $ret_array['where'], $ret_array['join']);
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
