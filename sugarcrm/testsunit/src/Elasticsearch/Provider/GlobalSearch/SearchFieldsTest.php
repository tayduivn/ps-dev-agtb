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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields
 *
 */
class SearchFieldsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::isFieldSearchable
     * @dataProvider dataProviderIsFieldSearchable
     *
     * @param array $params
     * @param boolean $isSearchable
     */
    public function testIsFieldSearchable(array $params, $isSearchable)
    {
        $sf = $this->getSearchFieldsMock();
        $result = TestReflection::callProtectedMethod($sf, 'isFieldSearchable', array($params));
        $this->assertSame($isSearchable, $result);
    }

    public function dataProviderIsFieldSearchable()
    {
        return array(
            array(
                array(
                    'name' => 'foo1',
                    'full_text_search' => array('enabled' => true, 'searchable' => false),
                ),
                false,
            ),
            array(
                array(
                    'name' => 'foo2',
                    'full_text_search' => array('enabled' => true, 'searchable' => true),
                ),
                true,
            ),
            array(
                array(
                    'name' => 'foo3',
                    'full_text_search' => array('enabled' => true, 'boost' => 1),
                ),
                true,
            ),
            array(
                array(
                    'name' => 'foo4',
                    'full_text_search' => array('enabled' => true, 'boost' => 3, 'searchable' => true),
                ),
                true,
            ),
            array(
                array(
                    'name' => 'foo5',
                    'full_text_search' => array('enabled' => true),
                ),
                false,
            ),
        );
    }

    /**
     * @covers ::getSearchFields
     * @covers ::getModuleSearchFields
     * @covers ::getMultiFieldSearchFields
     * @dataProvider dataProviderGetSearchFields
     *
     * @param array $modules
     * @param array $vardef
     * @param array $mappingDefs
     * @param integer $boost
     * @param array $expected
     */
    public function testGetSearchFields(array $modules, array $vardef, array $mappingDefs, $boost, array $expected)
    {
        $sf = $this->getSearchFieldsMock(
            array(
                'getFtsFields',
                'getMappingDefsForSugarType',
                'getBoostedField',
            )
        );

        $sf->expects($this->any())
            ->method('getFtsFields')
            ->will($this->returnValue($vardef));

        $sf->expects($this->any())
            ->method('getMappingDefsForSugarType')
            ->will($this->returnValue($mappingDefs));

        $sf->expects($this->exactly($boost))
            ->method('getBoostedField')
            ->will($this->returnCallback(array($this, 'getBoostedField')));

        $sf->setBoost((bool) $boost);

        $fields = TestReflection::callProtectedMethod($sf, 'getSearchFields', array($modules));
        $this->assertEquals($expected, $fields);
    }

    public function dataProviderGetSearchFields()
    {
        return array(
            array(
                array('Tasks', 'Accounts'),
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => array('enabled' => true, 'searchable' => true),
                    ),
                    'description' => array(
                        'name' => 'description',
                        'type' => 'text',
                        'full_text_search' => array('enabled' => true, 'boost' => 3, 'searchable' => true),
                    ),
                    'date_modified' => array(
                        'name' => 'date_modified',
                        'type' => 'datetime',
                        'full_text_search' => array('enabled' => true, 'searchable' => false),
                    ),
                    'date_entered' => array(
                        'name' => 'date_entered',
                        'type' => 'datetime',
                        'full_text_search' => array('enabled' => true),
                    ),
                ),
                array(
                    'gs_string',
                    'gs_strong',
                ),
                8,
                array(
                    'Tasks.name.gs_string^69',
                    'Tasks.name.gs_strong^69',
                    'Tasks.description.gs_string^69',
                    'Tasks.description.gs_strong^69',
                    'Accounts.name.gs_string^69',
                    'Accounts.name.gs_strong^69',
                    'Accounts.description.gs_string^69',
                    'Accounts.description.gs_strong^69',
                ),
            ),
            array(
                array('Contacts'),
                array(
                    'first_name' => array(
                        'name' => 'first_name',
                        'type' => 'name',
                        'full_text_search' => array('enabled' => true, 'searchable' => true),
                    ),
                ),
                array('gs_default'),
                0,
                array(
                    'Contacts.first_name.gs_default',
                ),
            ),
        );
    }

    /**
     * Used as callback for testGetSearchFields
     * @return string
     */
    public function getBoostedField()
    {
        $args = func_get_args();
        return $args[0] . '^69';
    }

    /**
     * Get SearchFields mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields
     */
    protected function getSearchFieldsMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
