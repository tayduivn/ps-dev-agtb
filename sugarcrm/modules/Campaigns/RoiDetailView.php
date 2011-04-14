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
 * $Id: TrackDetailView.php 17399 2006-10-31 19:18:15 +0000 (Tue, 31 Oct 2006) eddy $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/




require_once('include/DetailView/DetailView.php');
require_once('modules/Campaigns/Charts.php');


global $mod_strings;
global $app_strings;
global $app_list_strings;
global $sugar_version, $sugar_config;

$focus = new Campaign();

$detailView = new DetailView();
$offset = 0;
$offset=0;
if (isset($_REQUEST['offset']) or isset($_REQUEST['record'])) {
	$result = $detailView->processSugarBean("CAMPAIGN", $focus, $offset);
	if($result == null) {
	    sugar_die($app_strings['ERROR_NO_RECORD']);
	}
	$focus=$result;
} else {
	header("Location: index.php?module=Accounts&action=index");
}

// For all campaigns show the same ROI interface
// ..else default to legacy detail view
/*
if(!$focus->campaign_type == "NewsLetter"){
    include ('modules/Campaigns/NewsLetterTrackDetailView.php');
} else{
	
*/
    echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_NAME'].": ".$focus->name, true);
    
    $GLOBALS['log']->info("Campaign detail view");
    
    $xtpl=new XTemplate ('modules/Campaigns/RoiDetailView.html');
    $xtpl->assign("MOD", $mod_strings);
    $xtpl->assign("APP", $app_strings);
    
    $xtpl->assign("THEME", $theme);
    $xtpl->assign("GRIDLINE", $gridline);
    $xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
    $xtpl->assign("ID", $focus->id);
    $xtpl->assign("ASSIGNED_TO", $focus->assigned_user_name);
    $xtpl->assign("STATUS", $app_list_strings['campaign_status_dom'][$focus->status]);
    $xtpl->assign("NAME", $focus->name);
    $xtpl->assign("TYPE", $app_list_strings['campaign_type_dom'][$focus->campaign_type]);
    $xtpl->assign("START_DATE", $focus->start_date);
    $xtpl->assign("END_DATE", $focus->end_date);
    
    $xtpl->assign("BUDGET", $focus->budget);
    $xtpl->assign("ACTUAL_COST", $focus->actual_cost);
    $xtpl->assign("EXPECTED_COST", $focus->expected_cost);
    $xtpl->assign("EXPECTED_REVENUE", $focus->expected_revenue);
    
    
    $xtpl->assign("OBJECTIVE", nl2br($focus->objective));
    $xtpl->assign("CONTENT", nl2br($focus->content));
    $xtpl->assign("DATE_MODIFIED", $focus->date_modified);
    $xtpl->assign("DATE_ENTERED", $focus->date_entered);
    
    $xtpl->assign("CREATED_BY", $focus->created_by_name);
    $xtpl->assign("MODIFIED_BY", $focus->modified_by_name);
    $xtpl->assign("TRACKER_URL", $sugar_config['site_url'] . '/campaign_tracker.php?track=' . $focus->tracker_key);
    $xtpl->assign("TRACKER_COUNT", intval($focus->tracker_count));
    $xtpl->assign("TRACKER_TEXT", $focus->tracker_text);
    $xtpl->assign("REFER_URL", $focus->refer_url);
    $xtpl->assign("IMPRESSIONS", $focus->impressions);
   $roi_vals = array();
   $roi_vals['budget']= $focus->budget;
   $roi_vals['actual_cost']= $focus->actual_cost;
   $roi_vals['Expected_Revenue']= $focus->expected_revenue;
   $roi_vals['Expected_Cost']= $focus->expected_cost;
   
//Query for opportunities won, clickthroughs
$campaign_id = $focus->id;
            $opp_query1  = "select camp.name, count(*) opp_count,SUM(opp.amount) as Revenue, SUM(camp.actual_cost) as Investment, 
                            ROUND((SUM(opp.amount) - SUM(camp.actual_cost))/(SUM(camp.actual_cost)), 2)*100 as ROI";	           
            $opp_query1 .= " from opportunities opp";
            $opp_query1 .= " right join campaigns camp on camp.id = opp.campaign_id";
            $opp_query1 .= " where opp.sales_stage = 'Closed Won' and camp.id='$campaign_id'";
            $opp_query1 .= " and opp.deleted=0";                                  
            $opp_query1 .= " group by camp.name";
            $opp_result1=$focus->db->query($opp_query1);              
            $opp_data1=$focus->db->fetchByAssoc($opp_result1);
      if(empty($opp_data1['opp_count'])) $opp_data1['opp_count']=0; 
      //_ppd($opp_data1);     
     $xtpl->assign("OPPORTUNITIES_WON",$opp_data1['opp_count']);
          
            $camp_query1  = "select camp.name, count(*) click_thru_link";	           
            $camp_query1 .= " from campaign_log camp_log";
            $camp_query1 .= " right join campaigns camp on camp.id = camp_log.campaign_id";
            $camp_query1 .= " where camp_log.activity_type = 'link' and camp.id='$campaign_id'";
            $camp_query1 .= " group by camp.name";
            $opp_query1 .= " and deleted=0";                                  
            $camp_result1=$focus->db->query($camp_query1);              
            $camp_data1=$focus->db->fetchByAssoc($camp_result1);
            
   if(unformat_number($focus->impressions) > 0){         
    $cost_per_impression= unformat_number($focus->actual_cost)/unformat_number($focus->impressions);
   }
   else{
   	$cost_per_impression = format_number(0);
   }       
   $xtpl->assign("COST_PER_IMPRESSION",currency_format_number($cost_per_impression));
   if(empty($camp_data1['click_thru_link'])) $camp_data1['click_thru_link']=0;      
   $click_thru_links = $camp_data1['click_thru_link'];
   
   if($click_thru_links >0){
    $cost_per_click_thru= unformat_number($focus->actual_cost)/unformat_number($click_thru_links);   	
   }
   else{
   	$cost_per_click_thru = format_number(0);
   } 
   $xtpl->assign("COST_PER_CLICK_THROUGH",currency_format_number($cost_per_click_thru));
    
    
    	$currency  = new Currency();
    if(isset($focus->currency_id) && !empty($focus->currency_id))
    {
    	$currency->retrieve($focus->currency_id);
    	if( $currency->deleted != 1){
    		$xtpl->assign("CURRENCY", $currency->iso4217 .' '.$currency->symbol );
    	}else $xtpl->assign("CURRENCY", $currency->getDefaultISO4217() .' '.$currency->getDefaultCurrencySymbol() );
    }else{
    
    	$xtpl->assign("CURRENCY", $currency->getDefaultISO4217() .' '.$currency->getDefaultCurrencySymbol() );
    
    }
    global $current_user;
    if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){
    
    	$xtpl->assign("ADMIN_EDIT","<a href='index.php?action=index&module=DynamicLayout&from_action=".$_REQUEST['action'] ."&from_module=".$_REQUEST['module'] ."&record=".$_REQUEST['record']. "'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>");
    }
    
    $detailView->processListNavigation($xtpl, "CAMPAIGN", $offset, $focus->is_AuditEnabled());
    // adding custom fields:
    require_once('modules/DynamicFields/templates/Files/DetailView.php');
    
    //BEGIN SUGARCRM flav=pro ONLY
    $xtpl->assign("TEAM_NAME", $focus->team_name);
    $xtpl->parse("main.pro");
    /* comment out the non-pro code
    //END SUGARCRM flav=pro ONLY
    $xtpl->parse("main.open_source");
    //BEGIN SUGARCRM flav=pro ONLY
    */
    //END SUGARCRM flav=pro ONLY
    
    
    
    //add chart
    $seps				= array("-", "/");
    $dates				= array(date($GLOBALS['timedate']->dbDayFormat), $GLOBALS['timedate']->dbDayFormat);
    $dateFileNameSafe	= str_replace($seps, "_", $dates);
    //$cache_file_name	= $current_user->getUserPrivGuid()."_campaign_response_by_activity_type_".$dateFileNameSafe[0]."_".$dateFileNameSafe[1].".xml";
    $cache_file_name_roi	= $current_user->getUserPrivGuid()."_campaign_response_by_roi_".$dateFileNameSafe[0]."_".$dateFileNameSafe[1].".xml";
    $chart= new campaign_charts();
    //_ppd($roi_vals);
    $xtpl->assign("MY_CHART_ROI", $chart->campaign_response_roi($app_list_strings['roi_type_dom'],$app_list_strings['roi_type_dom'],$focus->id,true,true));    
    //end chart
    
    $xtpl->parse("main");
    $xtpl->out("main");
?>