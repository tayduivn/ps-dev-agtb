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
require_once 'include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php';

class SugarSearchEngineElasticTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('app_list_strings');
        // create a bean for a module which is fts enabled
        $modules = SugarSearchEngineMetadataHelper::getSystemEnabledFTSModules();
        if ($modules) {
            $this->bean = BeanFactory::newBean(array_shift($modules));
            $this->bean->id = create_guid();
            $this->bean->assigned_user_id = create_guid();
        }

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
        // temp fix for BR=1505 - need refactoring to remove the actual ES ping tests
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('info', 'fts_down', 0);

        // Elastica\Client mock
        $client = $this->getMockBuilder('Elastica\\Client')
            ->setMethods(array('request'))
            ->getMock();
        $client->expects($this->once())
            ->method('request')
            ->will($this->returnValue(new \Elastica\Response('{}')));

        $stub = new SugarSearchEngineElasticTestStub();
        $stub->setClient($client);
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
        if (empty($this->bean)) {
            $this->markTestIncomplete("No FTS enabled modules available");
        }

        $stub = new SugarSearchEngineElasticTestStub();
        $stub->setForceAsyncIndex(true);

        // be sure our queue is empty
        $this->_db->query("DELETE FROM fts_queue");

        // index the bean.
        $stub->indexBean($this->bean, false);

        // check for presence of bean in fts_queue
        $sql = sprintf(
            "SELECT COUNT(id) FROM fts_queue WHERE bean_id = %s",
            $this->_db->quoted($this->bean->id)
        );
        $count = $this->_db->getOne($sql);

        $msg = "Expected bean id {$this->bean->id} to be added to fts_queue table";
        $this->assertEquals(1, $count, $msg);
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
                        'fieldName' => 'test',
                        'value' => array('to' => 3)
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
            ->will($this->returnValue(new \Elastica\Filter\Bool()));

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
            ->will($this->returnValue(new \Elastica\Filter\Bool()));

        $result = $stub->constructMainFilter(array('Accounts', 'Opportunities'));

        $this->assertInstanceOf('Elastica\\Filter\\Bool', $result);
    }

    public function testConstructMainFilterNoModules()
    {
        $stub = new SugarSearchEngineElasticTestStub();

        $filter = $stub
            ->constructMainFilter(array())
            ->toArray();

        $this->assertEmpty($filter);
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

    public function checkAccess()
    {
        return true;
    }
}
