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

require_once 'include/utils/expression_utils.php';

/**
 * Class ExpressionTranslateOperatorTest
 * Check if translate_operator works properly and covers all operators
 */
class ExpressionTranslateOperatorTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider operatorDataset
     */
    public function testExpressionTranslateOperator($operator, $type, $expected)
    {
        $operator = translate_operator($operator, $type);

        $this->assertEquals($expected, $operator, "Operator translated improperly");
    }

    public static function operatorDataset()
    {
        return array(
            array('Equals', 'php', '=='),
            array('Is empty', 'php', '=='),
            array('Less Than', 'php', '<'),
            array('More Than', 'php', '>'),
            array('Does not Equal', 'php', '!='),
            array('Is not empty', 'php', '!='),
            array('Equals', 'sql', '='),
            array('Is empty', 'sql', '='),
            array('Less Than', 'sql', '<'),
            array('More Than', 'sql', '>'),
            array('Does not Equal', 'sql', '!='),
            array('Is not empty', 'sql', '!='),
        );
    }
}
