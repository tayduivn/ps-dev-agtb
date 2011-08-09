<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once 'include/SugarObjects/SugarConfig.php';

class SugarConfigTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_old_sugar_config = null;

    public function setUp() 
    {
        $this->_old_sugar_config = $GLOBALS['sugar_config'];
        $GLOBALS['sugar_config'] = array();
    }

    public function tearDown() 
    {
        $config = SugarConfig::getInstance();
        $config->clearCache();
        $GLOBALS['sugar_config'] = $this->_old_sugar_config;
    }

    /**
     * Stores a key/value pair in the config
     *
     * @internal override this in sub-classes if you are testing with the
     *           config data stored somewhere other than the $sugar_config
     *           super global
     * @param string $key
     * @param string $value
     */
    private function _addKeyValueToConfig(
        $key, 
        $value
        ) 
    {
        $GLOBALS['sugar_config'][$key] = $value;
    }

    private function _generateRandomValue() 
    {
        $this->_random = 'Some Random Foobar: ' . rand(10000, 20000);
        return $this->_getLastRandomValue();
    }

    private function _getLastRandomValue() 
    {
        return $this->_random;
    }

    public function testGetInstanceReturnsASugarConfigObject() 
    {
        $this->assertTrue(SugarConfig::getInstance() instanceOf SugarConfig, 'Returned object is not a SugarConfig object');
    }

    public function testGetInstanceReturnsASingleton() 
    {
        $one = SugarConfig::getInstance();
        $two = SugarConfig::getInstance();
        $this->assertSame($one, $two);
    }

    public function testReadsGlobalSugarConfigArray() 
    {
        for ($i = 0; $i < 10; $i++) {
            $anonymous_key = 'key-' . $i;
            $random_value = rand(10000, 20000);
            $rawConfigArray[$anonymous_key] = $random_value;
            $this->_addKeyValueToConfig($anonymous_key, $random_value);
        }

        $config = SugarConfig::getInstance();
        foreach ($rawConfigArray as $key => $value) {
            $this->assertEquals(
                $config->get($key), $value,
                "SugarConfig::get({$key}) should be equal to {$value}, got " . $config->get($key)
            );
        }
    }

    public function testAllowDotNotationForSubValuesWithinTheConfig() 
    {
        $random_value = 'Some Random Integer: ' . rand(1000, 2000);
        $this->_addKeyValueToConfig('grandparent', array(
                'parent' => array(
                'child' => $random_value,
            ),
        ));

        $config = SugarConfig::getInstance();
        $this->assertEquals($random_value, $config->get('grandparent.parent.child'));
    }

    public function testReturnsNullOnUnknownKey() 
    {
        $config = SugarConfig::getInstance();
        $this->assertNull($config->get('unknown-and-unknowable'));
    }

    public function testReturnsNullOnUnknownKeyWithinAHeirarchy() 
    {
        $this->_addKeyValueToConfig('grandparent', array(
            'parent' => array(
                'child' => 'foobar',
            ),
        ));
        $config= SugarConfig::getInstance();

        $this->assertNull($config->get('some-unknown-grandparent.parent.child'));
        $this->assertNull($config->get('grandparent.some-unknown-parent.child'));
        $this->assertNull($config->get('grandparent.parent.some-unknown-child'));
    }

    public function testAllowSpecifyingDefault() 
    {
        $config = SugarConfig::getInstance();

        $random = rand(10000, 20000);
        $this->assertSame($random, $config->get('unknown-and-unknowable', $random));
    }

    public function testAllowSpecifyingDefaultForSubValues() 
    {
        $this->_addKeyValueToConfig('grandparent', array(
            'parent' => array(
                'child' => 'foobar',
            ),
        ));
        $config = SugarConfig::getInstance();

        $this->assertEquals(
            $this->_generateRandomValue(),
            $config->get(
                'some-unknown-grandparent.parent.child',
                $this->_getLastRandomValue()
            )
        );
        $this->assertEquals(
            $this->_generateRandomValue(),
            $config->get(
                'grandparent.some-unknown-parent.child',
                $this->_getLastRandomValue()
            )
        );
        $this->assertEquals(
            $this->_generateRandomValue(),
            $config->get(
                'grandparent.parent.some-unknown-child',
                $this->_getLastRandomValue()
            )
        );
    }

    public function testStoresValuesInMemoryAfterFirstLookup() 
    {
        $this->_addKeyValueToConfig('foobar', 'barfoo');

        $config = SugarConfig::getInstance();
        $this->assertEquals($config->get('foobar'), 'barfoo');

        $this->_addKeyValueToConfig('foobar', 'foobar');
        $this->assertEquals($config->get('foobar'), 'barfoo', 'should still be equal "barfoo": got ' . $config->get('foobar'));
    }

    public function testCanClearsCachedValues() 
    {
        $this->_addKeyValueToConfig('foobar', 'barfoo');

        $config = SugarConfig::getInstance();
        $this->assertEquals($config->get('foobar'), 'barfoo', 'sanity check');
        $this->_addKeyValueToConfig('foobar', 'foobar');
        $this->assertEquals($config->get('foobar'), 'barfoo', 'sanity check');

        $config->clearCache();
        $this->assertEquals($config->get('foobar'), 'foobar', 'after clearCache() call, new value should be used');
    }

    public function testCanCherryPickKeyToClear() 
    {
        $this->_addKeyValueToConfig('foobar', 'barfoo');
        $this->_addKeyValueToConfig('barfoo', 'barfoo');

        $config = SugarConfig::getInstance();
        $this->assertEquals($config->get('foobar'), 'barfoo', 'sanity check, got: ' . $config->get('foobar'));
        $this->assertEquals($config->get('barfoo'), 'barfoo', 'sanity check');

        $this->_addKeyValueToConfig('foobar', 'foobar');
        $this->_addKeyValueToConfig('barfoo', 'foobar');
        $this->assertEquals($config->get('foobar'), 'barfoo', 'should still be equal to "barfoo", got: ' . $config->get('barfoo'));
        $this->assertEquals($config->get('barfoo'), 'barfoo', 'should still be equal to "barfoo", got: ' . $config->get('barfoo'));

        $config->clearCache('barfoo');
        $this->assertEquals($config->get('barfoo'), 'foobar', 'should be equal to "foobar" after cherry picked for clearing');
        $this->assertEquals($config->get('foobar'), 'barfoo', 'should not be effected by cherry picked clearCache() call');
    }

    public function testDemonstrateGrabbingSiblingNodes() 
    {
        $this->_addKeyValueToConfig('foobar', array(
            'foo' => array(
                array(
                    'first' => 'one',
                ),
                array(
                    'first' => 'uno',
                ),
            ),
        ));

        $config = SugarConfig::getInstance();
        $this->assertEquals($config->get('foobar.foo.0.first'), 'one');
        $this->assertEquals($config->get('foobar.foo.1.first'), 'uno');
    }
}

