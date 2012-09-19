<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
/*********************************************************************************
 * $Id$
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 *********************************************************************************/
$defaultDashlets = array(
						'MyCallsDashlet'=>'Calls', 
						'MyMeetingsDashlet'=>'Meetings',
						'MyOpportunitiesDashlet'=>'Opportunities',
						'MyAccountsDashlet'=>'Accounts', 
						'MyLeadsDashlet'=>'Leads',
						 );
						 
//BEGIN SUGARCRM flav=pro ONLY
$defaultSalesChartDashlets = array( translate('DEFAULT_REPORT_TITLE_6', 'Reports') => 'Opportunities',
	                     );    

//BEGIN SUGARCRM flav!=sales ONLY
$defaultSalesDashlets = array('MyPipelineBySalesStageDashlet'=>'Opportunities', 
							  'MyOpportunitiesGaugeDashlet'=>'Opportunities', 
							  'MyOpportunitiesDashlet'=>'Opportunities',
                              'MyClosedOpportunitiesDashlet'=>'Opportunities',		  
						 );   								  
						 
//Split up because of default ordering (35430)						 
$defaultSalesDashlets2 = array('MyForecastingChartDashlet'=>'Forecasts');						 
//END SUGARCRM flav!=sales ONLY
						 
$defaultMarketingChartDashlets = array( translate('DEFAULT_REPORT_TITLE_18', 'Reports')=>'Leads', // Leads By Lead Source
									  );
									  
$defaultMarketingDashlets = array(  'CampaignROIChartDashlet' => 'Campaigns',
                                    'MyLeadsDashlet'=>'Leads',  
									'TopCampaignsDashlet' => 'Campaigns');
									  
$defaultSupportDashlets = array( 'MyCasesDashlet'=>'Cases',
								 'MyBugsDashlet' =>'Bugs', 
								  );

$defaultSupportChartDashlets = array(   //translate('DEFAULT_REPORT_TITLE_10', 'Reports')=>'Cases', // New Cases By Month
										translate('DEFAULT_REPORT_TITLE_7', 'Reports')=>'Cases', // Open Cases By User By Status
										translate('DEFAULT_REPORT_TITLE_8', 'Reports')=>'Cases', // Open Cases By Month By User
										//translate('DEFAULT_REPORT_TITLE_9', 'Reports')=>'Cases', // Open Cases By Priority By User
									  );								  
								  
$defaultTrackingDashlets = array('TrackerDashlet'=>'Trackers', 
								 'MyModulesUsedChartDashlet'=>'Trackers', 
								 'MyTeamModulesUsedChartDashlet'=>'Trackers',
							    );
							    
$defaultTrackingReportDashlets =  array(translate('DEFAULT_REPORT_TITLE_27', 'Reports')=>'Trackers');

//END SUGARCRM flav=pro ONLY

											


if (is_file('custom/modules/Home/dashlets.php')) include_once('custom/modules/Home/dashlets.php');
?>