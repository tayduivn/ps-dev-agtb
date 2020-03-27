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

namespace Sugarcrm\SugarcrmTestsUnit\inc;

use PHPUnit\Framework\TestCase;

require_once 'include/utils.php';

/**
 * @coversDefaultClass \Configurator
 */
class UtilsTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        parent::setUp();
        $this->sugar_config_bak = $GLOBALS['sugar_config'] ?? [];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown() : void
    {
        $GLOBALS['sugar_config'] = $this->sugar_config_bak;
        parent::tearDown();
    }
    /**
     * Provider for testSugarArrayMergeRecursive
     */
    public function providerTestSugarArrayMergeRecursive()
    {
        return [
            'arrays are sequential' => [
                ['test' => [1, 2, 3]],
                ['test' => [5, 6]],
                ['test' => [5, 6]],
            ],
            'arrays are associative' => [
                [
                    'full_text_engine' => [
                        'Elastic' => [
                            'host' => 'localhost',
                            'port' => '9200',
                        ],
                    ],
                ],
                [
                    'full_text_engine' => [
                        'Elastic' => [
                            'port' => '9201',
                        ],
                    ],
                ],
                [
                    'full_text_engine' => [
                        'Elastic' => [
                            'host' => 'localhost',
                            'port' => '9201',
                        ],
                    ],
                ],
            ],
            'one array is sequential and another is associative' => [
                ['test' => [1, 2, 3]],
                ['test' => ['key' => 'value']],
                ['test' => [1, 2, 3, 'key' => 'value']],
            ],
            'left array is empty and another is associative' => [
                ['test' => []],
                ['test' => ['key' => 'value']],
                ['test' => ['key' => 'value']],
            ],
            'right array is empty and another is associative' => [
                ['test' => ['key' => 'value']],
                ['test' => []],
                ['test' => ['key' => 'value']],
            ],
            'left array is empty and another is sequential' => [
                ['test' => []],
                ['test' => [1, 2, 3]],
                ['test' => [1, 2, 3]],
            ],
            'right array is empty and another is sequential' => [
                ['test' => [1, 2, 3]],
                ['test' => []],
                ['test' => []],
            ],
        ];
    }

    /**
     * @covers \sugarArrayMergeRecursive
     * @param array $target
     * @param array $override
     * @param array $result
     * @dataProvider providerTestSugarArrayMergeRecursive
     */
    public function testSugarArrayMergeRecursive($target, $override, $result)
    {
        $this->assertEquals($result, \sugarArrayMergeRecursive($target, $override));
    }

    /**
     * @covers \getValueFromConfig
     */
    public function testGetValueFromConfig()
    {
        $GLOBALS['sugar_config']['berry'] = true;

        $this->assertEquals(true, \getValueFromConfig('berry'));
    }

    /**
     * @dataProvider isFalsyDataProvider
     * @covers \isFalsy
     * @param mixed $value param to pass
     * @param bool $expected expected return
     */
    public function testIsFalsy($value, $expected)
    {
        $this->assertEquals($expected, \isFalsy($value));
    }

    public function isFalsyDataProvider()
    {
        return [
            ['value' => false, 'expected' => true],
            ['value' => 'false', 'expected' => true],
            ['value' => 0, 'expected' => true],
            ['value' => '0', 'expected' => true],
            ['value' => 'off', 'expected' => true],
            ['value' => true, 'expected' => false],
            ['value' => 'banana', 'expected' => false],
            ['value' => -1, 'expected' => false],
        ];
    }
}
