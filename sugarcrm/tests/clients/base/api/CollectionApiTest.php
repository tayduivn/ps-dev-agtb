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

require_once 'clients/base/api/CollectionApi.php';

/**
 * @covers CollectionApi
 */
class CollectionApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api;

    public function setUp()
    {
        $this->api = new CollectionApi();
    }

    public function testGetData()
    {
        /** @var RelateApi|PHPUnit_Framework_MockObject_MockObject $api */
        $relateApi = $this->getMockBuilder('RelateApi')
            ->disableOriginalConstructor()
            ->setMethods(array('filterRelated'))
            ->getMock();
        $relateApi->expects($this->exactly(2))
            ->method('filterRelated')
            ->will($this->onConsecutiveCalls(array(
                array('name' => 'a'),
            ), array(
                array('name' => 'c1'),
                array('name' => 'c2'),
            )));

        /** @var CollectionApi|PHPUnit_Framework_MockObject_MockObject $api */
        $api = $this->getMockBuilder('CollectionApi')
            ->disableOriginalConstructor()
            ->setMethods(array('getLinkArguments'))
            ->getMock();
        $api->expects($this->exactly(2))
            ->method('getLinkArguments')
            ->will($this->returnCallback(function () {
                return array();
            }));

        SugarTestReflection::setProtectedValue($api, 'relateApi', $relateApi);

        $service = SugarTestRestUtilities::getRestServiceMock();

        $actual = SugarTestReflection::callProtectedMethod(
            $api,
            'getData',
            array($service, array(
                'offset' => array(
                    'a' => 0,
                    'b' => -1,
                    'c' => 1,
                ),
            ), new SugarBean(), array(
                array('name' => 'a'),
                array('name' => 'b'),
                array('name' => 'c'),
            ))
        );

        $this->assertEquals(array(
            'a' => array(
                array('name' => 'a'),
            ),
            'c' => array(
                array('name' => 'c1'),
                array('name' => 'c2'),
            ),
        ), $actual);
    }

    /**
     * @dataProvider getLinkArgumentsProvider
     */
    public function testGetLinkArguments(array $args, array $link, array $expected)
    {
        $service = SugarTestRestUtilities::getRestServiceMock();
        $bean = new SugarBean();
        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'getLinkArguments',
            array($service, $args, $bean, $link)
        );

        $this->assertEquals($expected, $actual);
    }

    public static function getLinkArgumentsProvider()
    {
        return array(
            array(
                array(
                    'fields' => 'alias,another_field',
                    'filter' => array(
                        '$or' => array(
                            'alias' => 'a',
                            'another_field' => 'b',
                        ),
                    ),
                    'order_by' => array(
                        'alias' => true,
                        'another_field' => false,
                    ),
                    'offset' => array(
                        'test_link' => 10,
                    ),
                    'max_num' => 20,
                ),
                array(
                    'name' => 'test_link',
                    'field_map' => array(
                        'alias' => 'field',
                    ),
                ),
                array(
                    'fields' => 'field,another_field',
                    'filter' => array(
                        '$or' => array(
                            'field' => 'a',
                            'another_field' => 'b',
                        ),
                    ),
                    'order_by' => 'field,another_field:desc',
                    'offset' => 10,
                    'max_num' => 20,
                    'link_name' => 'test_link'
                ),
            )
        );
    }

    public function testGetCollectionDefinitionSuccess()
    {
        /** @var CollectionApi|PHPUnit_Framework_MockObject_MockObject $api */
        $api = $this->getMockBuilder('CollectionApi')
            ->disableOriginalConstructor()
            ->setMethods(array('normalizeLinks'))
            ->getMock();
        $api->expects($this->once())
            ->method('normalizeLinks')
            ->will($this->returnCallback(function () {
                return array('from-normalize-links' => true);
            }));

        $bean = $this->getCollectionDefinitionBeanMock(array(
            'type' => 'collection',
            'links' => array(),
        ));

        $actual = SugarTestReflection::callProtectedMethod(
            $api,
            'getCollectionDefinition',
            array($bean, 'test')
        );

        $this->assertEquals(array(
            'type' => 'collection',
            'links' => array(
                'from-normalize-links' => true,
            ),
        ), $actual);
    }

    /**
     * @dataProvider getCollectionDefinitionFailureProvider
     */
    public function testGetCollectionDefinitionFailure($definition, $expected)
    {
        $bean = $this->getCollectionDefinitionBeanMock($definition);
        $this->setExpectedException($expected);
        SugarTestReflection::callProtectedMethod(
            $this->api,
            'getCollectionDefinition',
            array($bean, 'test')
        );
    }

    public static function getCollectionDefinitionFailureProvider()
    {
        return array(
            'non-collection' => array(
                null,
                'SugarApiExceptionNotFound'
            ),
            'no-links' => array(
                array('type' => 'collection'),
                'SugarApiExceptionError'
            ),
        );
    }

    protected function getCollectionDefinitionBeanMock($definition)
    {
        /** @var SugarBean|PHPUnit_Framework_MockObject_MockObject $api */
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array('getFieldDefinition'))
            ->getMock();
        $bean->expects($this->once())
            ->method('getFieldDefinition')
            ->with('test')
            ->will($this->returnValue($definition));

        return $bean;
    }

    /**
     * @dataProvider normalizeLinksSuccessProvider
     */
    public function testNormalizeLinksSuccess(array $links, $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'normalizeLinks',
            array($links, null, null)
        );

        $this->assertEquals($expected, $actual);
    }

    public static function normalizeLinksSuccessProvider()
    {
        return array(
            array(
                array(
                    'a',
                    array('name' => 'b'),
                    array(
                        'name' => 'c',
                        'field_map' => array(),
                    ),
                ),
                array(
                    array('name' => 'a'),
                    array('name' => 'b'),
                    array(
                        'name' => 'c',
                        'field_map' => array(),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider normalizeLinksFailureProvider
     * @expectedException SugarApiExceptionError
     */
    public function testNormalizeLinksFailure($links)
    {
        SugarTestReflection::callProtectedMethod(
            $this->api,
            'normalizeLinks',
            array($links, null, null)
        );
    }

    public static function normalizeLinksFailureProvider()
    {
        return array(
            'non-array-links' => array(null),
            'non-string-or-array-link' => array(
                array(null),
            ),
            'no-name' => array(
                array(
                    array(),
                ),
            ),
        );
    }

    /**
     * @dataProvider normalizeArgumentsProvider
     */
    public function testNormalizeArguments(array $args, array $definition, $expected)
    {
        /** @var CollectionApi|PHPUnit_Framework_MockObject_MockObject $api */
        $api = $this->getMockBuilder('CollectionApi')
            ->disableOriginalConstructor()
            ->setMethods(array('normalizeOffset', 'getDefaultLimit', 'getDefaultOrderBy'))
            ->getMock();
        $api->expects($this->any())
            ->method('normalizeOffset')
            ->will($this->returnCallback(function () {
                return 'from-normalize-offset';
            }));
        $api->expects($this->any())
            ->method('getDefaultLimit')
            ->will($this->returnCallback(function () {
                return 'from-default-limit';
            }));
        $api->expects($this->any())
            ->method('getDefaultOrderBy')
            ->will($this->returnCallback(function () {
                return 'from-default-order-by';
            }));

        $actual = SugarTestReflection::callProtectedMethod(
            $api,
            'normalizeArguments',
            array($args, array_merge(array(
                'links' => array(),
            ), $definition))
        );

        $this->assertEquals($expected, $actual);
    }

    public static function normalizeArgumentsProvider()
    {
        return array(
            'defaults' => array(
                array(),
                array(),
                array(
                    'offset' => 'from-normalize-offset',
                    'max_num' => 'from-default-limit',
                    'order_by' => 'from-default-order-by',
                ),
            ),
            'from-arguments' => array(
                array(
                    'order_by' => 'order,by',
                    'max_num' => 25,
                ),
                array(),
                array(
                    'order_by' => array(
                        'order' => true,
                        'by' => true,
                    ),
                    'max_num' => 25,
                    'offset' => 'from-normalize-offset',
                ),
            ),
            'from-link-definition' => array(
                array(),
                array(
                    'order_by' => 'defined,in:desc,link',
                ),
                array(
                    'offset' => 'from-normalize-offset',
                    'max_num' => 'from-default-limit',
                    'order_by' => array(
                        'defined' => true,
                        'in' => false,
                        'link' => true,
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider normalizeOffsetSuccess
     */
    public function testNormalizeOffsetSuccess($offset, array $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'normalizeOffset',
            array(
                array('offset' => $offset),
                array(
                    array('name' => 'a'),
                ),
            )
        );

        $this->assertEquals($expected, $actual);
    }

    public static function normalizeOffsetSuccess()
    {
        return array(
            'default' => array(
                null,
                array(
                    'a' => 0,
                ),
            ),
            'integer' => array(
                array('a' => 1),
                array('a' => 1),
            ),
            'numeric-string' => array(
                array('a' => '-1'),
                array('a' => -1),
            ),
            'non-numeric-string' => array(
                array('a' => 'non-numeric-string'),
                array('a' => 0),
            ),
            'negative' => array(
                array('a' => -2),
                array('a' => -1),
            ),
        );
    }

    /**
     * @dataProvider normalizeOffsetFailure
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testNormalizeOffsetFailure(array $offset, array $links)
    {
        SugarTestReflection::callProtectedMethod(
            $this->api,
            'normalizeOffset',
            array($offset, $links)
        );
    }

    public static function normalizeOffsetFailure()
    {
        return array(
            'non-array' => array(
                array(
                    'offset' => 'a',
                ),
                array(),
            ),
        );
    }

    /**
     * @dataProvider flattenDataProvider
     */
    public function testFlattenData(array $data, array $expectedRecords, array $expectedNextOffset)
    {
        $records = SugarTestReflection::callProtectedMethod(
            $this->api,
            'flattenData',
            array($data, &$nextOffset)
        );

        $this->assertEquals($expectedRecords, $records);
        $this->assertEquals($expectedNextOffset, $nextOffset);
    }

    public static function flattenDataProvider()
    {
        return array(
            array(
                array(
                    'a' => array(
                        'records' => array(
                            array('name' => 'A'),
                            array('name' => 'B'),
                        ),
                        'next_offset' => 2,
                    ),
                    'b' => array(
                        'records' => array(
                            array('title' => 'C'),
                        ),
                        'next_offset' => -1,
                    ),
                ),
                array(
                    array(
                        'name' => 'A',
                        '_link' => 'a',
                    ),
                    array(
                        'name' => 'B',
                        '_link' => 'a',
                    ),
                    array(
                        'title' => 'C',
                        '_link' => 'b',
                    ),
                ),
                array(
                    'a' => 2,
                    'b' => -1,
                ),
            ),
        );
    }

    /**
     * @dataProvider getNextOffsetProvider
     */
    public function testgetNextOffset(
        array $offset,
        array $records,
        array $nextOffset,
        array $remainder,
        array $expected
    ) {
        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'getNextOffset',
            array($offset, $records, $nextOffset, $remainder)
        );

        $this->assertEquals($expected, $actual);
    }

    public static function getNextOffsetProvider()
    {
        return array(
            array(
                array(
                    'a' => 1,
                    'b' => 1,
                    'c' => -1,
                ),
                array(
                    array(
                        '_link' => 'a',
                    ),
                    array(
                        '_link' => 'b',
                    ),
                ),
                array(
                    'a' => 3,
                    'b' => -1,
                ),
                array(
                    array(
                        '_link' => 'a',
                    ),
                ),
                array(
                    // requested from: 1, returned: 1, truncated: yes => 1 + 1 = 2 (ignore next offset from link)
                    'a' => 2,
                    // requested from: 1, returned: 1, truncated: no => -1 (use next offset from link)
                    'b' => -1,
                    // requested from: -1 => -1 (return original value)
                    'c' => -1,
                ),
            ),
        );
    }

    /**
     * @dataProvider sortRecordsProvider
     */
    public function testSortRecords(array $records, array $spec, array $expected)
    {
        $actual = $records;
        SugarTestReflection::callProtectedMethod(
            $this->api,
            'sortRecords',
            array(&$actual, $spec)
        );

        $this->assertEquals($expected, $actual);
    }

    public function sortRecordsProvider()
    {
        return array(
            'strings' => array(
                array(
                    array(
                        'a' => 'x',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'z',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'Y',
                        '_link' => 'l',
                    ),
                ),
                array(
                    array(
                        'map' => array('l' => 'a'),
                        'is_numeric' => false,
                        'direction' => true,
                    )
                ),
                array(
                    array(
                        'a' => 'x',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'Y',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'z',
                        '_link' => 'l',
                    ),
                ),
            ),
            'numbers' => array(
                array(
                    array(
                        'a' => '10',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => '100',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => '11',
                        '_link' => 'l',
                    ),
                ),
                array(
                    array(
                        'map' => array('l' => 'a'),
                        'is_numeric' => true,
                        'direction' => true,
                    )
                ),
                array(
                    array(
                        'a' => '10',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => '11',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => '100',
                        '_link' => 'l',
                    ),
                ),
            ),
            'reverse' => array(
                array(
                    array(
                        'a' => 'x',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'z',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'Y',
                        '_link' => 'l',
                    ),
                ),
                array(
                    array(
                        'map' => array('l' => 'a'),
                        'is_numeric' => false,
                        'direction' => false,
                    )
                ),
                array(
                    array(
                        'a' => 'z',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'Y',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'x',
                        '_link' => 'l',
                    ),
                ),
            ),
            'multiple-links-and-aliasing' => array(
                array(
                    array(
                        'a' => 'x',
                        '_link' => 'l1',
                    ),
                    array(
                        'b' => 'z',
                        '_link' => 'l2',
                    ),
                    array(
                        'c' => 'y',
                        '_link' => 'l3',
                    ),
                ),
                array(
                    array(
                        'map' => array(
                            'l1' => 'a',
                            'l2' => 'b',
                            'l3' => 'c',
                        ),
                        'is_numeric' => false,
                        'direction' => true,
                    )
                ),
                array(
                    array(
                        'a' => 'x',
                        '_link' => 'l1',
                    ),
                    array(
                        'c' => 'y',
                        '_link' => 'l3',
                    ),
                    array(
                        'b' => 'z',
                        '_link' => 'l2',
                    ),
                ),
            ),
            'multiple-columns' => array(
                array(
                    array(
                        'a' => 'x',
                        'b' => 'y',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'x',
                        'b' => 'x',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'y',
                        'b' => 'y',
                        '_link' => 'l',
                    ),
                ),
                array(
                    array(
                        'map' => array(
                            'l' => 'a',
                        ),
                        'is_numeric' => false,
                        'direction' => true,
                    ),
                    array(
                        'map' => array(
                            'l' => 'b',
                        ),
                        'is_numeric' => false,
                        'direction' => true,
                    ),
                ),
                array(
                    array(
                        'a' => 'x',
                        'b' => 'x',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'x',
                        'b' => 'y',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'y',
                        'b' => 'y',
                        '_link' => 'l',
                    ),
                ),
            ),
            'equal-records' => array(
                array(
                    array(
                        'a' => 'x',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'y',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'x',
                        '_link' => 'l',
                    ),
                ),
                array(
                    array(
                        'map' => array(
                            'l' => 'a',
                        ),
                        'is_numeric' => false,
                        'direction' => true,
                    ),
                ),
                array(
                    array(
                        'a' => 'x',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'x',
                        '_link' => 'l',
                    ),
                    array(
                        'a' => 'y',
                        '_link' => 'l',
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider mapFilterSuccessProvider
     */
    public function testMapFilterSuccess(array $filter, array $fieldMap, array $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod($this->api, 'mapFilter', array($filter, $fieldMap));
        $this->assertEquals($expected, $actual);
    }

    public static function mapFilterSuccessProvider()
    {
        return array(
            'simple' => array(
                array(
                    'a-alias' => 1,
                ),
                array(
                    'a-alias' => 'a',
                ),
                array(
                    'a' => 1,
                ),
            ),
            'cyclic' => array(
                array(
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                    'd' => 4,
                ),
                array(
                    'b' => 'a',
                    'c' => 'b',
                    'a' => 'c',
                    'd' => 'e',
                ),
                array(
                    'c' => 1,
                    'a' => 2,
                    'b' => 3,
                    'e' => 4,
                ),
            ),
            'recursive' => array(
                array(
                    'q' => 1,
                    '$or' => array(
                        'r' => array(
                            '$and' => array(
                                's' => 2,
                                't' => 3,
                            )
                        ),
                        'u' => 4,
                    ),
                ),
                array(
                    'q' => 'a',
                    'r' => 'b',
                    's' => 'c',
                    't' => 'd',
                    'u' => 'e',
                ),
                array(
                    'a' => 1,
                    '$or' => array(
                        'b' => array(
                            '$and' => array(
                                'c' => 2,
                                'd' => 3,
                            )
                        ),
                        'e' => 4,
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider mapFilterFailureProvider
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testMapFilterFailure(array $filter, array $fieldMap)
    {
        SugarTestReflection::callProtectedMethod($this->api, 'mapFilter', array($filter, $fieldMap));
    }

    public static function mapFilterFailureProvider()
    {
        return array(
            'alias-conflict' => array(
                array(
                    'a' => 1,
                    'b' => 1,
                ),
                array(
                    'a' => 'c',
                    'b' => 'c',
                ),
            ),
        );
    }

    /**
     * @dataProvider mapOrderBySuccessProvider
     */
    public function testMapOrderBySuccess(array $orderBy, array $fieldMap, $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod($this->api, 'mapOrderBy', array($orderBy, $fieldMap));
        $this->assertEquals($expected, $actual);
    }

    public static function mapOrderBySuccessProvider()
    {
        return array(
            array(
                array(
                    'a' => true,
                    'b' => false,
                    'c' => true,
                ),
                array(
                    'b' => 'a',
                    'a' => 'b',
                ),
                array(
                    'b' => true,
                    'a' => false,
                    'c' => true,
                ),
            ),
        );
    }

    /**
     * @dataProvider mapOrderByFailureProvider
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testMapOrderByFailure(array $orderBy, array $fieldMap)
    {
        SugarTestReflection::callProtectedMethod($this->api, 'mapOrderBy', array($orderBy, $fieldMap));
    }

    public static function mapOrderByFailureProvider()
    {
        return array(
            'alias-conflict' => array(
                array(
                    'a' => true,
                    'b' => false,
                ),
                array(
                    'a' => 'c',
                    'b' => 'c',
                ),
            ),
        );
    }

    /**
     * @dataProvider mapFieldsProvider
     */
    public function testMapFields(array $fields, array $fieldMap, array $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod($this->api, 'mapFields', array($fields, $fieldMap));
        $this->assertEquals($expected, $actual);
    }

    public static function mapFieldsProvider()
    {
        return array(
            array(
                array('a-alias', 'b-alias', 'c'),
                array(
                    'a-alias' => 'a',
                    'b-alias' => 'b',
                ),
                array('a', 'b', 'c'),
            ),
        );
    }

    /**
     * @dataProvider formatOrderByProvider
     */
    public function testFormatOrderBy($string, array $array)
    {
        $actual = SugarTestReflection::callProtectedMethod($this->api, 'formatOrderBy', array($array));
        $this->assertEquals($string, $actual);
    }

    public static function formatOrderByProvider()
    {
        return array(
            array(
                'a,b:desc',
                array(
                    'a' => true,
                    'b' => false,
                ),
            ),
        );
    }

    /**
     * @dataProvider mergeRequestFieldsAndSortFieldsProvider
     */
    public function testMergeRequestFieldsAndSortFields($args, $expected) {

        /** @var CollectionApi|PHPUnit_Framework_MockObject_MockObject $api */
        $api = $this->getMockBuilder('CollectionApi')
            ->disableOriginalConstructor()
            ->getMock();

        $actual = SugarTestReflection::callProtectedMethod(
            $api,
            'mergeRequestFieldsAndSortFields',
            array($args)
        );

        $this->assertEquals($expected, $actual,'Merged array does not contain the desired elements');

    }

    public static function mergeRequestFieldsAndSortFieldsProvider() {
        return array(
                array(
                    array('fields' => array('id','title'), 'order_by' =>  array('name' => true)),
                    array('fields' => array('id','title','name'), 'order_by' => array('name' => true), 'addedRequestFields' => array('name'))
                ),
                array(
                    array('fields' => array('id','title'), 'order_by' =>  array('name' => false)),
                    array('fields' => array('id','title','name'), 'order_by' => array('name' => false), 'addedRequestFields' => array('name'))
                ),
                array(
                    array('fields' => array('id','title','name'), 'order_by' =>  array('name' => true)),
                    array('fields' => array('id','title','name'), 'order_by' => array('name' => true))
                ),
                array(
                    array('fields' => array('id','title'), 'order_by' =>  array('name' => true, 'id' => true)),
                    array('fields' => array('id','title','name'), 'order_by' => array('name' => true, 'id' => true), 'addedRequestFields' => array('name'))
                ),
        );

    }

    /**
     * @dataProvider cleanDataProvider
     */
    public function testCleanData($records, $args, $expected) {

        /** @var CollectionApi|PHPUnit_Framework_MockObject_MockObject $api */
        $api = $this->getMockBuilder('CollectionApi')
            ->disableOriginalConstructor()
            ->getMock();

        $actual = SugarTestReflection::callProtectedMethod(
            $api,
            'cleanData',
            array($records, $args)
        );

        $this->assertEquals($expected, $actual,'Unrequested fields not removed from return data.');
    }

    public static function cleanDataProvider() {
        return array(
            array(
                array(array('id' => 123, 'title' => 'Sales Executive', 'name' => 'John Smith'),
                      array('id' => 456, 'title' => 'Sgr Manager', 'name' => 'Peter Hanks')),
                array('addedRequestFields' => array('name')),
                array(array('id' => 123, 'title' => 'Sales Executive'),
                      array('id' => 456, 'title' => 'Sgr Manager'))
            ),
            array(
                array(array('id' => 123, 'title' => 'Sales Executive', 'name' => 'John Smith'),
                    array('id' => 456, 'title' => 'Sgr Manager', 'name' => 'Peter Hanks')),
                array('addedRequestFields' => array('name','title')),
                array(array('id' => 123),
                    array('id' => 456))
            ),

        );
    }
}
