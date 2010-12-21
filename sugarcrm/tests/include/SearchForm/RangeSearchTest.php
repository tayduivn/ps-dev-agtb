<?php

require_once 'modules/DynamicFields/templates/Fields/TemplateInt.php';
require_once 'modules/DynamicFields/templates/Fields/TemplateDate.php';
require_once 'include/SearchForm/SearchForm2.php';
require_once 'modules/Opportunities/Opportunity.php';

class RangeSearchTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $hasExistingCustomSearchFields = false;
    var $searchForm;
    var $originalDbType;
 
    public function setUp()
    {	
		if(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'))
		{
		   $this->hasExistingCustomSearchFields = true;
		   copy('custom/modules/Opportunities/metadata/SearchFields.php', 'custom/modules/Opportunities/metadata/SearchFields.php.bak');
		   unlink('custom/modules/Opportunities/metadata/SearchFields.php');
		} else if(!file_exists('custom/modules/Opportunities/metadata')) {
		   mkdir_recursive('custom/modules/Opportunities/metadata');
		}
		
    	//Setup Opportunities module and date_closed field
		$_REQUEST['view_module'] = 'Opportunities';
		$_REQUEST['name'] = 'date_closed';
		$templateDate = new TemplateDate();
		$templateDate->enable_range_search = true;
		$templateDate->populateFromPost();
		include('custom/modules/Opportunities/metadata/SearchFields.php');

		//Prepare SearchForm
    	$seed = new Opportunity();
    	$module = 'Opportunities';
		$this->searchForm = new SearchForm($seed, $module);
		$this->searchForm->searchFields = array(
			'range_date_closed' => array
	        (
	            'query_type' => 'default',
	            'enable_range_search' => 1,
	            'is_date_field' => 1,
	            'value' => '[this_year]',
	            'operator' => 'this_year',
	        ),
	        'start_range_date_closed' => array
	        (
	            'query_type' => 'default',
	            'range_operator' => 'between',
	            'enable_range_search' => 1,
	            'is_date_field' => 1,
	        ),
	        'end_range_date_closed' => array
	        (
	            'query_type' => 'default',
	            'enable_range_search' => 1,
	            'is_date_field' => 1,
	        )
		);		
		
		$this->originalDbType = $GLOBALS['db']->dbType;
    }
    
    public function tearDown()
    {		
		$GLOBALS['db']->dbType = $this->originalDbType;
		
    	if(!$this->hasExistingCustomSearchFields)
		{
		   unlink('custom/modules/Opportunities/metadata/SearchFields.php');
		}    	
    	
		if(file_exists('custom/modules/Opportunities/metadata/SearchFields.php.bak')) {
		   copy('custom/modules/Opportunities/metadata/SearchFields.php.bak', 'custom/modules/Opportunities/metadata/SearchFields.php');
		   unlink('custom/modules/Opportunities/metadata/SearchFields.php.bak');
		}

    }
    
    public function testRangeSearchMysql()
    {
		$GLOBALS['db']->dbType = 'mysql';
		$where_clauses = $this->searchForm->generateSearchWhere();
		$this->assertEquals($where_clauses[0], 'LEFT(opportunities.date_closed,4) = EXTRACT(YEAR FROM ( current_date ))');
		
		$this->searchForm->searchFields['range_date_closed'] = array (
	            'query_type' => 'default',
	            'enable_range_search' => 1,
	            'is_date_field' => 1,
	            'value' => '[next_year]',
	            'operator' => 'next_year',
	    );

		$where_clauses = $this->searchForm->generateSearchWhere();		
		$this->assertEquals($where_clauses[0], 'LEFT(opportunities.date_closed,4) = EXTRACT(YEAR FROM ( current_date  + interval \'1\' year))');
    } 
    
    public function testRangeSearchMssql()
    {
		$GLOBALS['db']->dbType = 'mssql';
		$where_clauses = $this->searchForm->generateSearchWhere();
		$this->assertEquals($where_clauses[0], 'DATEPART(yy,opportunities.date_closed) = DATEPART(yy, GETDATE())');

		$this->searchForm->searchFields['range_date_closed'] = array (
	            'query_type' => 'default',
	            'enable_range_search' => 1,
	            'is_date_field' => 1,
	            'value' => '[next_year]',
	            'operator' => 'next_year',
	    );

		$where_clauses = $this->searchForm->generateSearchWhere();	
		$this->assertEquals($where_clauses[0], 'DATEPART(yy,opportunities.date_closed) = DATEPART(yy,( dateadd(yy, 1,GETDATE())))');
		
    }       

    public function testRangeSearchOracle()
    {
		$GLOBALS['db']->dbType = 'oci8';
		$where_clauses = $this->searchForm->generateSearchWhere();
		$this->assertEquals($where_clauses[0], 'TRUNC(opportunities.date_closed,\'YEAR\') = TRUNC( sysdate,\'YEAR\')');

		$this->searchForm->searchFields['range_date_closed'] = array (
	            'query_type' => 'default',
	            'enable_range_search' => 1,
	            'is_date_field' => 1,
	            'value' => '[next_year]',
	            'operator' => 'next_year',
	    );

		$where_clauses = $this->searchForm->generateSearchWhere();	
		$this->assertEquals($where_clauses[0], 'TRUNC(opportunities.date_closed,\'YEAR\') = TRUNC(add_months(sysdate,+12),\'YEAR\')');
		
    } 
}
?>