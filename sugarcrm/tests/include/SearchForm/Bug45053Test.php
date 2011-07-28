<?php
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
//FILE SUGARCRM flav=pro ONLY
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
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE id='{$this->opportunity->id}'");
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
