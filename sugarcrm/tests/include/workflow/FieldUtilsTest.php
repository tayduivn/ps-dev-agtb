<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once 'include/workflow/field_utils.php';

/**
 * Class FieldUtilsTest
 *
 * Test field_utils.php functions
 */
class FieldUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Test value_calc advanced workflow action
     *
     * @dataProvider dataProviderAdvancedActionValueCalc
     */
    public function testAdvancedActionValueCalc($operation, $operationValue, $field, $fieldValue, $expected)
    {
        $bean = BeanFactory::getBean('Accounts');

        $metaArray = array(
            'adv_type' => 'value_calc',
            'ext1' => $operation,
            'value' => $operationValue,
        );

        $bean->$field = $fieldValue;

        $value = process_advanced_actions($bean, $field, $metaArray, $bean);
        $this->assertEquals($expected, $value, 'Value calc returns incorrect value');
    }

    public static function dataProviderAdvancedActionValueCalc()
    {
        return array(
            array('+', 1, 'test', 1, 2),
            array('-', 1, 'test', 3, 2),
            array('*', 1, 'test', 3, 3),
            array('/', 3, 'test', 3, 1),
        );
    }
}
