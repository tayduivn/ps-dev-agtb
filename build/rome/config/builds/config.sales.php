<?php
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
$config['builds']['sales']['flav'] = array('sales');
$config['builds']['sales']['lic'] = array('sub');
$config['blackList']['sales'] = array(
'sugarcrm/build'=>1,

'sugarcrm/modules/ACLFields'=>1,

'sugarcrm/modules/Bugs'=>1,
'sugarcrm/service/v3_1'=>1,
'sugarcrm/modules/Campaigns'=>1,
'sugarcrm/modules/CampaignLog'=>1,
'sugarcrm/modules/CampaignTrackers'=>1,
'sugarcrm/modules/Cases'=>1,
'sugarcrm/modules/Charts/Dashlets/CampaignROIChartDashlet'=>1,
'sugarcrm/modules/Charts/Dashlets/MyForecastingChartDashlet'=>1,
'sugarcrm/modules/Charts/Dashlets/MyOpportunitiesGaugeDashlet'=>1,
'sugarcrm/modules/Connectors/connectors/filters/ext/rest/zoominfocompany'=>1,
'sugarcrm/modules/Connectors/connectors/filters/ext/rest/zoominfoperson'=>1,
'sugarcrm/modules/Connectors/connectors/sources/ext/rest/zoominfocompany'=>1,
'sugarcrm/modules/Connectors/connectors/sources/ext/rest/zoominfoperson'=>1,
'sugarcrm/modules/Contracts'=>1,
'sugarcrm/modules/ContractTypes'=>1,
'sugarcrm/modules/CustomQueries'=>1,

'sugarcrm/modules/DataSets'=>1,
'sugarcrm/modules/Dashboard'=>1,
'sugarcrm/modules/DCEActions'=>1,
'sugarcrm/modules/DCEClients'=>1,
'sugarcrm/modules/DCEClusters'=>1,
'sugarcrm/modules/DCEDataBases'=>1,
'sugarcrm/modules/DCEInstances'=>1,
'sugarcrm/modules/DCEReports'=>1,
'sugarcrm/modules/DCETemplates'=>1,

'sugarcrm/modules/EmailMarketing'=>1,
'sugarcrm/modules/Expressions'=>1,

'sugarcrm/modules/Forecasts'=>1,
'sugarcrm/modules/ForecastSchedule'=>1,

'sugarcrm/modules/Administration/views/view.configureshortcutbar.php'=>1,

'sugarcrm/modules/Groups'=>1,

'sugarcrm/modules/Holidays'=>1,
'sugarcrm/modules/KBContents'=>1,
'sugarcrm/modules/KBDocumentKBTags'=>1,
'sugarcrm/modules/KBDocumentRevisions'=>1,
'sugarcrm/modules/KBDocuments'=>1,
'sugarcrm/modules/KBTags'=>1,

'sugarcrm/modules/Leads'=>1,

'sugarcrm/modules/Manufacturers'=>1,



'sugarcrm/modules/ProductBundleNotes'=>1,
'sugarcrm/modules/ProductBundles'=>1,
'sugarcrm/modules/ProductCategories'=>1,
'sugarcrm/modules/Products'=>1,
'sugarcrm/modules/ProductTemplates'=>1,
'sugarcrm/modules/ProductTypes'=>1,
'sugarcrm/modules/Project'=>1,
'sugarcrm/modules/ProjectResources'=>1,
'sugarcrm/modules/ProjectTask'=>1,
'sugarcrm/modules/Prospects'=>1,
'sugarcrm/modules/ProspectLists'=>1,

'sugarcrm/modules/Quotas'=>1,
'sugarcrm/modules/Quotes'=>1,

'sugarcrm/modules/Releases'=>1,
'sugarcrm/modules/ReportMaker'=>1,
'sugarcrm/modules/Reports/Dashlets/MyReportsDashlet'=>1,
'sugarcrm/modules/Shippers'=>1,
'sugarcrm/modules/Sync'=>1,

'sugarcrm/modules/TaxRates'=>1,
'sugarcrm/modules/TeamNotices'=>1,
'sugarcrm/modules/Teams'=>1,
'sugarcrm/modules/TimePeriods'=>1,
'sugarcrm/modules/Trackers/Dashlets'=>1,

'sugarcrm/modules/WorkFlow'=>1,
'sugarcrm/modules/WorkFlowActions'=>1,
'sugarcrm/modules/WorkFlowActionShells'=>1,
'sugarcrm/modules/WorkFlowAlerts'=>1,
'sugarcrm/modules/WorkFlowAlertShells'=>1,
'sugarcrm/modules/WorkFlowTriggerShells'=>1,

'sugarcrm/include/workflow'=>1,
'sugarcrm/include/SugarFields/Teamset'=>1,

'sugarcrm/portal'=>1,

'sugarcrm/themes/Awesome80s'=>1,
'sugarcrm/themes/BoldMove'=>1,
'sugarcrm/themes/FinalFrontier'=>1,
'sugarcrm/themes/GoldenGate'=>1,
'sugarcrm/themes/Legacy'=>1,
'sugarcrm/themes/Links'=>1,
'sugarcrm/themes/Love'=>1,
'sugarcrm/themes/Paradise'=>1,
'sugarcrm/themes/Retro'=>1,
'sugarcrm/themes/RipCurl'=>1,
'sugarcrm/themes/RipCurlorg'=>1,
'sugarcrm/themes/Shred'=>1,
'sugarcrm/themes/Sugar2006'=>1,
'sugarcrm/themes/SugarClassic'=>1,
'sugarcrm/themes/SugarIE6'=>1,
'sugarcrm/themes/SugarLite'=>1,
'sugarcrm/themes/Sunset'=>1,
'sugarcrm/themes/TrailBlazers'=>1,
'sugarcrm/themes/VintageSugar'=>1,
'sugarcrm/themes/WhiteSands'=>1,

'sugarcrm/modules/SugarFollowing'=>1,
'sugarcrm/themes/default/images/user_follow.png'=>1,    
'sugarcrm/themes/default/images/user_unfollow.png'=>1,

'sugarcrm/include/EditView/InlineEdit.css'=>1,
'sugarcrm/include/EditView/InlineEdit.js'=>1,
'sugarcrm/include/EditView/InlineEdit.php'=>1,
'sugarcrm/include/MVC/View/views/view.inlinefield.php'=>1,
'sugarcrm/include/MVC/View/views/view.inlinefieldsave.php'=>1,

'sugarcrm/modules/SugarFavorites'=>1,
'sugarcrm/themes/default/images/star-sheet.png'=>1,

'sugarcrm/include/Expressions'=>1,
'sugarcrm/modules/ExpressionEngine'=>1,

'sugarcrm/themes/default/images/gmail_logo.png'=>1,
'sugarcrm/themes/default/images/yahoomail_logo.png'=>1,
'sugarcrm/themes/default/images/exchange_logo.png'=>1,

'sugarcrm/modules/DCEActions'=>1,
'sugarcrm/modules/DCEClients'=>1,
'sugarcrm/modules/DCEClusters'=>1,
'sugarcrm/modules/DCEDataBases'=>1,
'sugarcrm/modules/DCEInstances'=>1,
'sugarcrm/modules/DCEReports'=>1,
'sugarcrm/modules/DCETemplates'=>1,
'sugarcrm/modules/Charts/Dashlets/DCEActionsByTypesDashlet'=>1,

'sugarcrm/themes/default/images/dce_settings.gif'=>1,
'sugarcrm/themes/default/images/DCEClusters.gif'=>1,
'sugarcrm/themes/default/images/DCEInstances.gif'=>1,
'sugarcrm/themes/default/images/DCElicensingReport.gif'=>1,
'sugarcrm/themes/default/images/DCETemplates.gif'=>1,
'sugarcrm/themes/default/images/DCEDataBases.gif'=>1,
'sugarcrm/themes/default/images/createDCEClusters.gif'=>1,
'sugarcrm/themes/default/images/createDCEInstances.gif'=>1,
'sugarcrm/themes/default/images/createDCETemplates.gif'=>1,
'sugarcrm/themes/default/images/createDCEDataBases.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEActions_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEDataBases_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEInstances_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEClusters_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCETemplates_32.gif'=>1,
'sugarcrm/themes/default/images/icon_DCEReports_32.gif'=>1,

'sugarcrm/modules/QueryBuilder'=>1,
'sugarcrm/modules/Queues'=>1,

'sugarcrm/include/images/sugarsales_lg.png'=>1,
'sugarcrm/include/images/sugarsales_lg_dce.png'=>1,
'sugarcrm/include/images/sugarsales_lg_ent.png'=>1,
'sugarcrm/include/images/sugarsales_lg_express.png'=>1,
'sugarcrm/include/images/sugarsales_lg_open.png'=>1,
'sugarcrm/include/images/sugarsales_lg_corp.png'=>1,
'sugarcrm/include/images/sugarsales_lg_ult.png'=>1,
'sugarcrm/include/images/sugar_md.png'=>1,
'sugarcrm/include/images/sugar_md_dce.png'=>1,
'sugarcrm/include/images/sugar_md_dev.png'=>1,
'sugarcrm/include/images/sugar_md_ent.png'=>1,
'sugarcrm/include/images/sugar_md_express.png'=>1,
'sugarcrm/include/images/sugar_md_open.png'=>1,
'sugarcrm/include/images/sugar_md_corp.png'=>1,
'sugarcrm/include/images/sugar_md_ult.png'=>1,

'sugarcrm/portal2' =>1
);
$build = 'sales';
