<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once('modules/Reports/Report.php');

/**
 * Bug52796Test.php
 * This unit test attempts to simulate a row/column report against the Opportunities module to select
 * the amount and amount_usdollar fields, and check if amount_usdollar returned value is calculated
 * using the latest conversion rate for the users currency.
 *
 * @author avucinic
 *
 */
class Bug52796Test extends Sugar_PHPUnit_Framework_TestCase
{
	private $reportInstance;
	private $saved = array();

	public function setUp()
	{
        $this->markTestIncomplete("Disabling broken test, working with Andrija to fix");
		// Change default currency to check conversion
		global $sugar_config, $beanList, $beanFiles;
        require('include/modules.php');
		$this->saved['currency'] = $sugar_config['currency'];
		$currency = new Currency();
		$sugar_config['currency'] = $currency->retrieveIDBySymbol('€');
	}

	public function tearDown()
	{
		// Set back the default currency value
		global $sugar_config;
		$sugar_config['currency'] = $this->saved['currency'];
		$this->reportInstance = null;
		$this->saved = null;
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
	}

	/**
	 * testReportCurrencyConversion
	 * This method tests if conversion from dollar to another currency (Euro for tests) works after change of currency_rate
	 */
	function testReportCurrencyConversion()
	{	
		
		// Initialize an opportunities report with 3 columns
		$this->reportInstance = new Report();
		$this->reportInstance->clear_results();
		$this->reportInstance->from = "\n FROM opportunities ";
		$this->reportInstance->table_name = "opportunities";
		$this->reportInstance->focus = new Opportunity();
		
		// Report defs
		$this->reportInstance->report_def['display_columns'] = array (
			0 =>
			array (
			    'name' => 'name',
			    'label' => 'Opportunity Name',
			    'table_key' => 'self',
				'group_function' => '',
			),
			1 =>
			array (
			    'name' => 'amount',
			    'label' => 'Opportunity Amount',
			    'table_key' => 'self',
				'group_function' => '',
			),
			2 =>
			array (
			    'name' => 'amount_usdollar',
			    'label' => 'Amount',
			    'table_key' => 'self',
				'group_function' => '',
			),
		);
		
		// All fields
		$this->reportInstance->all_fields = array(
		'self:name' => array(
		  'name' => 'name',
			  'vname' => 'LBL_OPPORTUNITY_NAME',
			  'type' => 'name',
			  'dbType' => 'varchar',
			  'len' => '50',
			  'unified_search' => true,
			  'comment' => 'Name of the opportunity',
			  'merge_filter' => 'selected',
			  'importable' => 'required',
			  'required' => true,
			  'module' => 'Opportunities',
			  'real_table' => 'opportunities',
			  'rep_rel_name' => 'name_0',
			),
			'self:amount' => array (
			  'name' => 'amount',
			  'vname' => 'LBL_AMOUNT',
			  'type' => 'currency',
			  'dbType' => 'double',
			  'comment' => 'Unconverted amount of the opportunity',
			  'importable' => 'required',
			  'duplicate_merge' => '1',
			  'required' => true,
			  'options' => 'numeric_range_search_dom',
			  'enable_range_search' => true,
			  'module' => 'Opportunities',
			  'real_table' => 'opportunities',
			  'rep_rel_name' => 'amount_0',
			),
			'self:amount_usdollar' => array (
			  'name' => 'amount_usdollar',
			  'vname' => 'LBL_AMOUNT_USDOLLAR',
			  'type' => 'currency',
			  'group' => 'amount',
			  'dbType' => 'double',
			  'disable_num_format' => true,
			  'duplicate_merge' => '0',
			  'audited' => true,
			  'comment' => 'Formatted amount of the opportunity',
			  'studio' => 
			  array (
			    'wirelesseditview' => false,
			    'wirelessdetailview' => false,
			    'editview' => false,
			    'detailview' => false,
			    'quickcreate' => false,
			  ),
			  'module' => 'Opportunities',
			  'real_table' => 'opportunities',
			  'rep_rel_name' => 'amount_usdollar_0',
			)
		);
		
		// Report select fields
		$this->reportInstance->select_fields = array(
			0 => 'IFNULL(opportunities.id,\'\') primaryid',
			1 => 'IFNULL(opportunities.name,\'\') opportunities_name',
			2 => 'opportunities.amount opportunities_amount ',
			3 => 'IFNULL( opportunities.currency_id,\'\') OPPORTUNITIES_AMOUNT_C9AC638',
			4 => 'opportunities.amount_usdollar OPPORTUNITIES_AMOUNT_UBC8F31',
		);
		// Create and execute report query
		$this->reportInstance->create_query('query', 'select_fields');
		$this->reportInstance->execute_query('query');

		// Change the Euro currency conversion_rate
		$currency = new Currency();
		$currency->retrieve($currency->retrieveIDBySymbol('€'));
		$oldConversionRate = $currency->conversion_rate;
		$currency->conversion_rate = 0.5;
		$currency->save();
		
		// Loop through all results, and check if after conversion_rate change, the amounts are calculated properly
		while ($row = $this->reportInstance->get_next_row('result', 'display_columns')) {
			// Extract the amount in dollars from the first row and strip commas
			preg_match('/([0-9]+,)*[0-9]+\.[0-9]+/', $row['cells'][1], $matches);
			$dollars = str_replace(",", "", $matches[0]);
			
			// Extract the calculated amount in euros from the second row and strip commas
			preg_match('/([0-9]+,)*[0-9]+\.[0-9]+/', $row['cells'][2], $matches);
			$euros = str_replace(",", "", $matches[0]);
			
			$actual = $euros;
			$expected = $dollars * $currency->conversion_rate;
			$this->assertEquals($expected, $actual, "Reports are not processing the amount_usdollar field using latest conversion_rates.");
		}
		
		// Rollback the old conversion_rate after the test
		$currency->conversion_rate = $oldConversionRate;
		$currency->save();
	}
}
