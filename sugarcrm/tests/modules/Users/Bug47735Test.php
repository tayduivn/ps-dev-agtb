<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once "include/SearchForm/SearchForm2.php";

class Bug47735Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $user = null;
	var $requestArray = null;
	var $searchForm = null;

    public function setUp()
    {
		$GLOBALS['current_user'] = $this->user = SugarTestUserUtilities::createAnonymousUser();

    }

    public function tearDown()
    {

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($GLOBALS['current_user']);
    }

    /**
     *verify that Users search metadata are set up correctly to create a concatenated search on full name from the
     * GenerateSearchWhere function in SearchForm2.php
     */
    public function testGenerateSearchWhereForUsesConcatenatedFullName()
    {
        require 'modules/Users/vardefs.php';
        require 'modules/Users/metadata/SearchFields.php';
        require 'modules/Users/metadata/searchdefs.php';

    	//array to simulate REQUEST object, this simulates a basic search using both the first and
        //last name of the newly created anonymous user
    	$this->requestArray['module'] = 'Users';
    	$this->requestArray['action'] = 'index';
    	$this->requestArray['searchFormTab'] = 'basic_search';
    	$this->requestArray['search_name_basic'] = $this->user->first_name. " ". $this->user->last_name;
    	$this->requestArray['query'] = 'true';

        //create new searchform. populate it's values and generate query
    	$this->searchForm = new SearchForm($this->user,'Users');
        $this->searchForm->searchFields = $searchFields[$this->searchForm->module];
        $this->searchForm->searchdefs = $searchdefs[$this->searchForm->module];
        $this->searchForm->fieldDefs = $this->user->getFieldDefinitions();
    	$this->searchForm->populateFromArray($this->requestArray,'basic_search',false);
    	$whereArray = $this->searchForm->generateSearchWhere(true, $this->user->module_dir);

        //use the where query to search for the user
    	$test_query = "SELECT id FROM users WHERE " . $whereArray[0];
    	$result = $GLOBALS['db']->query($test_query);
    	$row = $GLOBALS['db']->fetchByAssoc($result);

        //make sure row is not empty
        $this->assertEquals($this->user->id, $row['id'], "Did not retrieve any users using the following query: ".$test_query);

        //make sure retrieved correct user
    	$this->assertEquals($this->user->id, $row['id'], "The query returned records but not the correct one: ".$test_query);
    }
}
