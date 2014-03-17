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

class SugarOverrideValueTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $file_exist = false;
    protected $config_file = '';

    protected function setUp()
    {
        parent::setUp();

        if(file_exists('config.php'))
        {
            $this->config_file = file_get_contents('config.php');
            $this->file_exist = true;
        }
        else
        {
            $this->config_file = '<?php' . "\r\n";
        }
        $new_line = '$sugar_config[\'http_referer_396\'][\'list\'][0] = \'abc.com\';' . "\r\n" .
            '$sugar_config[\'http_referer_396\'][\'list\'][1] = \'123.com\';' . "\r\n" .
            '$sugar_config[\'test_396\'][\'abc\'] = \'abc\';' . "\r\n";

        SugarAutoLoader::put('config.php', $this->config_file . "\r\n" . $new_line, true);
    }

    protected function tearDown()
    {
        if ($this->file_exist == true)
        {
            SugarAutoLoader::put('config.php', $this->config_file);
        }

        global $sugar_config;
        unset($sugar_config['http_referer_396']);
        unset($sugar_config['full_text_engine_396']);
        unset($sugar_config['test_396']);

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @ticket 396
     * @dataProvider providerOverride
     */
    public function test_override_value_to_string_recursive2($array_name, $value_name, $value, $config, $expected)
    {
        global $sugar_config;

        if (!empty($config)) {
            $sugar_config[$value_name] = $config;
        }
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
            array( // Override: sequential array exists in config.php
                "sugar_config",
                "http_referer_396",
                array('list' => array(0 => 'location.com')), // structure from config_override.php
                array('list' => array(0 => 'location.com', 1 => '123.com')), // merged config
                "\$sugar_config['http_referer_396']['list']['0'] = 'location.com';\n"
            ),
            array( // Override: sequential array exists in config.php
                "sugar_config",
                "http_referer_396",
                array('list' => array(1 => 'location.com')), // structure from config_override.php
                array('list' => array(0 => 'abc.com', 1 => 'location.com')), // merged config
                "\$sugar_config['http_referer_396']['list']['1'] = 'location.com';\n"
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
