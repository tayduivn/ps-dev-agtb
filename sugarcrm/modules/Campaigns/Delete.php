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
 * $Id: Delete.php 51719 2009-10-22 17:18:00Z mitani $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

if(!isset($_REQUEST['record']))
	sugar_die("A record number must be specified to delete the campaign.");



$focus = new Campaign();
$focus->retrieve($_REQUEST['record']);

if (isset($_REQUEST['mode']) and  $_REQUEST['mode']=='Test') {
	//deletes all data associated with the test run.

	//delete from emails table.	
	if ($focus->db->dbType=='mysql') {
		
		$query="update  emails "; 
		$query.="inner join campaign_log on campaign_log.related_id = emails.id and campaign_log.campaign_id = '{$focus->id}' ";
		$query.="inner join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test' ";
		$query.="set emails.deleted=1 ";
	} elseif ($focus->db->dbType=='mssql') {
        $query="update  emails ";
        $query.="set emails.deleted=1 ";
        $query.="where id in ( ";
        $query.="select related_id from campaign_log ";
        $query.="inner join prospect_lists on campaign_log.list_id = prospect_lists.id ";
        $query.="and prospect_lists.list_type='test' ";
        $query.="and campaign_log.campaign_id = '{$focus->id}' ) ";
	} else {
//BEGIN SUGARCRM flav=ent ONLY
		//oracle oci8.
		$query="update  emails ";
		$query.="set emails.deleted=1 ";
		$query.="where id in ( ";
		$query.="select related_id from campaign_log ";
		$query.="inner join prospect_lists on campaign_log.list_id = prospect_lists.id "; 
		$query.="and prospect_lists.list_type='test' ";
		$query.="and campaign_log.campaign_id = '{$focus->id}' ) ";
//END SUGARCRM flav=ent ONLY
	}
	$focus->db->query($query);
		
	//delete from message queue.
	if ($focus->db->dbType=='mysql') {
		$query="delete emailman.* from emailman ";
		$query.="inner join prospect_lists on emailman.list_id = prospect_lists.id and prospect_lists.list_type='test' ";
		$query.="WHERE emailman.campaign_id = '{$focus->id}' ";
	} elseif ($focus->db->dbType=='mssql') {
        $query="delete from emailman ";
        $query.="where list_id in ( ";
        $query.="       select prospect_list_id from prospect_list_campaigns ";
        $query.="       inner join prospect_lists on prospect_list_campaigns.prospect_list_id = prospect_lists.id ";
        $query.="       where prospect_lists.list_type='test' and prospect_list_campaigns.campaign_id = '{$focus->id}' ) ";
    } else {
//BEGIN SUGARCRM flav=ent ONLY
		//oracle oci8.
		$query="delete from emailman ";
		$query.="where list_id in ( ";
		$query.="	select prospect_list_id from prospect_list_campaigns ";
		$query.="	inner join prospect_lists on prospect_list_campaigns.prospect_list_id = prospect_lists.id "; 
		$query.="	where prospect_lists.list_type='test' and prospect_list_campaigns.campaign_id = '{$focus->id}' ) ";
//END SUGARCRM flav=ent ONLY
	}
	$focus->db->query($query);

	//delete from campaign_log
	if ($focus->db->dbType=='mysql') {
		$query="update  campaign_log "; 
		$query.="inner join prospect_lists on campaign_log.list_id = prospect_lists.id and prospect_lists.list_type='test' ";
		$query.="set campaign_log.deleted=1 ";
		$query.="where campaign_log.campaign_id='{$focus->id}' ";
	} elseif ($focus->db->dbType=='mssql') {
        $query="update  campaign_log ";
        $query.="set campaign_log.deleted=1 ";
        $query.="where list_id in ( ";
        $query.="                       select id from prospect_lists ";
        $query.="                       where prospect_lists.list_type='test') ";
        $query.="and campaign_log.campaign_id='{$focus->id}' ";
	} else {
//BEGIN SUGARCRM flav=ent ONLY
		//oracle oci8.
		$query="update  campaign_log "; 
		$query.="set campaign_log.deleted=1 ";
		$query.="where list_id in ( ";
		$query.="			select id from prospect_lists ";
		$query.="			where prospect_lists.list_type='test') ";
		$query.="and campaign_log.campaign_id='{$focus->id}' ";
//END SUGARCRM flav=ent ONLY
	}
	$focus->db->query($query);
} else {
	if(!$focus->ACLAccess('Delete')){
		ACLController::displayNoAccess(true);
		sugar_cleanup(true);
	}
	$focus->mark_deleted($_REQUEST['record']);
}
$return_id=!empty($_REQUEST['return_id'])?$_REQUEST['return_id']:$focus->id;
header("Location: index.php?module=".$_REQUEST['return_module']."&action=".$_REQUEST['return_action']."&record=".$return_id);
?>
