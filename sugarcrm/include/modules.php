<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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
/*********************************************************************************gf
 * $Id: modules.php 56945 2010-06-14 19:51:27Z jmertic $
 * Description:  Executes a step in the installation process.
 ********************************************************************************/

$moduleList = array();
// this list defines the modules shown in the top tab list of the app
//the order of this list is the default order displayed - do not change the order unless it is on purpose
$moduleList[] = 'Home';
//BEGIN SUGARCRM flav!=dce ONLY
$moduleList[] = 'Calendar';
$moduleList[] = 'Calls';
$moduleList[] = 'Meetings';
$moduleList[] = 'Tasks';
$moduleList[] = 'Notes';
//BEGIN SUGARCRM flav=pro || flav=sales ONLY
$moduleList[] = 'Reports';
//END SUGARCRM flav=pro || flav=sales ONLY
//BEGIN SUGARCRM flav!=sales ONLY
$moduleList[] = 'Leads';
//END SUGARCRM flav!=sales ONLY
$moduleList[] = 'Contacts';
$moduleList[] = 'Accounts';
$moduleList[] = 'Opportunities';

//BEGIN SUGARCRM flav!=sales ONLY
$moduleList[] = 'Emails';
$moduleList[] = 'Campaigns';
$moduleList[] = 'Prospects';
$moduleList[] = 'ProspectLists';
//END SUGARCRM flav!=sales ONLY

//BEGIN SUGARCRM flav=pro ONLY
$moduleList[] = 'Quotes';
$moduleList[] = 'Products';
//END SUGARCRM flav=pro ONLY

//BEGIN SUGARCRM flav!=sales ONLY
$moduleList[] = 'Documents';
$moduleList[] = 'Cases';
$moduleList[] = 'Project';
$moduleList[] = 'Bugs';
//END SUGARCRM flav!=sales ONLY
//BEGIN SUGARCRM flav=pro ONLY
$moduleList[] = 'Forecasts';
$moduleList[] = 'ForecastWorksheets';
$moduleList[] = 'ForecastManagerWorksheets';
$moduleList[] = 'ForecastSchedule';
//END SUGARCRM flav=pro ONLY
//BEGIN SUGARCRM flav=pro ONLY
$moduleList[] = 'Contracts';
$moduleList[] = 'KBDocuments';
//END SUGARCRM flav=pro ONLY
//END SUGARCRM flav!=dce ONLY

//BEGIN SUGARCRM flav=dce ONLY
$moduleList[] = 'Accounts';
$moduleList[] = 'Contacts';
$moduleList[] = 'DCEClusters';
$moduleList[] = 'DCETemplates';
$moduleList[] = 'DCEInstances';
$moduleList[] = 'DCEActions';
$moduleList[] = 'Emails';
$moduleList[] = 'Cases';
$moduleList[] = 'Reports';
//END SUGARCRM flav=dce ONLY

// this list defines all of the module names and bean names in the app
// to create a new module's bean class, add the bean definition here
$beanList = array();
//ACL Objects
$beanList['ACLRoles']       = 'ACLRole';
$beanList['ACLActions']     = 'ACLAction';
//BEGIN SUGARCRM flav=pro ONLY
$beanList['ACLFields']       = 'ACLField';
//END SUGARCRM flav=pro ONLY
//END ACL OBJECTS
//BEGIN SUGARCRM flav!=sales ONLY
$beanList['Leads']          = 'Lead';
$beanList['Cases']          = 'aCase';
$beanList['Bugs']           = 'Bug';
$beanList['ProspectLists']      = 'ProspectList';
$beanList['Prospects']  = 'Prospect';
$beanList['Project']            = 'Project';
$beanList['ProjectTask']            = 'ProjectTask';
$beanList['Campaigns']          = 'Campaign';
$beanList['EmailMarketing']  = 'EmailMarketing';
$beanList['CampaignLog']        = 'CampaignLog';
$beanList['CampaignTrackers']   = 'CampaignTracker';
$beanList['Releases']       = 'Release';
$beanList['Groups'] = 'Group';
$beanList['EmailMan'] = 'EmailMan';
//END SUGARCRM flav!=sales ONLY
$beanList['Schedulers']  = 'Scheduler';
$beanList['SchedulersJobs']  = 'SchedulersJob';
$beanList['Contacts']       = 'Contact';
$beanList['Accounts']       = 'Account';
$beanList['DynamicFields']  = 'DynamicField';
$beanList['EditCustomFields']   = 'FieldsMetaData';
$beanList['Opportunities']  = 'Opportunity';

$beanList['EmailTemplates']     = 'EmailTemplate';
$beanList['Notes']          = 'Note';
$beanList['Calls']          = 'Call';
$beanList['Emails']         = 'Email';
$beanList['Meetings']       = 'Meeting';
$beanList['Tasks']          = 'Task';
$beanList['Users']          = 'User';
$beanList['Currencies']     = 'Currency';
$beanList['Trackers']       = 'Tracker';
$beanList['Connectors']     = 'Connectors';
//BEGIN SUGARCRM flav=pro ONLY
$beanList['TrackerSessions']= 'TrackerSession';
$beanList['TrackerPerfs']   = 'TrackerPerf';
$beanList['TrackerQueries'] = 'TrackerQuery';
//END SUGARCRM flav=pro ONLY
$beanList['Import_1']         = 'ImportMap';
$beanList['Import_2']       = 'UsersLastImport';
$beanList['Versions']       = 'Version';
$beanList['Administration'] = 'Administration';
$beanList['vCals']          = 'vCal';
$beanList['CustomFields']       = 'CustomFields';





//BEGIN SUGARCRM flav!=sales ONLY
$beanList['Documents']  = 'Document';
$beanList['DocumentRevisions']  = 'DocumentRevision';
//END SUGARCRM flav!=sales ONLY
$beanList['Roles']  = 'Role';

$beanList['Audit']  = 'Audit';

// deferred
//$beanList['Queues'] = 'Queue';

$beanList['InboundEmail'] = 'InboundEmail';


$beanList['SavedSearch']            = 'SavedSearch';
$beanList['UserPreferences']        = 'UserPreference';
$beanList['MergeRecords'] = 'MergeRecord';
$beanList['EmailAddresses'] = 'EmailAddress';
$beanList['EmailText'] = 'EmailText';
$beanList['Relationships'] = 'Relationship';
$beanList['Employees']      = 'Employee';
//BEGIN SUGARCRM flav=pro || flav=sales ONLY
$beanList['Reports']        = 'SavedReport';
$beanList['Reports_1']      = 'SavedReport';
//END SUGARCRM flav=pro || flav=sales ONLY
//BEGIN SUGARCRM flav=pro ONLY
$beanList['Teams']          = 'Team';
$beanList['TeamMemberships']            = 'TeamMembership';
//BEGIN SUGARCRM flav=int ONLY
$beanList['TeamHierarchies']            = 'TeamHierarchy';
//END SUGARCRM flav=int ONLY
$beanList['TeamSets']            = 'TeamSet';
$beanList['TeamSetModules']            = 'TeamSetModule';
$beanList['Quotes']         = 'Quote';
$beanList['Products']       = 'Product';
$beanList['ProductBundles']     = 'ProductBundle';
$beanList['ProductBundleNotes'] = 'ProductBundleNote';
$beanList['ProductTemplates']= 'ProductTemplate';
$beanList['ProductTypes']   = 'ProductType';
$beanList['ProductCategories']= 'ProductCategory';
$beanList['Manufacturers']  = 'Manufacturer';
$beanList['Shippers']       = 'Shipper';
$beanList['TaxRates']       = 'TaxRate';
$beanList['TeamNotices']        = 'TeamNotice';
$beanList['TimePeriods']    = 'TimePeriod';
$beanList['Forecasts']  = 'Forecast';
$beanList['ForecastWorksheets']  = 'ForecastWorksheet';
$beanList['ForecastManagerWorksheets']  = 'ForecastManagerWorksheet';
$beanList['ForecastSchedule']  = 'ForecastSchedule';
$beanList['Worksheet']  = 'Worksheet';
$beanList['ForecastOpportunities']  = 'ForecastOpportunities';
$beanList['Quotas']     = 'Quota';
$beanList['WorkFlow']  = 'WorkFlow';
$beanList['WorkFlowTriggerShells']  = 'WorkFlowTriggerShell';
$beanList['WorkFlowAlertShells']  = 'WorkFlowAlertShell';
$beanList['WorkFlowAlerts']  = 'WorkFlowAlert';
$beanList['WorkFlowActionShells']  = 'WorkFlowActionShell';
$beanList['WorkFlowActions']  = 'WorkFlowAction';
$beanList['Expressions']  = 'Expression';
$beanList['Contracts']  = 'Contract';
$beanList['KBDocuments'] = 'KBDocument';
$beanList['KBDocumentRevisions'] = 'KBDocumentRevision';
$beanList['KBTags'] = 'KBTag';
$beanList['KBDocumentKBTags'] = 'KBDocumentKBTag';
$beanList['KBContents'] = 'KBContent';
$beanList['ContractTypes']  = 'ContractType';
$beanList['Holidays'] = 'Holiday';
$beanList['ProjectResources'] = 'ProjectResource';
//END SUGARCRM flav=pro ONLY

//BEGIN SUGARCRM flav=ent ONLY
$beanList['CustomQueries']  = 'CustomQuery';
$beanList['DataSets']  = 'DataSet';
$beanList['ReportMaker']  = 'ReportMaker';
//END SUGARCRM flav=ent ONLY
//BEGIN SUGARCRM flav=int ONLY
//$beanList['QueryBuilder']  = 'QueryBuilder';
//END SUGARCRM flav=int ONLY


//BEGIN SUGARCRM flav=dce ONLY
$beanList['DCEInstances'] = 'DCEInstance';
$beanList['DCEClusters'] = 'DCECluster';
$beanList['DCEDataBases'] = 'DCEDataBase';
$beanList['DCETemplates'] = 'DCETemplate';
$beanList['DCEActions'] = 'DCEAction';
$beanList['DCEReports'] = 'DCEReport';
// For ACL Action table
$DCEbeanList['DCEInstances'] = 'DCEInstance';
$DCEbeanList['DCEClusters'] = 'DCECluster';
$DCEbeanList['DCEDataBases'] = 'DCEDataBase';
$DCEbeanList['DCETemplates'] = 'DCETemplate';
$DCEbeanList['DCEActions'] = 'DCEAction';
$DCEbeanList['DCEReports'] = 'DCEReport';
$DCEbeanList['Contacts']       = 'Contact';
$DCEbeanList['Accounts']       = 'Account';
$DCEbeanList['Cases']          = 'aCase';
$DCEbeanList['Emails']         = 'Email';
$DCEbeanList['EmailTemplates']     = 'EmailTemplate';
$DCEbeanList['Notes']          = 'Note';
$DCEbeanList['Tasks']          = 'Task';
$DCEbeanList['Reports']        = 'SavedReport';
$DCEbeanList['Reports_1']      = 'SavedReport';
//END SUGARCRM flav=dce ONLY

// this list defines all of the files that contain the SugarBean class definitions from $beanList
// to create a new module's bean class, add the file definition here
$beanFiles = array();

$beanFiles['ACLAction'] = 'modules/ACLActions/ACLAction.php';
$beanFiles['ACLRole'] = 'modules/ACLRoles/ACLRole.php';
$beanFiles['Relationship']  = 'modules/Relationships/Relationship.php';

//BEGIN SUGARCRM flav!=sales ONLY
$beanFiles['Lead']          = 'modules/Leads/Lead.php';
$beanFiles['aCase']         = 'modules/Cases/Case.php';
$beanFiles['Bug']           = 'modules/Bugs/Bug.php';
$beanFiles['Group'] = 'modules/Groups/Group.php';
$beanFiles['CampaignLog']  = 'modules/CampaignLog/CampaignLog.php';
$beanFiles['Project']           = 'modules/Project/Project.php';
$beanFiles['ProjectTask']           = 'modules/ProjectTask/ProjectTask.php';
$beanFiles['Campaign']          = 'modules/Campaigns/Campaign.php';
$beanFiles['ProspectList']      = 'modules/ProspectLists/ProspectList.php';
$beanFiles['Prospect']  = 'modules/Prospects/Prospect.php';

$beanFiles['EmailMarketing']          = 'modules/EmailMarketing/EmailMarketing.php';
$beanFiles['CampaignTracker']  = 'modules/CampaignTrackers/CampaignTracker.php';
$beanFiles['Release']           = 'modules/Releases/Release.php';
$beanFiles['EmailMan']          = 'modules/EmailMan/EmailMan.php';
//END SUGARCRM flav!=sales ONLY

$beanFiles['Scheduler']  = 'modules/Schedulers/Scheduler.php';
$beanFiles['SchedulersJob']  = 'modules/SchedulersJobs/SchedulersJob.php';
$beanFiles['Contact']       = 'modules/Contacts/Contact.php';
$beanFiles['Account']       = 'modules/Accounts/Account.php';
$beanFiles['Opportunity']   = 'modules/Opportunities/Opportunity.php';
$beanFiles['EmailTemplate']         = 'modules/EmailTemplates/EmailTemplate.php';
$beanFiles['Note']          = 'modules/Notes/Note.php';
$beanFiles['Call']          = 'modules/Calls/Call.php';
$beanFiles['Email']         = 'modules/Emails/Email.php';
$beanFiles['Meeting']       = 'modules/Meetings/Meeting.php';
$beanFiles['Task']          = 'modules/Tasks/Task.php';
$beanFiles['User']          = 'modules/Users/User.php';
$beanFiles['Employee']      = 'modules/Employees/Employee.php';
$beanFiles['Currency']          = 'modules/Currencies/Currency.php';
$beanFiles['Tracker']          = 'modules/Trackers/Tracker.php';
//BEGIN SUGARCRM flav=pro ONLY
$beanFiles['TrackerPerf']      = 'modules/Trackers/TrackerPerf.php';
$beanFiles['TrackerSession']   = 'modules/Trackers/TrackerSession.php';
$beanFiles['TrackerQuery']     = 'modules/Trackers/TrackerQuery.php';
//END SUGARCRM flav=pro ONLY
$beanFiles['ImportMap']     = 'modules/Import/maps/ImportMap.php';
$beanFiles['UsersLastImport']= 'modules/Import/UsersLastImport.php';
$beanFiles['Administration']= 'modules/Administration/Administration.php';
$beanFiles['UpgradeHistory']= 'modules/Administration/UpgradeHistory.php';
$beanFiles['vCal']          = 'modules/vCals/vCal.php';

$beanFiles['Version']           = 'modules/Versions/Version.php';



$beanFiles['Role']          = 'modules/Roles/Role.php';

//BEGIN SUGARCRM flav!=sales ONLY
$beanFiles['Document']  = 'modules/Documents/Document.php';
$beanFiles['DocumentRevision']  = 'modules/DocumentRevisions/DocumentRevision.php';
//END SUGARCRM flav!=sales ONLY
$beanFiles['FieldsMetaData']    = 'modules/DynamicFields/FieldsMetaData.php';
//$beanFiles['Audit']           = 'modules/Audit/Audit.php';

// deferred
//$beanFiles['Queue'] = 'modules/Queues/Queue.php';

$beanFiles['InboundEmail'] = 'modules/InboundEmail/InboundEmail.php';



$beanFiles['SavedSearch']  = 'modules/SavedSearch/SavedSearch.php';
$beanFiles['UserPreference']  = 'modules/UserPreferences/UserPreference.php';
$beanFiles['MergeRecord']  = 'modules/MergeRecords/MergeRecord.php';
$beanFiles['EmailAddress'] = 'modules/EmailAddresses/EmailAddress.php';
$beanFiles['EmailText'] = 'modules/EmailText/EmailText.php';
//BEGIN SUGARCRM flav=pro || flav=sales ONLY
$beanFiles['SavedReport']   = 'modules/Reports/SavedReport.php';
//END SUGARCRM flav=pro || flav=sales ONLY
//BEGIN SUGARCRM flav=pro ONLY
$beanFiles['ACLField'] = 'modules/ACLFields/ACLField.php';
$beanFiles['Contract']  = 'modules/Contracts/Contract.php';
$beanFiles['Team']          = 'modules/Teams/Team.php';
$beanFiles['TeamMembership']            = 'modules/Teams/TeamMembership.php';
//BEGIN SUGARCRM flav=int ONLY
$beanFiles['TeamHierarchy']            = 'modules/Teams/TeamHierarchy.php';
//END SUGARCRM flav=int ONLY
$beanFiles['TeamSet']            = 'modules/Teams/TeamSet.php';
$beanFiles['TeamSetModule']            = 'modules/Teams/TeamSetModule.php';
$beanFiles['TeamNotice']            = 'modules/TeamNotices/TeamNotice.php';
$beanFiles['ProductTemplate']= 'modules/ProductTemplates/ProductTemplate.php';
$beanFiles['ProductType']   = 'modules/ProductTypes/ProductType.php';
$beanFiles['ProductCategory']= 'modules/ProductCategories/ProductCategory.php';
$beanFiles['Manufacturer']  = 'modules/Manufacturers/Manufacturer.php';
$beanFiles['Quote']         = 'modules/Quotes/Quote.php';
$beanFiles['ProductBundleNote'] = 'modules/ProductBundleNotes/ProductBundleNote.php';
$beanFiles['Product']       = 'modules/Products/Product.php';
$beanFiles['ProductBundle']     = 'modules/ProductBundles/ProductBundle.php';
$beanFiles['Shipper']       = 'modules/Shippers/Shipper.php';
$beanFiles['TaxRate']       = 'modules/TaxRates/TaxRate.php';
$beanFiles['TimePeriod']        = 'modules/TimePeriods/TimePeriod.php';
$beanFiles['Forecast']      = 'modules/Forecasts/Forecast.php';
$beanFiles['ForecastWorksheet'] = 'modules/Forecasts/ForecastWorksheet.php';
$beanFiles['ForecastManagerWorksheet'] = 'modules/Forecasts/ForecastManagerWorksheet.php';
$beanFiles['ForecastSchedule']  = 'modules/ForecastSchedule/ForecastSchedule.php';
$beanFiles['ForecastOpportunities']  = 'modules/Forecasts/ForecastOpportunities.php';
$beanFiles['Quota']  = 'modules/Quotas/Quota.php';
$beanFiles['Worksheet']  = 'modules/Forecasts/Worksheet.php';
$beanFiles['WorkFlow']  = 'modules/WorkFlow/WorkFlow.php';
$beanFiles['WorkFlowTriggerShell']  = 'modules/WorkFlowTriggerShells/WorkFlowTriggerShell.php';
$beanFiles['WorkFlowAlertShell']  = 'modules/WorkFlowAlertShells/WorkFlowAlertShell.php';
$beanFiles['WorkFlowAlert']  = 'modules/WorkFlowAlerts/WorkFlowAlert.php';
$beanFiles['WorkFlowActionShell']  = 'modules/WorkFlowActionShells/WorkFlowActionShell.php';
$beanFiles['WorkFlowAction']  = 'modules/WorkFlowActions/WorkFlowAction.php';
$beanFiles['Expression']  = 'modules/Expressions/Expression.php';
$beanFiles['System']      = 'modules/Administration/System.php';
$beanFiles['SessionManager']      = 'modules/Administration/SessionManager.php';
$beanFiles['KBDocument'] = 'modules/KBDocuments/KBDocument.php';
$beanFiles['KBDocumentRevision'] = 'modules/KBDocumentRevisions/KBDocumentRevision.php';
$beanFiles['KBTag'] = 'modules/KBTags/KBTag.php';
$beanFiles['KBDocumentKBTag'] = 'modules/KBDocumentKBTags/KBDocumentKBTag.php';
$beanFiles['KBContent'] = 'modules/KBContents/KBContent.php';
$beanFiles['ContractType']  = 'modules/ContractTypes/ContractType.php';
$beanFiles['ProjectResource'] = 'modules/ProjectResources/ProjectResource.php';
$beanFiles['Holiday'] = 'modules/Holidays/Holiday.php';
//END SUGARCRM flav=pro ONLY
//BEGIN SUGARCRM flav=ent ONLY
$beanFiles['CustomQuery']= 'modules/CustomQueries/CustomQuery.php';
$beanFiles['DataSet']= 'modules/DataSets/DataSet.php';
$beanFiles['ReportMaker']= 'modules/ReportMaker/ReportMaker.php';
//END SUGARCRM flav=ent ONLY

//BEGIN SUGARCRM flav=int ONLY
//$beanFiles['QueryBuilder']= 'modules/QueryBuilder/QueryBuilder.php';
//END SUGARCRM flav=int ONLY


// TODO: Remove the Library module, it is an example.
//$moduleList[] = 'Library';
//$beanList['Library']= 'Library';
//$beanFiles['Library'] = 'modules/Library/Library.php';

//BEGIN SUGARCRM flav=dce ONLY
$beanFiles['DCEAction']    = 'modules/DCEActions/DCEAction.php';
$beanFiles['DCEInstance']  = 'modules/DCEInstances/DCEInstance.php';
$beanFiles['DCETemplate']  = 'modules/DCETemplates/DCETemplate.php';
$beanFiles['DCECluster']   = 'modules/DCEClusters/DCECluster.php';
$beanFiles['DCEDataBase']   = 'modules/DCEDataBases/DCEDataBase.php';
$beanFiles['DCECronSchedule']   = 'modules/DCEInstances/DCECronSchedule.php';
$beanFiles['DCEReport']    = 'modules/DCEReports/DCEReport.php';
//END SUGARCRM flav=dce ONLY
$beanFiles['Configurator']          = 'modules/Configurator/Configurator.php';

// added these lists for security settings for tabs
$modInvisList = array('Administration', 'Currencies', 'CustomFields', 'Connectors',
    'Dropdown', 'Dynamic', 'DynamicFields', 'DynamicLayout', 'EditCustomFields',
    'Help', 'Import',  'MySettings', 'EditCustomFields','FieldsMetaData',
    'UpgradeWizard', 'Trackers', 'Connectors', 'Employees', 'Calendar',
    //BEGIN SUGARCRM flav=pro ONLY
    'Manufacturers','ProductBundles', 'ProductBundleNotes', 'ProductCategories', 'ProductTemplates', 'ProductTypes','Shippers', 'TaxRates', 'TeamNotices', 'Teams','TimePeriods','ForecastOpportunities','Quotas','KBDocumentRevisions','KBDocumentKBTags','KBTags','KBContents',
    //END SUGARCRM flav=pro ONLY
    'Releases','Sync',
    'Users',  'Versions', 'LabelEditor','Roles','EmailMarketing'
    ,'OptimisticLock', 'TeamMemberships', 'TeamSets', 'TeamSetModule', 'Audit', 'MailMerge', 'MergeRecords', 'EmailAddresses','EmailText',
    //BEGIN SUGARCRM flav=int ONLY
    'TeamHierarchy',
    //END SUGARCRM flav=int ONLY
    'Schedulers','Schedulers_jobs', /*'Queues',*/ 'EmailTemplates',
    //BEGIN SUGARCRM flav!=sales ONLY
    'CampaignTrackers', 'CampaignLog', 'EmailMan', 'Prospects', 'ProspectLists',
    //END SUGARCRM flav!=sales ONLY
    'Groups','InboundEmail',
    'ACLActions', 'ACLRoles',
    //BEGIN SUGARCRM flav!=sales ONLY
    'DocumentRevisions',
    //END SUGARCRM flav!=sales ONLY
    //BEGIN SUGARCRM flav=pro ONLY
    'ContractTypes', 'ForecastSchedule', 'Worksheet','ACLFields', 'ProjectResources', 'Holidays', 'SNIP',
    //END SUGARCRM flav=pro ONLY
    //BEGIN SUGARCRM flav=dce ONLY
    'DCEDataBases',
    //END SUGARCRM flav=dce ONLY
    //BEGIN SUGARCRM flav!=dce ONLY
    'ProjectTask',
    //END SUGARCRM flav!=dce ONLY
    //BEGIN SUGARCRM flav=sales ONLY
    'Emails',
    //END SUGARCRM flav=sales ONLY
    );
$adminOnlyList = array(
                    //module => list of actions  (all says all actions are admin only)
                   //'Administration'=>array('all'=>1, 'SupportPortal'=>'allow'),
                    'Dropdown'=>array('all'=>1),
                    'Dynamic'=>array('all'=>1),
                    'DynamicFields'=>array('all'=>1),
                    'Currencies'=>array('all'=>1),
                    'EditCustomFields'=>array('all'=>1),
                    'FieldsMetaData'=>array('all'=>1),
                    'LabelEditor'=>array('all'=>1),
                    'ACL'=>array('all'=>1),
                    'ACLActions'=>array('all'=>1),
                    'ACLRoles'=>array('all'=>1),
                    //BEGIN SUGARCRM flav=pro ONLY
                    'ACLFields'=>array('all'=>1),
                    //END SUGARCRM flav=pro ONLY
                    'UpgradeWizard' => array('all' => 1),
                    'Studio' => array('all' => 1),
                    'Schedulers' => array('all' => 1),
                    );

//BEGIN SUGARCRM flav=ent ONLY
$modInvisList[] = 'CustomQueries';
$modInvisList[] = 'DataSets';
$modInvisList[] = 'ReportMaker';
//END SUGARCRM flav=ent ONLY

//BEGIN SUGARCRM flav=pro ONLY
//$modInvisList[] = 'QueryBuilder';
$modInvisList[] = 'WorkFlow';
$modInvisList[] = 'WorkFlowTriggerShells';
$modInvisList[] = 'WorkFlowAlertShells';
$modInvisList[] = 'WorkFlowAlerts';
$modInvisList[] = 'WorkFlowActionShells';
$modInvisList[] = 'WorkFlowActions';
$modInvisList[] = 'Expressions';
$modInvisList[] = 'ACLFields';
//END SUGARCRM flav=pro ONLY
$modInvisList[] = 'ACL';
$modInvisList[] = 'ACLRoles';
$modInvisList[] = 'Configurator';
$modInvisList[] = 'UserPreferences';
$modInvisList[] = 'SavedSearch';
// deferred
//$modInvisList[] = 'Queues';
$modInvisList[] = 'Studio';
$modInvisList[] = 'Connectors';

$report_include_modules = array();
//BEGIN SUGARCRM flav!=dce ONLY
$report_include_modules['Currencies']='Currency';
//add prospects
$report_include_modules['Prospects']='Prospect';
$report_include_modules['DocumentRevisions'] = 'DocumentRevision';
$report_include_modules['ProductCategories'] = 'ProductCategory';
$report_include_modules['ProductTypes'] = 'ProductType';
//BEGIN SUGARCRM flav=pro ONLY
$report_include_modules['Contracts']='Contract';
//END SUGARCRM flav=pro ONLY
//END SUGARCRM flav!=dce ONLY
//add Tracker modules

//BEGIN SUGARCRM flav!=sales ONLY
$report_include_modules['Trackers']         = 'Tracker';

//END SUGARCRM flav!=sales ONLY

//BEGIN SUGARCRM flav=pro ONLY
$report_include_modules['TimePeriods'] = 'TimePeriod';
$report_include_modules['TrackerPerfs']     = 'TrackerPerf';
$report_include_modules['TrackerSessions']  = 'TrackerSession';
$report_include_modules['TrackerQueries']   = 'TrackerQuery';
$report_include_modules['Worksheet']    = 'Worksheet';
$report_include_modules['Quotas']    = 'Quota';
//END SUGARCRM flav=pro ONLY
//BEGIN SUGARCRM flav=dce ONLY
$report_include_modules['DCEReports']   = 'DCEReport';
$report_include_modules['DCEDataBases']   = 'DCEDataBase';
//END SUGARCRM flav=dce ONLY

$beanList['SugarFeed'] = 'SugarFeed';
$beanFiles['SugarFeed'] = 'modules/SugarFeed/SugarFeed.php';
$modInvisList[] = 'SugarFeed';

//BEGIN SUGARCRM flav=pro OR flav=sales ONLY
$beanList['Notifications'] = 'Notifications';
$beanFiles['Notifications'] = 'modules/Notifications/Notifications.php';
$modInvisList[] = 'Notifications';
//END SUGARCRM flav=pro OR flav=sales ONLY
// This is the mapping for modules that appear under a different module's tab
// Be sure to also add the modules to $modInvisList, otherwise their tab will still appear
$GLOBALS['moduleTabMap'] = array(
    'UpgradeWizard' => 'Administration',
    'EmailMan' => 'Administration',
    'ModuleBuilder' => 'Administration',
    'Configurator' => 'Administration',
    'Studio' => 'Administration',
    'Currencies' => 'Administration',
    'SugarFeed' => 'Administration',
    //BEGIN SUGARCRM flav!=sales ONLY
    'DocumentRevisions' => 'Documents',
    //END SUGARCRM flav!=sales ONLY
    'EmailTemplates' => 'Emails',
//BEGIN SUGARCRM flav=ent ONLY
    'DataSets' => 'ReportMaker',
    'CustomQueries' => 'ReportMaker',
//END SUGARCRM flav=ent ONLY
//BEGIN SUGARCRM flav!=sales ONLY
    'EmailMarketing' => 'Campaigns',
//END SUGARCRM flav!=sales ONLY
//BEGIN SUGARCRM flav=pro ONLY
    'Quotas' => 'Forecasts',
    'TeamNotices' => 'Teams',
//END SUGARCRM flav=pro ONLY
 );
$beanList['EAPM'] = 'EAPM';
$beanFiles['EAPM'] = 'modules/EAPM/EAPM.php';
$modules_exempt_from_availability_check['EAPM'] = 'EAPM';
$modInvisList[] = 'EAPM';
$beanList['OAuthKeys'] = 'OAuthKey';
$beanFiles['OAuthKey'] = 'modules/OAuthKeys/OAuthKey.php';
$modules_exempt_from_availability_check['OAuthKeys'] = 'OAuthKeys';
$modInvisList[] = 'OAuthKeys';
$beanList['OAuthTokens'] = 'OAuthToken';
$beanFiles['OAuthToken'] = 'modules/OAuthTokens/OAuthToken.php';
$modules_exempt_from_availability_check['OAuthTokens'] = 'OAuthTokens';
$modInvisList[] = 'OAuthTokens';

//BEGIN SUGARCRM flav=pro ONLY
$beanList['SugarFavorites'] = 'SugarFavorites';
$beanFiles['SugarFavorites'] = 'modules/SugarFavorites/SugarFavorites.php';
$modules_exempt_from_availability_check['SugarFavorites'] = 'SugarFavorites';
$modInvisList[] = 'SugarFavorites';
//END SUGARCRM flav=pro ONLY
//BEGIN SUGARCRM flav=following ONLY
$beanList['SugarFollowing'] = 'SugarFollowing';
$beanFiles['SugarFollowing'] = 'modules/SugarFollowing/SugarFollowing.php';
$modules_exempt_from_availability_check['SugarFollowing'] = 'SugarFollowing';
$modInvisList[] = 'SugarFollowing';
//END SUGARCRM flav=following ONLY


//Object list is only here to correct for modules that break
//the bean class name == dictionary entry/object name convention
//No future module should need an entry here.
$objectList = array();
$objectList['Cases'] =  'Case';
$objectList['Groups'] =  'User';
$objectList['Users'] =  'User';
//BEGIN SUGARCRM flav=pro ONLY
$objectList['TrackerSessions'] =  'tracker_sessions';
$objectList['TrackerPerfs'] =  'tracker_perf';
$objectList['TrackerQueries'] =  'tracker_queries';
$objectList['TeamNotices'] =  'TeamNotices';
//END SUGARCRM flav=pro ONLY

if (file_exists('include/modules_override.php'))
{
    include('include/modules_override.php');
}
if (file_exists('custom/application/Ext/Include/modules.ext.php'))
{
    include('custom/application/Ext/Include/modules.ext.php');
}
?>
