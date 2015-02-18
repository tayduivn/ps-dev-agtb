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

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
 * @uses \SugarAutoLoader
 *
 */
class GlobalSearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::setAutoIncrementValues
     * @dataProvider providerSetAutoIncrementValues
     *
     * @param string $moduleName : the name of the module
     * @param string $fieldName : the name of the field
     * @param string $fieldValue : the value of the field
     */
    public function testSetAutoIncrementValues($moduleName, $fieldName, $fieldValue)
    {
        $provider = $this->getGlobalSearchMock(
            array(
                'retrieveFieldByQuery',
                'getFtsAutoIncrementFields'
            )
        );

        $provider->expects($this->any())
            ->method('retrieveFieldByQuery')
            ->will($this->returnValue($fieldValue));

        $provider->expects($this->any())
            ->method('getFtsAutoIncrementFields')
            ->will($this->returnValue(array($fieldName)));

        $bean = $this->getSugarBeanMock();
        $bean->module_name = $moduleName;
        $provider->setAutoIncrementValues($bean);
        $this->assertEquals($fieldValue, $bean->$fieldName);
    }

    public function providerSetAutoIncrementValues()
    {
        return array(
            array(
                'Bugs',
                'bug_number',
                '8fb551ba-1b88-8c08-a3cb-54daacba6800',
            ),
            array(
                'Bugs',
                'bug_number',
                ''
            ),
            array(
                'Cases',
                'case_number',
                '34073c20-2d6b-0c1d-0a5b-54dab423fbdd',
            ),
            array(
                'Cases',
                'case_number',
                '',
            ),
        );
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
     */
    protected function getGlobalSearchMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @return \SugarBean
     */
    protected function getSugarBeanMock(array $methods = null)
    {
        return $this->getMockBuilder('\SugarBean')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
