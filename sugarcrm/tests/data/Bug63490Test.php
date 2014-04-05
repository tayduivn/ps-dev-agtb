<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * @ticket 63490
 */
class Bug63490Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarBean
     */
    private static $bean;

    public static function setUpBeforeClass()
    {
        self::$bean = new SugarBean();
        self::$bean->table_name = 'bean';
        self::$bean->field_defs = array(
            'name' => array(),
        );
    }

    /**
     * @param string $input
     * @param string $expected
     * @param bool $suppress_table_name
     * @param array $field_map
     *
     * @dataProvider correctProvider
     */
    public function testCorrectColumns(
        $input,
        $expected,
        $suppress_table_name = false,
        $field_map = array()
    ) {
        $actual = self::$bean->process_order_by(
            $input,
            null,
            $suppress_table_name,
            $field_map
        );
        $this->assertContains($expected, $actual);
        if ($suppress_table_name) {
            $this->assertContains('id', $actual);
        } else {
            $this->assertContains('bean.id', $actual);
        }
    }

    /**
     * @param string $input
     *
     * @dataProvider incorrectProvider
     */
    public function testIncorrectColumns($input)
    {
        $actual = self::$bean->process_order_by($input);
        $this->assertNotContains($input, $actual);
        $this->assertContains('bean.id', $actual);
    }

    public static function correctProvider()
    {
        return array(
            // existing field is accepted
            array('name', 'bean.name'),
            // valid order is accepted
            array('name asc', 'bean.name asc'),
            // order is case-insensitive
            array('name DeSc', 'bean.name DeSc'),
            // any white spaces are accepted
            array("\tname\t\nASC\n\r", 'bean.name ASC'),
            // invalid order is ignored
            array('name somehow', 'bean.name'),
            // everything after the first white space considered order
            array('name desc asc', 'bean.name'),
            // $suppress_table_name usage
            array('name', 'name', true),
            // $relate_field_map usage
            array('name desc', 'first_name desc, last_name desc', false, array(
                'name' => array('first_name', 'last_name'),
            )),
        );
    }

    public static function incorrectProvider()
    {
        return array(
            // non-existing field is removed
            array('title'),
            // non-existing field is removed together with order
            array('title asc'),
            // field name containing table name is removed
            array('bean.name'),
        );
    }
}
