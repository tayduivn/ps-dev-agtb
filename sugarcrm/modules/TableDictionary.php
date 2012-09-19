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
 * in the same form as they appear in the distribution.  See full license for includements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
// $Id: TableDictionary.php 53116 2009-12-10 01:24:37Z mitani $
//BEGIN SUGARCRM flav!=sales ONLY
include("metadata/accounts_bugsMetaData.php");
include("metadata/accounts_casesMetaData.php");
//END SUGARCRM flav!=sales ONLY
include("metadata/accounts_contactsMetaData.php");
include("metadata/accounts_opportunitiesMetaData.php");
include("metadata/calls_contactsMetaData.php");
include("metadata/calls_usersMetaData.php");
//BEGIN SUGARCRM flav!=sales ONLY
include("metadata/calls_leadsMetaData.php");
include("metadata/cases_bugsMetaData.php");
include("metadata/contacts_bugsMetaData.php");
include("metadata/contacts_casesMetaData.php");
//END SUGARCRM flav!=sales ONLY
include("metadata/configMetaData.php");
include("metadata/contacts_usersMetaData.php");
include("metadata/custom_fieldsMetaData.php");
include("metadata/email_addressesMetaData.php");
include("metadata/emails_beansMetaData.php");
include("metadata/foldersMetaData.php");
include("metadata/import_mapsMetaData.php");
include("metadata/meetings_contactsMetaData.php");
include("metadata/meetings_usersMetaData.php");
//BEGIN SUGARCRM flav!=sales ONLY
include("metadata/meetings_leadsMetaData.php");
//END SUGARCRM flav!=sales ONLY
include("metadata/opportunities_contactsMetaData.php");
include("metadata/user_feedsMetaData.php");
include("metadata/users_passwordLinkMetaData.php");
//BEGIN SUGARCRM flav=pro ONLY
include("metadata/team_sets_teamsMetaData.php");
include("metadata/tracker_perfMetaData.php");
include("metadata/tracker_queriesMetaData.php");
include("metadata/tracker_sessionsMetaData.php");
include("metadata/tracker_tracker_queriesMetaData.php");
//END SUGARCRM flav=pro ONLY
//BEGIN SUGARCRM flav!=sales ONLY
include("metadata/prospect_list_campaignsMetaData.php");
include("metadata/prospect_lists_prospectsMetaData.php");
//END SUGARCRM flav!=sales ONLY
include("metadata/roles_modulesMetaData.php");
include("metadata/roles_usersMetaData.php");
//include("metadata/project_relationMetaData.php");
include("metadata/outboundEmailMetaData.php");
include("metadata/addressBookMetaData.php");
//BEGIN SUGARCRM flav!=sales ONLY
include("metadata/project_bugsMetaData.php");
include("metadata/project_casesMetaData.php");
include("metadata/project_productsMetaData.php");
include("metadata/projects_accountsMetaData.php");
include("metadata/projects_contactsMetaData.php");
include("metadata/projects_opportunitiesMetaData.php");

//END SUGARCRM flav!=sales ONLY

//BEGIN SUGARCRM flav=pro || flav=sales ONLY
include("metadata/report_cache.php");
include("metadata/report_schedulesMetaData.php");
include("metadata/saved_reportsMetaData.php");
//END SUGARCRM flav=pro || flav=sales ONLY

//BEGIN SUGARCRM flav=pro ONLY
//BEGIN SUGARCRM flav!=sales ONLY
include("metadata/product_bundle_noteMetaData.php");
include("metadata/product_bundle_productMetaData.php");
include("metadata/product_bundle_quoteMetaData.php");
include("metadata/product_productMetaData.php");
include("metadata/quotes_accountsMetaData.php");
include("metadata/quotes_contactsMetaData.php");
include("metadata/quotes_opportunitiesMetaData.php");
include("metadata/products_categoryTreeMetaData.php");
include("metadata/fts_queueMetaData.php");
//END SUGARCRM flav=pro ONLY
include("metadata/workflow_schedulesMetaData.php");
include("metadata/schedulers_timesMetaData.php");
//BEGIN SUGARCRM flav=pro ONLY
include("metadata/contracts_opportunitiesMetaData.php");
include("metadata/contracts_contactsMetaData.php");
include("metadata/contracts_quotesMetaData.php");
include("metadata/contracts_productsMetaData.php");
include("metadata/projects_quotesMetaData.php");
include("metadata/kbdocuments_views_ratingsMetaData.php");
//END SUGARCRM flav!=sales ONLY
include("metadata/users_holidaysMetaData.php");

//END SUGARCRM flav=pro ONLY
//BEGIN SUGARCRM flav=ent ONLY

include("metadata/dataset_layoutsMetaData.php");
include("metadata/dataset_attributesMetaData.php");
//END SUGARCRM flav=ent ONLY
//ACL RELATIONSHIPS
include("metadata/acl_roles_actionsMetaData.php");
include("metadata/acl_roles_usersMetaData.php");
// INBOUND EMAIL
include("metadata/inboundEmail_autoreplyMetaData.php");
include("metadata/inboundEmail_cacheTimestampMetaData.php");
include("metadata/email_cacheMetaData.php");
//BEGIN SUGARCRM flav!=sales ONLY
include("metadata/email_marketing_prospect_listsMetaData.php");
//END SUGARCRM flav!=sales ONLY
include("metadata/users_signaturesMetaData.php");
//linked documents.
//BEGIN SUGARCRM flav!=sales ONLY
include("metadata/linked_documentsMetaData.php");
//END SUGARCRM flav!=sales ONLY
//BEGIN SUGARCRM flav=pro ONLY
include("metadata/sessionHistoryMetaData.php");
//END SUGARCRM flav=pro ONLY

// Documents, so we can start replacing Notes as the primary way to attach something to something else.
include("metadata/documents_accountsMetaData.php");
include("metadata/documents_contactsMetaData.php");
include("metadata/documents_opportunitiesMetaData.php");
include("metadata/documents_casesMetaData.php");
include("metadata/documents_bugsMetaData.php");
//BEGIN SUGARCRM flav=pro ONLY
include("metadata/documents_productsMetaData.php");
include("metadata/documents_quotesMetaData.php");
include("metadata/forecast_treeMetaData.php");
//END SUGARCRM flav=pro ONLY
include("metadata/oauth_nonce.php");

if(file_exists('custom/application/Ext/TableDictionary/tabledictionary.ext.php')){
	include('custom/application/Ext/TableDictionary/tabledictionary.ext.php');
}
?>
