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

namespace Sugarcrm\SugarcrmTest\Elasticsearch\Provider\GlobalSearch;

use Sugarcrm\Sugarcrm\Elasticsearch\Container;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch;


/**
 * Test for the generic Logger.
 */
class GlobalSearchTest extends \Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test if a field is searchable or not.
     * @param array $params : the field's metadata.
     * @param boolean $isSearchable : the expected
     * @dataProvider providerIsFieldSearchable
     */
    public function testIsFieldSearchable($params, $isSearchable)
    {
        $container = new Container();
        $provider = new GlobalSearch($container);
        $result = \SugarTestReflection::callProtectedMethod($provider, 'isFieldSearchable', array($params));
        $this->assertEquals($isSearchable, $result);
    }

    /**
     * Data provider to test isFieldSearchable().
     * @return array
     */
    public function providerIsFieldSearchable()
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
     * Test getting saerch fields for modules.
     * @param array $modules : the list of modules
     * @param array $vardef : the fields from getFtsFields
     * @param array $result : the expected fields
     * @dataProvider providerGetSearchFields
     */
    public function testGetSearchFields($modules, $vardef, $result)
    {
        $container = new Container();
        $provider = $this->getMock(
            'Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch',
            array('getFtsFields'),
            array($container)
        );
        $provider->expects($this->any())
            ->method('getFtsFields')
            ->will($this->returnValue($vardef));

        \SugarTestReflection::setProtectedValue($provider, 'modules', $modules);

        $fields = \SugarTestReflection::callProtectedMethod($provider, 'getSearchFields', array(true));
        $this->assertEquals($result, $fields);
    }

    /**
     * Data provider to test getSearchFields().
     * @return array
     */
    public function providerGetSearchFields()
    {
        return array(
            array(
                array('Tasks'),
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
                ),
                array(
                    'Tasks.name.gs_string',
                    'Tasks.description.gs_string^3',
                ),
            ),
        );
    }

}
