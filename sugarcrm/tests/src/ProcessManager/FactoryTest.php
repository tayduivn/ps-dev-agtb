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

    public function getFieldEvaluatorProvider()
    {
        $nsRoot = 'Sugarcrm\\Sugarcrm\\ProcessManager\\Field\\Evaluator\\';

        return array(
            array(
                'def' => array(
                    'type' => 'int',
                ),
                'instances' => array(
                    $nsRoot . 'Base',
                    $nsRoot . 'EvaluatorInterface',
                ),
            ),
            // Tests date type fields
            array(
                'def' => array(
                    'type' => 'date',
                ),
                'instances' => array(
                    $nsRoot . 'Datetime',
                    $nsRoot . 'EvaluatorInterface',
                ),
            ),
            // tests time type fields
            array(
                'def' => array(
                    'type' => 'time',
                ),
                'instances' => array(
                    $nsRoot . 'Datetime',
                    $nsRoot . 'EvaluatorInterface',
                ),
            ),
            // tests datetimecombo type fields
            array(
                'def' => array(
                    'type' => 'datetimecombo',
                ),
                'instances' => array(
                    $nsRoot . 'Datetime',
                    $nsRoot . 'EvaluatorInterface',
                ),
            ),
            // Tests datetime type fields
            array(
                'def' => array(
                    'type' => 'datetime',
                ),
                'instances' => array(
                    $nsRoot . 'Datetime',
                    $nsRoot . 'EvaluatorInterface',
                ),
            ),
            array(
                'def' => array(
                    'custom_type' => 'foobar',
                ),
                'instances' => array(
                    $nsRoot . 'Base',
                    $nsRoot . 'EvaluatorInterface',
                ),
            ),
        );
    }

    public function getElementProvider()
    {
        return array(
            array(
                'name' => '',
                'instances' => array('PMSEElement', 'PMSERunnable'),
            ),
            array(
                'name' => 'PMSEActivity',
                'instances' => array('PMSEActivity', 'PMSERunnable'),
            ),
        );
    }
}
