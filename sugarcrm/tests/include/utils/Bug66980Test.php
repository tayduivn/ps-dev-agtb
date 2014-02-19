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

require_once 'include/utils/array_utils.php';

class Bug66980Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function test_override_value_to_string_recursive2()
    {
        $array_name = "sugar_config";
        $value_name = "http_referer";
        $value= array('list' => array(0 => 'test.location.com'));

        $expected = "\$sugar_config['http_referer']['list'][] = 'test.location.com';\n";

        $this->assertEquals($expected, override_value_to_string_recursive2($array_name, $value_name, $value));
    }
}