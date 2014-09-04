<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
 
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

    /**
     * Testing that range uses datetime instead of date format
     * @see CRYS-454
     */
    public function testReturnDateFilterOnRange()
    {
        $db = $this->getMock(get_class(DBManagerFactory::getInstance()), array('convert'));
        $db->expects($this->exactly(2))->method('convert')->with($this->matchesRegularExpression('/^\'\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\'$/'), $this->equalTo('datetime'));
        return_date_filter($db, 'test', 'last_30_days');
    }

    /**
     * Testing that range uses datetime instead of date format
     * @see CRYS-454
     */
    public function testReturnCannedQueryAdded()
    {
        $db = $this->getMock(get_class(DBManagerFactory::getInstance()), array('convert'));
        $db->expects($this->exactly(2))->method('convert')->with($this->matchesRegularExpression('/^\'\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\'$/'), $this->equalTo('datetime'));
        return_canned_query($db, 'added');
    }

    /**
     * Testing that range uses datetime instead of date format
     * @see CRYS-454
     */
    public function testReturnCannedQueryUpdated()
    {
        $db = $this->getMock(get_class(DBManagerFactory::getInstance()), array('convert'));
        $db->expects($this->exactly(2))->method('convert')->with($this->matchesRegularExpression('/^\'\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\'$/'), $this->equalTo('datetime'));
        return_canned_query($db, 'updated');
    }
}
