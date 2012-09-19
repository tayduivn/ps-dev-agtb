<?php
//FILE SUGARCRM flav=sales ONLY
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

class Build_Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $removed_directories;
	var $removed_modules;
	var $removed_db_tables;

    public function setUp()
    {
      $this->removed_directories = array();
      $this->removed_directories[] = 'modules/ACLFields';
      $this->removed_directories[] = 'modules/Bugs';
      $this->removed_directories[] = 'modules/Campaigns';
      $this->removed_directories[] = 'modules/CampaignLog';
      $this->removed_directories[] = 'modules/CampaignTrackers';
      $this->removed_directories[] = 'modules/Cases';
      $this->removed_directories[] = 'modules/Charts/Dashlets/CampaignROIChartDashlet';
      $this->removed_directories[] = 'modules/Charts/Dashlets/MyForecastingChartDashlet';
	  $this->removed_directories[] = 'modules/Connectors/connectors/filters/ext/rest/zoominfocompany';
	  $this->removed_directories[] = 'modules/Connectors/connectors/filters/ext/rest/zoominfoperson';
	  $this->removed_directories[] = 'modules/Connectors/connectors/sources/ext/rest/zoominfocompany';
	  $this->removed_directories[] = 'modules/Connectors/connectors/sources/ext/rest/zoominfoperson';
	  $this->removed_directories[] = 'modules/Contracts';
      $this->removed_directories[] = 'modules/ContractTypes';
      $this->removed_directories[] = 'modules/CustomQueries';
      $this->removed_directories[] = 'modules/DataSets';
      $this->removed_directories[] = 'modules/EmailMarketing';
      $this->removed_directories[] = 'modules/Expressions';
      $this->removed_directories[] = 'modules/Forecasts';
      $this->removed_directories[] = 'modules/ForecastSchedule';
      $this->removed_directories[] = 'modules/Groups';
      $this->removed_directories[] = 'modules/Holidays';
      $this->removed_directories[] = 'modules/KBContents';
      $this->removed_directories[] = 'modules/KBDocumentKBTags';
      $this->removed_directories[] = 'modules/KBDocumentRevisions';
      $this->removed_directories[] = 'modules/KBDocuments';
      $this->removed_directories[] = 'modules/KBTags';
      $this->removed_directories[] = 'modules/Leads';
      $this->removed_directories[] = 'modules/Manufacturers';
      $this->removed_directories[] = 'modules/ProductBundleNotes';
      $this->removed_directories[] = 'modules/ProductBundles';
      $this->removed_directories[] = 'modules/ProductCategories';
      $this->removed_directories[] = 'modules/Products';
      $this->removed_directories[] = 'modules/ProductTemplates';
      $this->removed_directories[] = 'modules/ProductTypes';
      $this->removed_directories[] = 'modules/Project';
      $this->removed_directories[] = 'modules/ProjectResources';
      $this->removed_directories[] = 'modules/ProjectTask';
      $this->removed_directories[] = 'modules/Prospects';
      $this->removed_directories[] = 'modules/ProspectLists';
      $this->removed_directories[] = 'modules/Quotas';
      $this->removed_directories[] = 'modules/Quotes';
      $this->removed_directories[] = 'modules/Releases';
      $this->removed_directories[] = 'modules/ReportMaker';
      $this->removed_directories[] = 'modules/Shippers';
      $this->removed_directories[] = 'modules/Sync';
      $this->removed_directories[] = 'modules/TaxRates';
      $this->removed_directories[] = 'modules/TeamNotices';
      $this->removed_directories[] = 'modules/Teams';
      $this->removed_directories[] = 'modules/TimePeriods';
      $this->removed_directories[] = 'modules/Trackers/Dashlets';
      $this->removed_directories[] = 'modules/WorkFlow';
      $this->removed_directories[] = 'modules/WorkFlowActions';
      $this->removed_directories[] = 'modules/WorkFlowActionShells';
      $this->removed_directories[] = 'modules/WorkFlowAlerts';
      $this->removed_directories[] = 'modules/WorkFlowAlertShells';
      $this->removed_directories[] = 'modules/WorkFlowTriggerShells';
      $this->removed_directories[] = 'include/workflow';
      $this->removed_directories[] = 'portal';

      $this->removed_modules = array();
	  $this->removed_modules[] = 'Leads';
	  $this->removed_modules[] = 'Campaigns';
	  $this->removed_modules[] = 'Quotes';
	  $this->removed_modules[] = 'Products';
	  $this->removed_modules[] = 'Forecasts';
	  $this->removed_modules[] = 'Contracts';
	  $this->removed_modules[] = 'KBDocuments';
	  $this->removed_modules[] = 'Cases';
	  $this->removed_modules[] = 'Bugs';

	  $this->removed_db_tables = array();
	  $this->removed_db_tables[] = 'cases';
	  $this->removed_db_tables[] = 'cases_audit';
	  $this->removed_db_tables[] = 'cases_bugs';
	  $this->removed_db_tables[] = 'contacts_bugs';
	  $this->removed_db_tables[] = 'contacts_cases';
	  $this->removed_db_tables[] = 'email_marketing_prospect_list';
	  $this->removed_db_tables[] = 'leads';
	  $this->removed_db_tables[] = 'leads_audit';
	  $this->removed_db_tables[] = 'project';
	  $this->removed_db_tables[] = 'project_resources';
	  $this->removed_db_tables[] = 'project_task';
	  $this->removed_db_tables[] = 'projects_accounts';
	  $this->removed_db_tables[] = 'projects_bugs';
	  $this->removed_db_tables[] = 'projects_cases';
	  $this->removed_db_tables[] = 'projects_contacts';
	  $this->removed_db_tables[] = 'projects_opportunities';
	  $this->removed_db_tables[] = 'projects_products';
	  $this->removed_db_tables[] = 'projects_quotes';
	  $this->removed_db_tables[] = 'products';
	  $this->removed_db_tables[] = 'products_audit';
	  $this->removed_db_tables[] = 'product_bundle_note';
      $this->removed_db_tables[] = 'product_bundle_product';
      $this->removed_db_tables[] = 'product_bundle_quote';
      $this->removed_db_tables[] = 'product_categories';
      $this->removed_db_tables[] = 'product_product';
	  $this->removed_db_tables[] = 'prospects';
	  $this->removed_db_tables[] = 'prospect_lists';
	  $this->removed_db_tables[] = 'prospect_list_campaigns';
	  $this->removed_db_tables[] = 'prospect_lists_prospects';
	  $this->removed_db_tables[] = 'quotes';
      $this->removed_db_tables[] = 'quotes_accounts';
      $this->removed_db_tables[] = 'quotes_audit';
      $this->removed_db_tables[] = 'quotes_contacts';
      $this->removed_db_tables[] = 'quotes_opportunities';
      $this->removed_db_tables[] = 'tracker_sessions';
      $this->removed_db_tables[] = 'tracker_perf';
      $this->removed_db_tables[] = 'tracker_queries';
      $this->removed_db_tables[] = 'tracker_tracker_queries';
    }

    public function tearDown()
    {

    }

    function test_directories_removed() {
      foreach($this->removed_directories as $dir) {
         $this->assertTrue(!file_exists($dir), 'Asserting that directory ' . $dir . ' is removed.');
      }
    }

    function test_modules_removed() {
      require('include/modules.php');
      foreach($this->removed_modules as $mod) {
      	 $this->assertTrue(!in_array($mod, $moduleList), 'Asserting that ' . $mod . ' is not in \$moduleList Array');
      }
    }

    function test_db_tables_removed() {
      $db = DBManagerFactory::getInstance();
      foreach($this->removed_db_tables as $table) {
      	 $this->assertTrue(!$db->tableExists($table), 'Asserting that ' . $table . ' is not built.');
      }
    }
}