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

require_once "modules/Tasks/Task.php";
//BEGIN SUGARCRM flav=pro ONLY
require_once "modules/Teams/Team.php";
//END SUGARCRM flav=pro ONLY
require_once "modules/Contacts/Contact.php";
require_once "include/SearchForm/SearchForm2.php";

/**
 * 
 * Test checks if SearchDef with 'force_unifiedsearch' => true concatenates the db_field array properly,
 * when the search value is a multiple word term (contains space between the words)
 * 
 * @author snigam@sugarcrm.com, avucinic@sugarcrm.com
 *
 */
class Bug45709_53785_Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $task = null;
	var $contact = null;
	var $team = null;
	var $requestArray = null;
	var $searchForm = null;

    public function setUp()
    {
		SugarTestHelper::setUp('app_list_strings');
		SugarTestHelper::setUp('app_strings');
		SugarTestHelper::setUp('current_user');
		
		$this->contact = SugarTestContactUtilities::createContact();
    	$this->task = SugarTestTaskUtilities::createTask();
    	$this->task->contact_id = $this->contact->id;
    	$this->task->save();
        //BEGIN SUGARCRM flav=pro ONLY
        $this->team = SugarTestTeamUtilities::createAnonymousTeam();
    	$this->team->name = 'Test';
    	$this->team->name_2 = 'Team';
    	$this->team->save();
        //END SUGARCRM flav=pro ONLY
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        //BEGIN SUGARCRM flav=pro ONLY
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        //END SUGARCRM flav=pro ONLY
        SugarTestHelper::tearDown();
    }

    /**
     * @ticket 45709
     */
    public function testGenerateSearchWhereForFieldsWhenFullContactNameGiven()
    {
    	// Array to simulate REQUEST object
    	$this->requestArray['module'] = 'Tasks';
    	$this->requestArray['action'] = 'index';
    	$this->requestArray['searchFormTab'] = 'advanced_search';
    	$this->requestArray['contact_name_advanced'] = $this->contact->first_name . " " . $this->contact->last_name; //value of a contact name field set in REQUEST object
    	$this->requestArray['query'] = 'true';

		// Initialize search form
    	$this->searchForm = new SearchForm($this->task, 'Tasks');

    	// Load the vardefs and search metadata
    	require 'modules/Tasks/vardefs.php';
    	require 'modules/Tasks/metadata/SearchFields.php';
    	require 'modules/Tasks/metadata/searchdefs.php';
        $this->searchForm->searchFields = $searchFields[$this->searchForm->module];
        $this->searchForm->searchdefs = $searchdefs[$this->searchForm->module];
        $this->searchForm->fieldDefs = $this->task->getFieldDefinitions();
        
        // Fill the data from the array we are using to simulate REQUEST
    	$this->searchForm->populateFromArray($this->requestArray,'advanced_search',false);
    	
    	// Get the generated search clause
    	$whereArray = $this->searchForm->generateSearchWhere(true, $this->task->module_dir);
    	
    	// And use it to load the contact created
    	$test_query = "SELECT id FROM contacts WHERE " . $whereArray[0];
    	$result = $GLOBALS['db']->query($test_query);
    	$row = $GLOBALS['db']->fetchByAssoc($result);
    	
    	// Check if the contact was successfully loaded
    	$this->assertEquals($this->contact->id, $row['id'], "Didn't find the correct contact id");

    	// Load the task using the contact_id we got from the previous query
    	$result2 = $GLOBALS['db']->query("SELECT * FROM tasks WHERE tasks.contact_id='{$this->task->contact_id}'");
        $row2 = $GLOBALS['db']->fetchByAssoc($result2);
        
    	// Check if the task is loaded properly	
        $this->assertEquals($this->task->id, $row2['id'], "Couldn't find the expected related task");
    }

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * @ticket 53785
     */
    public function testGenerateSearchWhereForFieldsWhenFullTeamNameGiven()
    {
    	// Array to simulate REQUEST object
    	$this->requestArray['module'] = 'Teams';
    	$this->requestArray['action'] = 'index';
    	$this->requestArray['searchFormTab'] = 'basic_search';
    	$this->requestArray['name_basic'] = $this->team->name . " " . $this->team->name_2; //value of team name field set in REQUEST object
    	$this->requestArray['query'] = 'true';

		// Initialize search form
    	$this->searchForm = new SearchForm($this->team, 'Teams');

    	// Load the vardefs and search metadata
    	require 'modules/Teams/vardefs.php';
    	require 'modules/Teams/metadata/SearchFields.php';
    	require 'modules/Teams/metadata/searchdefs.php';
        $this->searchForm->searchFields = $searchFields[$this->searchForm->module];
        $this->searchForm->searchdefs = $searchdefs[$this->searchForm->module];
        $this->searchForm->fieldDefs = $this->team->getFieldDefinitions();
        
        // Fill the data from the array we are using to simulate REQUEST
    	$this->searchForm->populateFromArray($this->requestArray, 'basic_search', false);
    	
    	// Get the generated search clause
    	$whereArray = $this->searchForm->generateSearchWhere(true, $this->team->module_dir);
    	
    	// And use it to load the team created
    	$test_query = "SELECT id FROM teams WHERE " . $whereArray[0];
    	$result = $GLOBALS['db']->query($test_query);
    	$row = $GLOBALS['db']->fetchByAssoc($result);

    	// Check if the team was successfully loaded
    	$this->assertEquals($this->team->id, $row['id'], "Didn't find the correct team id");
    }
    //END SUGARCRM flav=pro ONLY
}
