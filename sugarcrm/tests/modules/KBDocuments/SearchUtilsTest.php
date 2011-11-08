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

class SearchUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    /**
     * @group bug41574
     */
    public function testGetQSAuthorUsesPassedFormname()
    {
        $qsArray = getQSAuthor('testme');
        
        $this->assertEquals($qsArray['form'],'testme');
    }
    
    /**
     * @group bug41574
     */
    public function testGetQSApproverUsesPassedFormname()
    {
        $qsArray = getQSApprover('testme');
        
        $this->assertEquals($qsArray['form'],'testme');
    }
    
    /**
     * @group bug41574
     */
    public function testGetQSTagsUsesPassedFormname()
    {
        $qsArray = getQSTags('testme');
        
        $this->assertEquals($qsArray['form'],'testme');
    }


    /**
     * createFtsSearchListQueryProvider
     *
     * This is the data provider for the testCreateFtsSearchListQuery function
     */
    public function createFtsSearchListQueryProvider()
    {
        return array
        (
            //Simulate a search with text = '*', kbdocument_name = 'Draft' and 'status_id' = 'Draft'
            array(
                'spec_SearchVars' => array
                (
                    'active_date' => '',
                    'active_date2' => '',
                    'exp_date' => '',
                    'exp_date2' => '',
                    'searchText_include' => '*',
                    'canned_search' => 'all',
                ),
                'searchVars' => array
                (
                    'kbdocument_name' => 'Draft',
                    'status_id' => array
                     (
                         'operator' => '=',
                         'filter' => 'Draft',
                     ),
                ),
                true
            ),

            //Simulate a search with text = '*' and 'status_id' = 'Draft'
            array(
                'spec_SearchVars' => array
                (
                    'active_date' => '',
                    'active_date2' => '',
                    'exp_date' => '',
                    'exp_date2' => '',
                    'searchText_include' => '*',
                    'canned_search' => 'all',
                ),
                'searchVars' => array
                (
                    'status_id' => array
                     (
                         'operator' => '=',
                         'filter' => 'Draft',
                     ),
                ),
                false
            ),
        );
    }


    /**
     * testCreateFtsSearchListQuery
     *
     * These are some additional tests on the create_fts_search_list_query function.  We use the createFtsSearchListQueryProvider
     * method to supply simulated arguments for the $spec_SearchVars and $searchVars input.
     *
     * @dataProvider createFtsSearchListQueryProvider
     * @see modules/KBDocuments/SearchUtils.php
     * 
     * @param $spec_SearchVars Array of special arguments for KBDocuments module
     * @param $searchVars Array of arguments sent from search form to KBDocuments module
     * @param $hasAnd boolean value indicating whether or not to search for the 'and' string in resulting where query
     */
    public function testCreateFtsSearchListQuery($spec_SearchVars, $searchVars, $hasAnd)
    {
        $result = create_fts_search_list_query($GLOBALS['db'], $spec_SearchVars, $searchVars);
        if($hasAnd)
        {
            $this->assertRegExp('/ and /', $result['where'], 'Assert and clause found');
        } else {
            $this->assertNotRegExp('/ and /', $result['where'], 'Assert no and clause found');
        }
    }

}
