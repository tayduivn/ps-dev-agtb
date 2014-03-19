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
    private $_old_sugar_config = null;

    protected function setUp()
    {
        parent::setUp();

        $this->_old_sugar_config = $GLOBALS['sugar_config'];
        $GLOBALS['sugar_config'] = array();
    }

    protected function tearDown()
    {
        $conf = SugarConfig::getInstance();
        $conf->clearCache();
        $GLOBALS['sugar_config'] = $this->_old_sugar_config;

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    private function _addKeyValueToConfig($key, $value)
    {
        $GLOBALS['sugar_config'][$key] = $value;
    }

    /**
     * @ticket 396
     * @dataProvider providerOverride
     */
    public function test_override_value_to_string_recursive2($array_name, $value_name, $value, $config, $expected)
    {
        $this->_addKeyValueToConfig($value_name, $config);
        $this->assertEquals($expected, override_value_to_string_recursive2($array_name, $value_name, $value));
    }

    public function providerOverride()
    {
        $returnArray = array(
            array( // Append: sequential array exists in config.php
                "sugar_config",
                "http_referer_396",
                array('list' => array(0 => 'location.com')), // structure from config_override.php
                array('list' => array(0 => 'abc.com', 1 => '123.com', 2 => 'location.com')), // merged config
                "\$sugar_config['http_referer_396']['list'][] = 'location.com';\n"
            ),
            array( // Override: does not exist in config.php
                "sugar_config",
                "full_text_engine_396",
                array('Elastic' => array('curl' => array(123 => 'user:password'))), // structure from config_override.php
                array('Elastic' => array('curl' => array(123 => 'user:password'))), // merged config
                "\$sugar_config['full_text_engine_396']['Elastic']['curl']['123'] = 'user:password';\n"
            ),
            array( // Override: key is a string
                "sugar_config",
                "test_396",
                array('def' => 'def'), // structure from config_override.php
                array('abc' => 'abc', 'def' => 'def'), // merged config
                "\$sugar_config['test_396']['def'] = 'def';\n"
            ),
            array( // Override: not config related
                "app_list_strings",
                "http_referer_396",
                array('list' => array(0 => 'location.com')), // structure from config_override.php
                array(),
                "\$app_list_strings['http_referer_396']['list']['0'] = 'location.com';\n"
            ),
        );
        return $returnArray;
    }
}
