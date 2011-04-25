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
 * $Id: QueueCampaign.php 51719 2009-10-22 17:18:00Z mitani $
 * Description: Schedules email for delivery. emailman table holds emails for delivery.
 * A cron job polls the emailman table and delivers emails when intended send date time is reached.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/





global $timedate;
global $current_user;
global $mod_strings;

$campaign = new Campaign();
$campaign->retrieve($_REQUEST['record']);
$err_messages=array();

$test=false;
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] =='test') {
	$test=true;
}

//this is to account for the case of sending directly from summary page in wizards
$from_wiz =false;
if (isset($_REQUEST['wiz_mass'])) {
    $mass[] = $_REQUEST['wiz_mass'];
    $_POST['mass'] = $mass;
    $from_wiz =true;
}
if (isset($_REQUEST['from_wiz'])) {
    $from_wiz =true;
}

//if campaign status is 'sending' disallow this step.
if (!empty($campaign->status) && $campaign->status == 'sending') {
	$err_messages[]=$mod_strings['ERR_SENDING_NOW'];
}
if ($campaign->db->dbType=='oci8') {
//BEGIN SUGARCRM flav=ent ONLY
	$current_date="TO_DATE('".$timedate->nowDb()."','YYYY-MM-DD HH24:MI:SS')";
//END SUGARCRM flav=ent ONLY
} else {
	$current_date= "'".$timedate->nowDb()."'";
}

//start scheduling now.....
foreach ($_POST['mass'] as $message_id) {

	//fetch email marketing definition.
	if (!class_exists('EmailMarketing')) require_once('modules/EmailMarketing/EmailMarketing.php');


	$marketing = new EmailMarketing();
	$marketing->retrieve($message_id);

	//make sure that the marketing message has a mailbox.
	//
	if (empty($marketing->inbound_email_id)) {

		echo "<p>";
		echo "<h4>{$mod_strings['ERR_NO_MAILBOX']}</h4>";
		echo "<BR><a href='index.php?module=EmailMarketing&action=EditView&record={$marketing->id}'>$marketing->name</a>";
		echo "</p>";
		sugar_die('');
	}


	global $timedate;
	$mergedvalue=$timedate->merge_date_time($marketing->date_start,$marketing->time_start);

	if ($campaign->db->dbType=='oci8') {
//BEGIN SUGARCRM flav=ent ONLY
		if ($test) {
			$send_date_time="TO_DATE('".$timedate->getNow()->get("-60 seconds")->asDb()."','YYYY-MM-DD HH24:MI:SS')";
		} else {
			$send_date_time= "TO_DATE('".$timedate->to_db($mergedvalue)."','YYYY-MM-DD HH24:MI:SS')";
		}
//END SUGARCRM flav=ent ONLY
	} else {
		if ($test) {
			$send_date_time="'".$timedate->getNow()->get("-60 seconds")->asDb() ."'";
		} else {
			$send_date_time= "'".$timedate->to_db($mergedvalue)."'";
		}
	}

	//find all prospect lists associated with this email marketing message.
	if ($marketing->all_prospect_lists == 1) {
		$query="SELECT prospect_lists.id prospect_list_id from prospect_lists ";
		$query.=" INNER JOIN prospect_list_campaigns plc ON plc.prospect_list_id = prospect_lists.id";
		$query.=" WHERE plc.campaign_id='{$campaign->id}'";
		$query.=" AND prospect_lists.deleted=0";
		$query.=" AND plc.deleted=0";
		if ($test) {
			$query.=" AND prospect_lists.list_type='test'";
		} else {
			$query.=" AND prospect_lists.list_type!='test' AND prospect_lists.list_type not like 'exempt%'";
		}
	} else {
		$query="select email_marketing_prospect_lists.* FROM email_marketing_prospect_lists ";
		$query.=" inner join prospect_lists on prospect_lists.id = email_marketing_prospect_lists.prospect_list_id";
		$query.=" WHERE prospect_lists.deleted=0 and email_marketing_id = '$message_id' and email_marketing_prospect_lists.deleted=0";

		if ($test) {
			$query.=" AND prospect_lists.list_type='test'";
		} else {
			$query.=" AND prospect_lists.list_type!='test' AND prospect_lists.list_type not like 'exempt%'";
		}
	}
	$result=$campaign->db->query($query);
	while (($row=$campaign->db->fetchByAssoc($result))!=null ) {


		$prospect_list_id=$row['prospect_list_id'];

		//delete all messages for the current campaign and current email marketing message.
		$delete_emailman_query="delete from emailman where campaign_id='{$campaign->id}' and marketing_id='{$message_id}' and list_id='{$prospect_list_id}'";
		$campaign->db->query($delete_emailman_query);

		$insert_query= "INSERT INTO emailman (date_entered, user_id, campaign_id, marketing_id,list_id, related_id, related_type, send_date_time";
		if ($campaign->db->dbType=='oci8') {
//BEGIN SUGARCRM flav=ent ONLY
			$insert_query.=',id';
//END SUGARCRM flav=ent ONLY
		}
		$insert_query.=')';
		$insert_query.= " SELECT $current_date,'{$current_user->id}',plc.campaign_id,'{$message_id}',plp.prospect_list_id, plp.related_id, plp.related_type,{$send_date_time} ";
		if ($campaign->db->dbType=='oci8') {
//BEGIN SUGARCRM flav=ent ONLY
			$insert_query.=',EMAILMAN_ID_SEQ.nextval ';
//END SUGARCRM flav=ent ONLY
		}
		$insert_query.= "FROM prospect_lists_prospects plp ";
		$insert_query.= "INNER JOIN prospect_list_campaigns plc ON plc.prospect_list_id = plp.prospect_list_id ";
		$insert_query.= "WHERE plp.prospect_list_id = '{$prospect_list_id}' ";
		$insert_query.= "AND plp.deleted=0 ";
		$insert_query.= "AND plc.deleted=0 ";
		$insert_query.= "AND plc.campaign_id='{$campaign->id}'";

		if ($campaign->db->dbType=='oci8') {
//BEGIN SUGARCRM flav=ent ONLY
			$insert_query.= " AND plp.id not in ( ";
			$insert_query.= " 		SELECT niplp.id from prospect_lists_prospects niplp ";
			$insert_query.= " 		INNER JOIN prospect_list_campaigns niplc ON niplc.id = niplp.prospect_list_id and niplc.campaign_id='{$campaign->id}' ";
			$insert_query.= " 		INNER JOIN prospect_lists nipl ON nipl.id = niplp.prospect_list_id and nipl.list_type = 'exempt'  ";
			$insert_query.= " 		WHERE niplp.deleted=0 ";
			$insert_query.= " 		and nipl.deleted=0 ";
			$insert_query.= " 		and niplc.deleted=0 ";
			$insert_query.= " ) ";
//END SUGARCRM flav=ent ONLY
		}
		$campaign->db->query($insert_query);
	}
}

//delete all entries from the emailman table that belong to the exempt list.
if (!$test) {
    //id based exempt list treatment.
    if ($campaign->db->dbType =='mysql') {

        $delete_query = "DELETE emailman.* FROM emailman ";
        $delete_query.= "INNER JOIN prospect_lists_prospects plp on plp.related_id = emailman.related_id and  plp.related_type = emailman.related_type ";
        $delete_query.= "INNER JOIN prospect_lists pl ON pl.id = plp.prospect_list_id ";
        $delete_query .= "INNER JOIN prospect_list_campaigns plc on plp.prospect_list_id = plc.prospect_list_id ";
        $delete_query.= "WHERE plp.deleted=0 ";
        $delete_query.= "AND plc.campaign_id = '{$campaign->id}'";
        $delete_query.= "AND pl.list_type = 'exempt' ";
        $delete_query.= "AND emailman.campaign_id='{$campaign->id}'";
        $campaign->db->query($delete_query);

    }elseif($campaign->db->dbType =='mssql'){
        $delete_query =  "DELETE FROM emailman ";
        $delete_query .= "WHERE emailman.campaign_id='".$campaign->id."' ";
        $delete_query .= "and emailman.related_id in ";
        $delete_query .= "(select prospect_lists_prospects.related_id from prospect_lists_prospects where prospect_lists_prospects.prospect_list_id in (select prospect_lists.id from prospect_lists where prospect_lists.list_type = 'exempt' and prospect_lists_prospects.prospect_list_id in(select prospect_list_campaigns.prospect_list_id from prospect_list_campaigns where prospect_list_campaigns.campaign_id = '".$campaign->id."'))) ";
        $delete_query .= "and emailman.related_type in ";
        $delete_query .= "(select prospect_lists_prospects.related_type from prospect_lists_prospects where prospect_lists_prospects.prospect_list_id in (select prospect_lists.id from prospect_lists where prospect_lists.list_type = 'exempt' and prospect_lists_prospects.prospect_list_id in(select prospect_list_campaigns.prospect_list_id from prospect_list_campaigns where prospect_list_campaigns.campaign_id = '".$campaign->id."'))) ";
        $campaign->db->query($delete_query);
    }
}

$return_module=isset($_REQUEST['return_module'])?$_REQUEST['return_module']:'Campaigns';
$return_action=isset($_REQUEST['return_action'])?$_REQUEST['return_action']:'DetailView';
$return_id=$_REQUEST['record'];

if ($test) {
	//navigate to EmailManDelivery..
	$header_URL = "Location: index.php?action=EmailManDelivery&module=EmailMan&campaign_id={$_REQUEST['record']}&return_module={$return_module}&return_action={$return_action}&return_id={$return_id}&mode=test";
    if($from_wiz){$header_URL .= "&from_wiz=true";}
} else {
	//navigate back to campaign detail view...
	$header_URL = "Location: index.php?action={$return_action}&module={$return_module}&record={$return_id}";
    if($from_wiz){$header_URL .= "&from=send";}
}
$GLOBALS['log']->debug("about to post header URL of: $header_URL");
header($header_URL);
?>