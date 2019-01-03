<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\data;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \SugarBean
 */
class SugarBeanTest extends TestCase
{
    /**
     * @covers ::loadAutoIncrementValues()
     */
    public function testLoadAutoIncrementValuesWithAutoincrement()
    {
        // The field for the test
        $field = 'test_ai';

        // The value for the test
        $value = 4; // guaranteed to be random http://www.xkcd.com/221/

        // The sugar query object mock, needed to mock the return of execute
        $query = TestMockHelper::getObjectMock(
            $this,
            'SugarQuery',
            ['execute', 'from', 'select', 'where', 'equals']
        );

        // Mock out the chainable returns from the individual component method
        // calls since these are inside a private method that cannot be mocked
        $query->method('from')->will($this->returnValue($query));
        $query->method('select')->will($this->returnValue($query));
        $query->method('where')->will($this->returnValue($query));
        $query->method('equals')->will($this->returnValue($query));

        // The expected return of execute
        $query->expects($this->once())
              ->method('execute')
              ->will($this->returnValue([[$field => $value]]));

        // The mock bean object we will be working on
        $bean = TestMockHelper::getObjectMock(
            $this,
            'SugarBean',
            ['getSugarQueryObject']
        );

        // Set a bean ID since we need one
        $bean->id = 1;

        // Set the field defs on the bean
        $bean->field_defs = [
            $field => [
                'name' => $field,
                'type' => 'int',
                'auto_increment' => true,
            ],
        ];

        // And set the test property on the bean
        $bean->{$field} = null;

        // Set expectations
        $bean->expects($this->once())
             ->method('getSugarQueryObject')
             ->will($this->returnValue($query));

        // Call the method to test now
        TestReflection::callProtectedMethod($bean, 'loadAutoIncrementValues');

        // Verify what was done
        $this->assertEquals($bean->{$field}, $value);
    }
}
