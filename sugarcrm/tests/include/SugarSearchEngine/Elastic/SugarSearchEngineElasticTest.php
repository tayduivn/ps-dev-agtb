<?php
//FILE SUGARCRM flav=pro ONLY
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


require_once 'include/SugarSearchEngine/Elastic/SugarSearchEngineElastic.php';

class SugarSearchEngineElasticTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('app_list_strings');
        // create a Bean..doesn't need to be saved
        $this->bean = BeanFactory::newBean('Accounts');
        $this->bean->id = create_guid();
        $this->bean->name = 'Test';
        $this->bean->assigned_user_id = create_guid();
        
        if (empty($this->_db)) {
            $this->_db = DBManagerFactory::getInstance();
        }
        
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact = SugarTestContactUtilities::createContact();
    }
    
    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function providerQueryStringData()
    {
        return array(
            array('abc', true),
            array('abc def', true),
            array("abc[10 TO 20]", false),
            array('{10 TO 20}abc', false),
            array('"abc"', false),
            array('abc~', false),
            array('accounts:abc', true),
            array('abc*', false),
            );
    }

    /**
     * @dataProvider providerQueryStringData
     */
    public function testCanAppendWildcard($queryString, $canAppend)
    {
        $queryString = html_entity_decode($queryString);

        $stub = new SugarSearchEngineElasticTestStub();
        $result = $stub->canAppendWildcard($queryString);

        $this->assertEquals($canAppend, $result, 'Expect different value from canAppendWildcard()');
    }


    public function testCreateIndexDocument() {
        $stub = new SugarSearchEngineElasticTestStub();
        $document = $stub->createIndexDocument($this->bean);
        $data = $document->getData();
        $this->assertEquals($this->bean->assigned_user_id, $data['doc_owner']);
        
    }
    
    public function searchProvider()
    {
        return array(
            //array(array('moduleFilter' => array('Calls'), 'addSearchBoosts' => true)),
            //array(array('moduleFilter' => array('Accounts'), 'addSearchBoosts' => true)),
            array(array('moduleFilter' => array('Contacts'), 'addSearchBoosts' => true)),
        );
    }
    
    /**
     * @dataProvider searchProvider
     */
    public function testSearch($options)
    {
        $stub = new SugarSearchEngineElasticTestStub();
        $searchTerm = 'sk';
        $offset = 0;
        $limit = 20;
        $searchResult = $stub->search($searchTerm, $offset, $limit, $options);
        $msg = "search() returned NULL, expected SugarSeachEngineElasticResultSet";
        $this->assertNotNull($searchResult, $msg);
    }
    
    
    public function searchFieldOptionsProvider()
    {
        return array(
            array(array('moduleFilter' => array(), 'addSearchBoosts' => true), false),
            array(array('moduleFilter' => array('Accounts'), 'addSearchBoosts' => true), false),
            array(array('moduleFilter' => array('Administration'), 'addSearchBoosts' => true), true),
        );
    }
    
    /** 
     * @dataProvider searchFieldOptionsProvider
     */
    public function testGetSearchFields($options, $emptyListExpected)
    {
        $stub = new SugarSearchEngineElasticTestStub();
        $fields = $stub->getSearchFields($options);
        $emptyFieldsList = empty($fields);
        $moduleName = empty($options['moduleFilter']) ? 'no module filter' : $options['moduleFilter'][0];
        $emptyState = $emptyListExpected ? 'empty' : 'populated';
        $this->assertEquals($emptyFieldsList, $emptyListExpected, "Expected $emptyState field list for $moduleName.");
    }
    
    
    public function testGetSearchFieldsBoost()
    {
        $stub = new SugarSearchEngineElasticTestStub();
        $modules = SugarSearchEngineMetadataHelper::getUserEnabledFTSModules();
        foreach ($modules as $module) {
            $options = array('moduleFilter' => array($module), 'addSearchBoosts' => true);
            $fields = $stub->getSearchFields($options);
            foreach ($fields as $fieldName) {
                $boost = substr($fieldName, -2);
                $boostOK = (preg_match('/\^\d/', $fieldName) === 1);
                $this->assertTrue($boostOK);
            }
        }
    }


    public function mappingSearchableTypeProvider()
    {
        return array(
            array('name', true),
            array('varchar', true),
            array('phone', true),
            array('enum', false),
            array('iframe', false),
            array('bool', false),
            array('invalid', false),
        );
    }

    /**
     * @dataProvider mappingSearchableTypeProvider
     */
    public function testSearchableType($type, $searchable)
    {
        $ret = SugarSearchEngineFactory::getInstance('Elastic')->isTypeFtsEnabled($type);
        $this->assertEquals($searchable, $ret, 'field type incorrect searchable definition');
    }
    
    
    /**
     * testForceAsyncIndex()
     *
     * Tests if index bean adds a bean to the fts_queue table when the forceAsyncIndex
     * property is set to true.
     */
    public function testForceAsyncIndex()
    {
        $stub = new SugarSearchEngineElasticTestStub();
        $stub->setForceAsyncIndex(true);
        /*
        // get an account bean to test with.
        $accountIDQuery = 'select id from accounts limit 1';
        $account = $this->_db->getOne($accountIDQuery);
        $accountBean = BeanFactory::getBean('Accounts', $account['id']);
        
        */
        // find out how many times this account bean is in fts_queue already.
        $ftsQueueQuery = "select count(bean_id) as total from fts_queue where bean_id = '{$this->account->id}'";
        //print("query: $ftsQueueQuery");
        $ftsCountBefore = $this->_db->getOne($ftsQueueQuery);
        
        // index the bean.
        $stub->indexBean($this->account, false);
        
        // re-check for presence of bean in fts_queue. Should be incremeted by 1.
        $ftsCountAfter = $this->_db->getOne($ftsQueueQuery);
        
        $msg = "Expected Account bean id {$this->account->id} to be added to fts_queue table";
        $this->assertEquals($ftsCountBefore['total'], ($ftsCountAfter['total'] - 1), $msg);
    }

    public function constructMainFilterDataProvider()
    {
        return array(
            array(array()),
            array(
                array('my_items' => true),
            ),
            array(
                array('favorites' => 2)
            ),
            array(
                array(
                    'filter' => array(
                        'type' => 'range',
                        'fieldname' => 'test',
                        'range' => array('to' => 3)
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider constructMainFilterDataProvider
     */
    public function testConstructMainFilter($options)
    {
        $stub = $this->getMockBuilder('SugarSearchEngineElasticTestStub')
            ->setMethods(array('constructModuleLevelFilter'))
            ->getMock();

        $stub->expects($this->any())
            ->method('constructModuleLevelFilter')
            ->will($this->returnValue(new Elastica_Filter_Bool()));

        $filter = $stub
            ->constructMainFilter(array('Accounts', 'Opportunities', 'Contacts'), $options)
            ->toArray();

        $this->assertArrayHasKey('should', $filter['bool']);
    }

    public function testConstructMainFilterInstanceOf()
    {
        $stub = $this->getMockBuilder('SugarSearchEngineElasticTestStub')
            ->setMethods(array('constructModuleLevelFilter'))
            ->getMock();

        $stub->expects($this->any())
            ->method('constructModuleLevelFilter')
            ->will($this->returnValue(new Elastica_Filter_Bool()));

        $result = $stub->constructMainFilter(array('Accounts', 'Opportunities'));

        $this->assertInstanceOf('Elastica_Filter_Bool', $result);
    }

    public function testConstructMainFilterNoModules()
    {
        $stub = new SugarSearchEngineElasticTestStub();

        $filter = $stub
            ->constructMainFilter(array())
            ->toArray();

        $this->assertArrayNotHasKey('should', $filter['bool']);
    }
}


class SugarSearchEngineElasticTestStub extends SugarSearchEngineElastic
{
    // to test protected function
    public function canAppendWildcard($queryString)
    {
        return parent::canAppendWildcard($queryString);
    }

    public function getSearchFields($options)
    {
        return parent::getSearchFields($options);
    }

    public function setForceAsyncIndex($state)
    {
        $this->forceAsyncIndex = $state;
    }

    public function constructMainFilter($finalTypes, $options = array())
    {
        return parent::constructMainFilter($finalTypes, $options);
    }
}
