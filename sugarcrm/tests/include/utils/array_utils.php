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

class array_utils extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @ticket 396
     * @dataProvider providerOverride
     */
    public function test_override_value_to_string_recursive2($array_name, $value_name, $value, $config, $expected)
    {
        $this->assertEquals($expected, override_value_to_string_recursive2($array_name, $value_name, $value, true, $config));
    }

    public function providerOverride()
    {
        $returnArray = array(
            array( // Append: sequential array exists in config.php
                "sugar_config",
                "http_referer_396",
                array('list' => array(3 => 'location.com')), // structure from config_override.php
                array('http_referer_396' => array('list' => array(0 => 'abc.com', 1 => '123.com', 2 => 'mylocation.com'))),
                "\$sugar_config['http_referer_396']['list'][] = 'location.com';\n"
            ),
            array( // Append: non-sequential array exists in config.php
                "sugar_config",
                "http_referer_396",
                array('list' => array(3 => 'location.com')), // structure from config_override.php
                array('http_referer_396' => array('list' => array(0 => 'abc.com',  2 => 'mylocation.com'))),
                "\$sugar_config['http_referer_396']['list']['3'] = 'location.com';\n"
            ),
            array( // Append: no array exists in config.php and key = 0, treat it as append
                "sugar_config",
                "http_referer_396",
                array('list' => array(0 => 'location.com')), // structure from config_override.php
                array(),
                "\$sugar_config['http_referer_396']['list'][] = 'location.com';\n"
            ),
            array( // Override: sequential array exists in config.php but old key is overridden
                "sugar_config",
                "http_referer_396",
                array('list' => array(0 => 'otherlocation.com')), // structure from config_override.php
                array('http_referer_396' => array('list' => array(0 => 'location.com', 1 => '123.com'))),
                "\$sugar_config['http_referer_396']['list']['0'] = 'otherlocation.com';\n"
            ),
            array( // Override: does not exist in config.php
                "sugar_config",
                "full_text_engine_396",
                array('Elastic' => array('curl' => array(123 => 'user:password'))), // structure from config_override.php
                array(),
                "\$sugar_config['full_text_engine_396']['Elastic']['curl']['123'] = 'user:password';\n"
            ),
            array( // Override: key is a string
                "sugar_config",
                "test_396",
                array('def' => 'def2'), // structure from config_override.php
                array("test_396" => array('abc' => 'abc', 'def' => 'def')),
                "\$sugar_config['test_396']['def'] = 'def2';\n"
            ),
            array( // Override: test app_list_strings
                "app_list_strings",
                "http_referer_396",
                array('list' => array(0 => 'location.com')), // structure from config_override.php
                null,
                "\$app_list_strings['http_referer_396']['list']['0'] = 'location.com';\n"
            ),
        );
        return $returnArray;
    }
}
