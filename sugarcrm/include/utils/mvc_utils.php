<?php
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

function loadParentView($type)
{
    if(file_exists('custom/include/MVC/View/views/view.'.$type.'.php'))
    {
        require_once('custom/include/MVC/View/views/view.'.$type.'.php');
    } else if(file_exists('include/MVC/View/views/view.'.$type.'.php')) {
        require_once('include/MVC/View/views/view.'.$type.'.php');
    }
}


function getPrintLink()
{
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "ajaxui")
    {
        return "javascript:SUGAR.ajaxUI.print();";
    }
    return "javascript:void window.open('index.php?{$GLOBALS['request_string']}',"
         . "'printwin','menubar=1,status=0,resizable=1,scrollbars=1,toolbar=0,location=1')";
}


function ajaxBannedModules(){
    $bannedModules = array(
        'Calendar',
        //BEGIN SUGARCRM flav=pro || flav=sales ONLY
        'Reports',
        //END SUGARCRM flav=pro || flav=sales ONLY
        //BEGIN SUGARCRM flav!=sales ONLY
        'Emails',
        'Campaigns',
        'Documents',
        'DocumentRevisions',
        'Project',
        'ProjectTask',
        'EmailMarketing',
        'CampaignLog',
        'CampaignTrackers',
        'Releases',
        'Groups',
        'EmailMan',
        //END SUGARCRM flav!=sales ONLY
        //BEGIN SUGARCRM flav=pro ONLY
        'ACLFields',
        'ACLRoles',
        'ACLActions',
        'TrackerSessions',
        'TrackerPerfs',
        'TrackerQueries',
        'Teams',
        'TeamMemberships',
        'TeamSets',
        'TeamSetModules',
        'Quotes',
        'Products',
        'ProductBundles',
        'ProductBundleNotes',
        'ProductTemplates',
        'ProductTypes',
        'ProductCategories',
        'Manufacturers',
        'Shippers',
        'TaxRates',
        'TeamNotices',
        'TimePeriods',
        'Forecasts',
        'ForecastSchedule',
        'Worksheet',
        'ForecastOpportunities',
        'Quotas',
        'WorkFlow',
        'WorkFlowTriggerShells',
        'WorkFlowAlertShells',
        'WorkFlowAlerts',
        'WorkFlowActionShells',
        'WorkFlowActions',
        'Expressions',
        'Contracts',
        'KBDocuments',
        'KBDocumentRevisions',
        'KBTags',
        'KBDocumentKBTags',
        'KBContents',
        'ContractTypes',
        'Holidays',
        'ProjectResources',
        //END SUGARCRM flav=pro ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        'CustomQueries',
        'DataSets',
        'ReportMaker',
        //END SUGARCRM flav=ent ONLY
        //BEGIN SUGARCRM flav=dce ONLY
        'DCEInstances',
        'DCEClusters',
        'DCEDataBases',
        'DCETemplates',
        'DCEActions',
        'DCEReports',
        //END SUGARCRM flav=dce ONLY
        "Administration",
        "ModuleBuilder",
        'Schedulers',
        'SchedulersJobs',
        'DynamicFields',
        'EditCustomFields',
        'EmailTemplates',
        'Users',
        'Currencies',
        'Trackers',
        'Connectors',
        'Import_1',
        'Import_2',
        'Versions',
        'vCals',
        'CustomFields',
        'Roles',
        'Audit',
        'InboundEmail',
        'SavedSearch',
        'UserPreferences',
        'MergeRecords',
        'EmailAddresses',
        'Relationships',
        'Employees',
        'Import',
        'OAuthKeys'
    );

    if(!empty($GLOBALS['sugar_config']['addAjaxBannedModules'])){
        $bannedModules = array_merge($bannedModules, $GLOBALS['sugar_config']['addAjaxBannedModules']);
    }
    if(!empty($GLOBALS['sugar_config']['overrideAjaxBannedModules'])){
        $bannedModules = $GLOBALS['sugar_config']['overrideAjaxBannedModules'];
    }

    return $bannedModules;
}

function ajaxLink($url)
{
    global $sugar_config;
    $match = array();
    $javascriptMatch = array();

    preg_match('/module=([^&]*)/i', $url, $match);
    preg_match('/^javascript/i', $url, $javascriptMatch);

    if(!empty($sugar_config['disableAjaxUI'])){
        return $url;
    }
    else if(isset($match[1]) && in_array($match[1], ajaxBannedModules())){
        return $url;
    }
    //Don't modify javascript calls.
    else if (isset($javascriptMatch[0])) {
    	return $url;
    }
    else
    {
        return "?action=ajaxui#ajaxUILoc=" . urlencode($url);
    }
}

?>
