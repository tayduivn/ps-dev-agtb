<?php

require_once "modules/Opportunities/Opportunity.php";
require_once "modules/Accounts/Account.php";
require_once "include/SearchForm/SearchForm2.php";

class Bug45053Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $opportunity = null;
	var $account = null;
	var $requestArray = null;
	var $searchForm = null;
   
    public function setUp(){
    	$this->account = SugarTestAccountUtilities::createAccount();
    	$this->opportunity = new Opportunity();
    	$this->opportunity->name = 'Bug45053Test ' . time();
    	$this->opportunity->account_name = $this->account->name;
    	$this->opportunity->amount = 500;
    	$tomorrow = mktime(0,0,0,date("m"),date("d")+1,date("Y"));
    	$this->opportunity->date_closed = date("Y-m-d", $tomorrow);
    	$this->opportunity->sales_stage = "Prospecting";
    	
    	
    }
    
    public function tearDown()
    {
        
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $GLOBALS['db']->query('DELETE FROM opportunities WHERE id='.$this->opportunity->id );
    }
    
    /**
     * @ticket 45053
     */
    public function testPopulateFromArrayFunctionInSearchForm2ForFieldDefsArrayRelateFields()
    {
        //test that when request object has values for relate fields
        //and request is not coming from search button
        //then fieldDefs array has value for relate fields set from populateFromArray() function in SearchForm2.php
        
    	//array to simulate REQUEST object
    	$this->requestArray['module'] = 'Opportunities';
    	$this->requestArray['action'] = 'index';
    	$this->requestArray['searchFormTab'] = 'advanced_search';
    	$this->requestArray['sales_stage'] = 'Prospecting'; //value of a relate field set in REQUEST object
    	$this->requestArray['query'] = 'true';
    	
    	
    	$this->searchForm = new SearchForm($this->opportunity,'Opportunities');
    	
    	require 'modules/Opportunities/vardefs.php';
    	require 'modules/Opportunities/metadata/SearchFields.php';
    	require 'modules/Opportunities/metadata/Searchdefs.php';
        $this->searchForm->searchFields = $searchFields[$this->searchForm->module]; 
        $this->searchForm->searchdefs = $searchdefs[$this->searchForm->module]; 
        $this->searchForm->fieldDefs = $this->opportunity->getFieldDefinitions();                        
    	$this->searchForm->populateFromArray($this->requestArray,'advanced_search',false);
    	$test_sales_stage = $this->searchForm->fieldDefs['sales_stage_advanced']['value'];
    	echo $test_sales_stage;
    	$this->assertEquals($this->requestArray['sales_stage'], $test_sales_stage);

    }
}
