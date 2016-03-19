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

namespace Sugarcrm\SugarcrmTests\ProcessManager;

use Sugarcrm\Sugarcrm\ProcessManager;
use Sugarcrm\Sugarcrm\ProcessManager\Exception as PME;

class FactoryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Tests getting a proper field evaluator object
     * @dataProvider getFieldEvaluatorProvider
     * @param array $def Field def array
     * @param array $instances Array of instance types to check
     */
    public function testGetFieldEvaluator($def, $instances)
    {
        // ProcessManager\Field\Evaluator\EvaluatorInterface
        $obj = ProcessManager\Factory::getFieldEvaluator($def);
        foreach ($instances as $instance) {
            $this->assertInstanceOf($instance, $obj);
        }
    }

    /**
     * Tests getting a PMSE Element object
     * @dataProvider getElementProvider
     * @param string $name The Element object type name
     * @param array $instances Array of instance types to check
     */
    public function testGetElement($name, $instances)
    {
        $obj = ProcessManager\Factory::getElement($name);
        foreach ($instances as $instance) {
            $this->assertInstanceOf($instance, $obj);
        }
    }

    /**
     * Tests getting any PMSE object
     * @dataProvider getPMSEObjectProvider
     * @param string $name The name of the object to load
     */
    public function testGetPMSEObject($name)
    {
        $obj = ProcessManager\Factory::getPMSEObject($name);
        $this->assertInstanceOf($name, $obj);
    }

    /**
     * Tests that proper exceptions are thrown on failure
     * @dataProvider getPMSEObjectThrowsExceptionProvider
     * @param string $name Errant class name
     */
    public function testGetPMSEObjectThrowsException($name)
    {
        $this->setExpectedException(PME\RuntimeException::class);
        $obj = ProcessManager\Factory::getPMSEObject($name);
    }

    public function getFieldEvaluatorProvider()
    {
        $nsRoot = 'Sugarcrm\\Sugarcrm\\ProcessManager\\Field\\Evaluator\\';

        return [
            [
                'def' => [
                    'type' => 'int',
                ],
                'instances' => [
                    $nsRoot . 'Base',
                    $nsRoot . 'EvaluatorInterface',
                ],
            ],
            // Tests date type fields
            [
                'def' => [
                    'type' => 'date',
                ],
                'instances' => [
                    $nsRoot . 'Datetime',
                    $nsRoot . 'EvaluatorInterface',
                ],
            ],
            // tests time type fields
            [
                'def' => [
                    'type' => 'time',
                ],
                'instances' => [
                    $nsRoot . 'Datetime',
                    $nsRoot . 'EvaluatorInterface',
                ],
            ],
            // tests datetimecombo type fields
            [
                'def' => [
                    'type' => 'datetimecombo',
                ],
                'instances' => [
                    $nsRoot . 'Datetime',
                    $nsRoot . 'EvaluatorInterface',
                ],
            ],
            // Tests datetime type fields
            [
                'def' => [
                    'type' => 'datetime',
                ],
                'instances' => [
                    $nsRoot . 'Datetime',
                    $nsRoot . 'EvaluatorInterface',
                ],
            ],
            [
                'def' => [
                    'custom_type' => 'foobar',
                ],
                'instances' => [
                    $nsRoot . 'Base',
                    $nsRoot . 'EvaluatorInterface',
                ],
            ],
        ];
    }

    public function getElementProvider()
    {
        return [
            [
                'name' => '',
                'instances' => ['PMSEElement', 'PMSERunnable'],
            ],
            [
                'name' => 'PMSEActivity',
                'instances' => ['PMSEActivity', 'PMSERunnable'],
            ],
        ];
    }

    public function getPMSEObjectProvider()
    {
        return [
            // parser
            ['name' => 'PMSEBusinessRuleParser'],
            // Elements
            ['name' => 'PMSEBusinessRule'],
            // Handlers
            ['name' => 'PMSECaseFlowHandler'],
            // PreProcessor
            ['name' => 'PMSERequest'],
            // wrappers
            ['name' => 'PMSECaseWrapper'],
            // engine
            ['name' => 'PMSEEvalCriteria'],
        ];
    }

    public function getPMSEObjectThrowsExceptionProvider()
    {
        return [
            ['name' => ''],
            ['name' => 'FooeyFooFoo'],
        ];
    }
}
