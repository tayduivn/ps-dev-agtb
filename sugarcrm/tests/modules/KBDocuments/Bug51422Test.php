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

require_once "modules/KBDocuments/SearchUtils.php";

/*
 * This test calls SearchUtils.php::create_fts_search_list_query() with blank FTS parameters to test that the query produced
 * does not have the FTS clause.  If allowed to run, an FTS (Full Text Search) query with blank, single wildcard ('*') or no parameters will always return nothing
 * which is confusing to the user since they are used to blank meaning return all.
 * @ticket 51422
 */
class  Bug51422Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $fullQuerySoap;
    private $searchVars;
    private $spec_SearchVars;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        //sets the full query flag to true, so the query is returned.  This is whats used by portal
        $this->fullQuerySoap = true;
        //set the parameters for the where clause, to simulate a regular string text search
        $this->searchVars = array('is_external_article' => array ('operator' => '=','filter' => 1,),  'status_id' => array ('operator' => '=','filter' => 'Published',),  'kbdocument_name' => 'Foooey',);
        //Set the FTS parameters to an empty array.  This is key to the test
        $this->spec_SearchVars = array();


	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($fullQuerySoap);
        unset($searchVars);
        unset($spec_SearchVars);
    }


    
    public function testBlankFtsSearchListQuery()
    {

        //use the soap/portal approach to retrieve the list query
        //if the fts statement was included (incorrectly), there will be an error produced from $GLOBALS['db']->getFulltextQuery();
        $listQuery = create_fts_search_list_query($GLOBALS['db'], $this->spec_SearchVars,$this->searchVars,$this->fullQuerySoap);

        //lets assert that the query is not empty and is a string not an array.  Just the fact that it was returned means the
        //FTS query was left out and did not produce a php error
        $this->assertNotEmpty($listQuery, 'There was an error generating the list query from SearchUtils.php::create_fts_search_list_query() ');
        //assertType is deprecated, so use assertEquals
        $this->assertEquals('string', gettype($listQuery), 'There was an error generating the list query from SearchUtils.php::create_fts_search_list_query() ');



        //now lets retest but simulate the request coming from the app UI (without the full query boolean defined)
        //again, if the fts statement was included (incorrectly), there will be an error produced from $GLOBALS['db']->getFulltextQuery(); and this test will fail
        $listQueryArray = create_fts_search_list_query($GLOBALS['db'], $this->spec_SearchVars,$this->searchVars);

        //lets assert that the query array returned is in fact an array and is not empty Just the fact that it was returned means the
        //FTS query was left out and did not produce a php error
        $this->assertNotEmpty($listQueryArray, 'There was an error generating the list query from SearchUtils.php::create_fts_search_list_query() ');
        //assertType is deprecated, so use assertEquals
        $this->assertEquals('array', gettype($listQueryArray), 'There was an error generating the list query from SearchUtils.php::create_fts_search_list_query() ');
    }

}
