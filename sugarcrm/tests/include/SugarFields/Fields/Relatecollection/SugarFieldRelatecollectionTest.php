<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'include/SugarFields/Fields/Relatecollection/SugarFieldRelatecollection.php';

class SugarFieldRelatecollectionTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTestParseProperties
     */
    public function testParseProperties($properties, $expected, $message)
    {
        $relateCollection = new SugarFieldRelatecollectionTestMock('relatecollection');

        $actual = $relateCollection->parsePropertiesTest($properties);

        $this->assertEquals($expected, $actual, $message);
    }

    public function providerTestParseProperties()
    {
        return array(
            // empty input data
            array(
                array(),
                array(
                    false,
                    array(
                        'id',
                        'name',
                    ),
                    -1,
                    false,
                ),
                'Default should be driven by class properties',
            ),
            // collection_limit and collection_create is overrided OK
            array(
                array(
                    'link' => 'testLink',
                    'collection_limit' => 23,
                    'collection_create' => true,
                ),
                array(
                    'testLink',
                    array(
                        'id',
                        'name',
                    ),
                    23,
                    true,
                ),
                'Class should respect "collection_limit" and "collection_create" values',
            ),
            // Collection fields should be merged to final fields list
            array(
                array(
                    'link' => 'testLink',
                    'collection_limit' => 23,
                    'collection_create' => true,
                    'collection_fields' => array('test', 'test2'),
                ),
                array(
                    'testLink',
                    array(
                        'id',
                        'name',
                        'test',
                        'test2',
                    ),
                    23,
                    true,
                ),
                '"collection_fields" values should be merged to final fields list',
            ),
            // Collection fields should be unique
            array(
                array(
                    'link' => 'testLink',
                    'collection_limit' => 23,
                    'collection_create' => true,
                    'collection_fields' => array('id', 'name'),
                ),
                array(
                    'testLink',
                    array(
                        'id',
                        'name',
                    ),
                    23,
                    true,
                ),
                '"collection_fields" values should be merged to final fields list'
            ),
            // Collection fields should be an array with fields
            array(
                array(
                    'link' => 'testLink',
                    'collection_limit' => 23,
                    'collection_create' => true,
                    'collection_fields' => 'someValue',
                ),
                array(
                    'testLink',
                    array(
                        'id',
                        'name',
                    ),
                    23,
                    true,
                ),
                '"collection_fields" should be an array'
            ),
        );
    }

    /**
     * @dataProvider providerTestApiFormatField
     */
    public function testApiFormatField($field, $properties, $relateBean, $expected)
    {
        $result = array(
            array(
                'id'   => 'id1',
                'name' => 'test',
                'data' => 'test_data',
            ),
            array(
                'id'   => 'id2',
                'name' => 'test2',
                'data' => 'test_data2',
            ),
        );

        $sugarQuery = $this->getSugarQuery($result);

        // prepare test object
        $sut = $this->getRelColMock(array('getSugarQuery', 'getRelatedSeedBean'));

        $sut->expects($this->any())
            ->method('getSugarQuery')
            ->will($this->returnValue($sugarQuery));

        $sut->expects($this->any())
            ->method('getRelatedSeedBean')
            ->will($this->returnValue($relateBean));

        // perform test
        $data = array();
        $sut->apiFormatField($data, $this->getBeanMock(), array(), $field, $properties);

        // assertions
        $this->assertArrayHasKey($field, $data);
        $this->assertEquals($expected, $data[$field]);
    }

    public function providerTestApiFormatField()
    {
        return array(
            // no link provided
            array(
                'foo',
                array(),
                null,
                array(),
            ),
            // link provided
            array(
                'bar',
                array('link' => 'contacts'),
                null,
                array(),
            ),
            // link provided and link exists (mock getRelatedSeedBean)
            array(
                'bar',
                array('link' => 'contacts'),
                BeanFactory::getBean('Contacts'),
                array(
                    array(
                        'id'   => 'id1',
                        'name' => 'test',
                        'data' => 'test_data'
                    ),
                    array(
                        'id'   => 'id2',
                        'name' => 'test2',
                        'data' => 'test_data2'
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider providerTestApiSave
     */
    public function testApiSave($existing, $put, $createFlag, $expectAdd, $expectDelete, $expectCreate)
    {
        // base settings
        $field = 'testField';
        $linkField = 'testLink';
        $params = array(
            $field => $put,
        );
        $properties = array(
            'link' => $linkField,
            'collection_create' => $createFlag,
        );

        // prepare mocked bean to test relationship add/removal
        $link = $this->getLink2Mock();

        $link->expects($this->exactly($expectAdd))
            ->method('add');
        $link->expects($this->exactly($expectDelete))
            ->method('delete');

        $bean = $this->getBeanMock();
        $bean->$linkField = $link;

        // prepare subject under test
        $stubs = array('getLinkedRecords', 'createNewBeanBeforeLink');
        $sut = $this->getRelColMock($stubs);

        // stub already linked records
        $sut->expects($this->any())
            ->method('getLinkedRecords')
            ->will($this->returnValue($existing));

        // stub bean create
        $createdBean = $this->getBeanMock();
        $createdBean->id = 'dummy';
        $sut->expects($this->exactly($expectCreate))
            ->method('createNewBeanBeforeLink')
            ->will($this->returnValue($createdBean));

        // execute save
        $sut->apiSave($bean, $params, $field, $properties);
    }

    public function providerTestApiSave()
    {
        return array(

            // add link
            array(
                array(), // existing linked ids
                array(
                    array(
                        'id' => '1234',
                        'name' => 'related1234',
                    ),
                ),
                false, // collectionCreate flag
                1, 	   // add
                0,     // remove
                0,     // create
            ),

            // add already existing link - ignored
            array(
                array('1234' => '1234'), // existing linked ids
                array(
                    array(
                        'id' => '1234',
                        'name' => 'related1234',
                    ),
                ),
                false, // collectionCreate flag
                0, 	   // add
                0,     // remove
                0,     // create
            ),

            // missing required fields - ignored
            array(
                array(), // existing linked ids
                array(
                    array(
                        'wazaa' => '1234',
                    ),
                ),
                 false, // collectionCreate flag
                0, 	   // add
                0,     // remove
                0,     // create
            ),

            // add link for new object without create enabled - ignored
            array(
                array(), // existing linked ids
                array(
                    array(
                        'id' => false,
                        'name' => 'relatednew',
                    ),
                ),
                false, // collectionCreate flag
                0, 	   // add
                0,     // remove
                0,     // create
            ),

            // add link for new object with create enabled
            array(
                array(), // existing linked ids
                array(
                    array(
                        'id' => false,
                        'name' => 'relatednew',
                    ),
                ),
                true,  // collectionCreate flag
                1, 	   // add
                0,     // remove
                1,     // create
            ),

            // remove link
            array(
                array('1234' => '1234'), // existing linked ids
                array(
                    array(
                        'id' => '1234',
                        'name' => 'related1234',
                        'removed' => true,
                    ),
                ),
                false, // collectionCreate flag
                0, 	   // add
                1,     // remove
                0,     // create
            ),

            // remove link which doesnt really exist - handled by Link2
            array(
                array('1234' => '1234'), // existing linked ids
                array(
                    array(
                        'id' => '5678',
                        'name' => 'related5678',
                        'removed' => true,
                    ),
                ),
                false, // collectionCreate flag
                0, 	   // add
                1,     // remove
                0,     // create
            ),

            // remove link for a non-existing object - ignored
            array(
                array('1234' => '1234'), // existing linked ids
                array(
                    array(
                        'id' => false,
                        'name' => 'relatednew',
                        'removed' => true,
                    ),
                ),
                false, // collectionCreate flag
                0, 	   // add
                0,     // remove
                0,     // create
            ),
        );
    }

    /**
     *
     * @param mixed (null|array) $methods
     * @return SugarFieldRelatecollection
     */
    protected function getRelColMock($methods = array())
    {
        return $this->getMockBuilder('SugarFieldRelatecollection')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     *
     * @param mixed (null|array) $methods
     * @return SugarBean
     */
    protected function getBeanMock($methods = array())
    {
        return $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Prepare SugarQuery mock
     *
     * @param $result
     * @return SugarQuery
     */
    protected function getSugarQuery($result)
    {
        // prepare mocked SugarQuery. Mock joinSubpanel to not load relations
        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->setMethods(array('execute', 'joinSubpanel'))
            ->getMock();

        $sugarQuery->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($result));

        return $sugarQuery;
    }

    protected function getLink2Mock()
    {
        return $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
    }
}

class SugarFieldRelatecollectionTestMock extends SugarFieldRelatecollection
{
    public function parsePropertiesTest($properties)
    {
        return parent::parseProperties($properties);
    }
}
