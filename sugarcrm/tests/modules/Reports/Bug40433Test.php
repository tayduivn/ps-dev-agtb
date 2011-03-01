<?php
// FILE SUGARCRM flav=pro ONLY 
require_once('modules/Reports/Report.php');

/**
 * Bug40433Test.php
 * This unit test attempts to simulate a row/column report against the Contract module to select
 * the currency_value and currency_value_usd fields.  What happened is that the code in Report.php
 * (create_query method) was not correctly re-creating the select_fields Array values when applying
 * the mssql specific formatting to certain fields in the query.
 * 
 * @author clee
 *
 */
class Bug40433Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $reportInstance;
    
	public function setUp() 
    {
    	$beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;
		$this->reportInstance = new Report();
		$this->dbType = $this->reportInstance->db->dbType;
		//force test to simulate mssql
		$this->reportInstance->db->dbType = 'mssql';	    
	    $this->reportInstance->from = "\n FROM CONTRACTS ";
	}
	
	public function tearDown()
	{
	    $this->reportInstance->db->dbType = $this->dbType;	
		$this->reportInstance = null;
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
	}

	/**
	 * test_create_query
	 * This method simulates a row/column report for the contracts module.  We are attempting to select
	 * the contract_value, contract_id and contract_value_us_dollar
	 */	
	function test_create_query()
	{
		$this->reportInstance->select_fields = array(0=>'contracts.id primaryid',
													 1=>'contracts.name contracts_name',
													 2=>'contracts.status contracts_status',
													 3=>'contracts.total_contract_value CONTRACTS_TOTAL_CONTRA1E104D , contracts.currency_id CONTRACTS_TOTAL_CONTRAE75D5E',
													 4=>'contracts.total_contract_value_us_dollar CONTRACTS_TOTAL_CONTRA5A324D',											 
	    );		
		$this->reportInstance->create_query('query', 'select_fields');
		$this->assertTrue(isset($this->reportInstance->select_fields[3]), "Assert that we have preserved the select argument");
		preg_match('/total_contract_value/', $this->reportInstance->select_fields[3], $matches);
		$this->assertTrue(isset($matches[0]) && $matches[0] == 'total_contract_value', "Assert that the contract_value select statement is preserved");
		preg_match('/currency_id/', $this->reportInstance->select_fields[3], $matches);
		$this->assertTrue(isset($matches[0]) && $matches[0] == 'currency_id', "Assert that the currency_id select statement is preserved");
		$this->assertTrue(isset($this->reportInstance->select_fields[4]), "Assert that we have preserved the select argument");
		preg_match('/total_contract_value/', $this->reportInstance->select_fields[4], $matches);
		$this->assertTrue(isset($matches[0]) && $matches[0] == 'total_contract_value', "Assert that the contract_value select statement is preserved");
		$this->assertTrue(isset($matches[0]) && $matches[0] == 'total_contract_value');
	}

	/**
	 * test_create_query2
	 * This is similar to test_create_query except that the [3] element has the currency_id and currency_value positions swapped
	 */
	function test_create_query2()
	{
		$this->reportInstance->select_fields = array(0=>'contracts.id primaryid',
													 1=>'contracts.name contracts_name',
													 2=>'contracts.status contracts_status',
													 3=>'contracts.currency_id CONTRACTS_TOTAL_CONTRAE75D5E, contracts.total_contract_value CONTRACTS_TOTAL_CONTRA1E104D',
													 4=>'contracts.total_contract_value_us_dollar CONTRACTS_TOTAL_CONTRA5A324D',											 
	    );		
		$this->reportInstance->create_query('query', 'select_fields');
		$this->assertTrue(isset($this->reportInstance->select_fields[3]), "Assert that we have preserved the select argument");
		preg_match('/total_contract_value/', $this->reportInstance->select_fields[3], $matches);
		$this->assertTrue(isset($matches[0]) && $matches[0] == 'total_contract_value', "Assert that the contract_value select statement is preserved");
		preg_match('/currency_id/', $this->reportInstance->select_fields[3], $matches);
		$this->assertTrue(isset($matches[0]) && $matches[0] == 'currency_id', "Assert that the currency_id select statement is preserved");
		$this->assertTrue(isset($this->reportInstance->select_fields[4]), "Assert that we have preserved the select argument");
		preg_match('/total_contract_value/', $this->reportInstance->select_fields[4], $matches);
		$this->assertTrue(isset($matches[0]) && $matches[0] == 'total_contract_value', "Assert that the contract_value select statement is preserved");
		$this->assertTrue(isset($matches[0]) && $matches[0] == 'total_contract_value');
	}	

}