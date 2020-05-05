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

use PHPUnit\Framework\TestCase;

class SugarSpotTest extends TestCase
{
    protected function setUp() : void
    {
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }
    
    protected function tearDown() : void
    {
        unset($GLOBALS['app_strings']);
    }
    
    /**
     * @ticket 41236
     */
    public function testSearchGrabsModuleDisplayName()
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppListString('moduleList', ['Foo'=>'Bar']);
        $langpack->save();
        
        $result = [
            'Foo' => [
                'data' => [
                    [
                        'ID' => '1',
                        'NAME' => 'recordname',
                        ],
                    ],
                'pageData' => [
                    'offsets' => [
                        'total' => 1,
                        'next' => 0,
                        ],
                    'bean' => [
                        'moduleDir' => 'Foo',
                        ],
                    ],
                ],
                'readAccess' => true,
            ];
        
        $sugarSpot = $this->createPartialMock('SugarSpot', ['_performSearch']);
        $sugarSpot->expects($this->any())
            ->method('_performSearch')
            ->will($this->returnValue($result));
            
        $returnValue = $sugarSpot->searchAndDisplay('', '');

        $this->assertMatchesRegularExpression('/Bar/', $returnValue);
    }

    /**
     * @ticket 43080
     */
    public function testSearchGrabsMore()
    {
        $app_strings = return_application_language($GLOBALS['current_language']);
        $this->assertTrue(array_key_exists('LBL_SEARCH_MORE', $app_strings));

        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppString('LBL_SEARCH_MORE', 'XXmoreXX');
        $langpack->save();
        
        $result = [
            'Foo' => [
                'data' => [
                    [
                        'ID' => '1',
                        'NAME' => 'recordname',
                        ],
                    ],
                'pageData' => [
                    'offsets' => [
                        'total' => 100,
                        'next' => 0,
                        ],
                    'bean' => [
                        'moduleDir' => 'Foo',
                        ],
                    ],
                ],
                'readAccess' => true,
            ];
        
        $sugarSpot = $this->createPartialMock('SugarSpot', ['_performSearch']);
        $sugarSpot->expects($this->any())
            ->method('_performSearch')
            ->will($this->returnValue($result));
            
        $returnValue = $sugarSpot->searchAndDisplay('', '');

        $this->assertStringNotContainsString('(99 more)', $returnValue);
        $this->assertStringContainsString('(99 XXmoreXX)', $returnValue);
    }


    /**
     * providerTestSearchType
     * This is the provider function for testFilterSearchType
     */
    public function providerTestSearchType()
    {
        return [
              ['phone', '777', true],
              ['phone', '(777)', true],
              ['phone', '%777', true],
              ['phone', '77', false],
              ['phone', '%77) 7', false],
              ['phone', '88-88-88', false],
              ['int', '1', true],
              ['int', '1.0', true],
              ['int', '.1', true],
              ['int', 'a', false],
              ['decimal', '1.0', true],
              ['decimal', '1', true],
              ['decimal', '1,000', true],
              ['decimal', 'aaaaa', false],
              ['float', '1.0', true],
              ['float', '1', true],
              ['float', '1,000', true],
              ['float', 'aaaaa', false],
              ['id', '1', false],
              ['datetime', '2011-01-01 10:10:10', false],
              ['date', '2011-01-01', false],
              ['bool', true, false],
              ['bool', false, false],
              ['foo', 'foo', true],
        ];
    }

    /**
     * testFilterSearchType
     * This function uses a provider to test the filter search type
     * @dataProvider providerTestSearchType
     */
    public function testFilterSearchType($type, $query, $expected)
    {
        $sugarSpot = new Bug50484SugarSpotMock();
        $this->assertEquals(
            $expected,
            $sugarSpot->filterSearchType($type, $query),
            ('SugarSpot->filterSearchType expected type ' . $type . ' with value ' . $query . ' to return ' . $expected ? 'true' : false)
        );
    }

    /**
     * @dataProvider getOptionProvider
     */
    public function testGetOption($options, $name, $module, $expected)
    {
        $sugarSpot = new Bug50484SugarSpotMock();
        $actual = $sugarSpot->getOption($options, $name, $module);
        $this->assertEquals($expected, $actual);
    }

    public static function getOptionProvider()
    {
        return [
            'none-provided' => [
                [],
                'foo',
                null,
                null,
            ],
            'global-provided' => [
                [
                    'foo' => 'bar',
                ],
                'foo',
                null,
                'bar',
            ],
            'module-specific-provided' => [
                [
                    'modules' => [
                        'Accounts' => [
                            'foo' => 'baz',
                        ],
                    ],
                ],
                'foo',
                'Accounts',
                'baz',
            ],
            'both-provided' => [
                [
                    'foo' => 'bar',
                    'modules' => [
                        'Accounts' => [
                            'foo' => 'baz',
                        ],
                    ],
                ],
                'foo',
                'Accounts',
                'baz',
            ],
        ];
    }
}


class Bug50484SugarSpotMock extends SugarSpot
{
    public function filterSearchType($type, $query)
    {
        return parent::filterSearchType($type, $query);
    }

    public function getOption(array $options, $name, $module = null)
    {
        return parent::getOption($options, $name, $module);
    }
}
