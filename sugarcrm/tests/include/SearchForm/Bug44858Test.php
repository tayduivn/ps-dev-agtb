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

require_once "modules/Accounts/Account.php";
require_once "include/Popups/PopupSmarty.php";
require_once "include/SearchForm/SearchForm2.php";

class Bug44858Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        //$this->useOutputBuffering = true;
	}

	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
	}
    
    /**
     * @ticket 44858
     */
    public function testGeneratedWhereClauseDoesNotHaveValueOfFieldNotSetInSearchForm()
    {
        //test to check that if value of a dropdown field is already set in REQUEST object (from any form such as mass update form instead of search form)
        //i.e. search is made on empty string, but REQUEST object gets value of that dropdown field from some other form on the same page
        //then on clicking serach button, value of that field should not be used as filter in where clause
        $this->markTestIncomplete('This test should actually check that the $whereArray is indeed populated');
        return;
        
    	//array to simulate REQUEST object
    	$requestArray['module'] = 'Accounts';
    	$requestArray['action'] = 'index';
    	$requestArray['searchFormTab'] = 'basic_search';
    	$requestArray['account_type'] = 'Analyst'; //value of a dropdown field set in REQUEST object
    	$requestArray['query'] = 'true';
    	$requestArray['button']  = 'Search';
    	$requestArray['globalLinksOpen']='true';
    	$requestArray['current_user_only_basic'] = 0;
    	
    	$account = SugarTestAccountUtilities::createAccount();
    	$searchForm = new SearchForm($account,'Accounts');
    	
    	require 'modules/Accounts/vardefs.php';
    	require 'modules/Accounts/metadata/SearchFields.php';
    	require 'modules/Accounts/metadata/searchdefs.php';
        $searchForm->searchFields = $searchFields[$searchForm->module]; 
        $searchForm->searchdefs = $searchdefs[$searchForm->module];                          
    	$searchForm->populateFromArray($requestArray,'basic_search',false);
    	$whereArray = $searchForm->generateSearchWhere(true, $account->module_dir);
    	//echo var_export($whereArray, true);
    	$this->assertEquals(0, count($whereArray));

    }
}
