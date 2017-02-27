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

namespace Sugarcrm\SugarcrmTestsUnit\data\Relationships;

/**
 * @coversDefaultClass M2MRelationship
 */
class M2MRelationshipTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $GLOBALS['log'] = $this->getMockBuilder('LoggerManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        unset($GLOBALS['log']);
        parent::tearDown();
    }

    public function loadDataProvider()
    {
        return [
            'no relationship rows - left side' => [[], [], true],
            'no relationship rows - right side' => [[], [], false],
            'no ids in relationship rows - left side' => [
                [],
                [['field' => 'field-1'], ['field' => 'field-2']],
                true,
            ],
            'no ids in relationship rows - left side' => [
                [],
                [['field' => 'field-1'], ['field' => 'field-2']],
                false,
            ],
            'valid results - left side' => [
                [
                    'id-1' => ['id' => 'id-1', 'id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    'id-2' => ['id' => 'id-2', 'id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                [
                    ['id' => 'id-1', 'id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    ['id' => 'id-2', 'id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                true,
            ],
            'valid results - right side' => [
                [
                    'id-1' => ['id' => 'id-1', 'id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    'id-2' => ['id' => 'id-2', 'id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                [
                    ['id' => 'id-1', 'id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    ['id' => 'id-2', 'id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                false,
            ],
            'join key used - left side' => [
                [
                    'id-right-1' => ['id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    'id-right-2' => ['id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                [
                    ['id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    ['id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                true,
            ],
            'join key used - right side' => [
                [
                    'id-left-1' => ['id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    'id-left-2' => ['id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                [
                    ['id-left' => 'id-left-1', 'id-right' => 'id-right-1'],
                    ['id-left' => 'id-left-2', 'id-right' => 'id-right-2'],
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider loadDataProvider
     * @covers ::load
     */
    public function testLoad($expected, $queryResults, $linkIsLHS)
    {
        $def = [
            'join_key_rhs' => 'id-right',
            'join_key_lhs' => 'id-left',
            'name' => 'link-test',
            'lhs_module' => 'LeftModule',
            'rhs_module' => 'RightModule',
        ];
        $relationship = $this->getMockBuilder('M2MRelationship')
            ->setMethods(['getSugarQuery', 'linkIsLHS', 'getLinkedDefForModuleByRelationship'])
            ->setConstructorArgs([$def])
            ->getMock();
        $sugarQuery = $this->getMock('SugarQuery', [], [], '', false);
        $sugarQuery->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($queryResults));
        $relationship->expects($this->once())
            ->method('getSugarQuery')
            ->will($this->returnValue($sugarQuery));
        $relationship->expects($this->once())
            ->method('linkIsLHS')
            ->will($this->returnValue($linkIsLHS));
        $rows = $relationship->load($this->getMock('Link2', [], [], '', false), []);
        $this->assertArrayHasKey('rows', $rows);
        $this->assertEquals($expected, $rows['rows']);
    }

    /**
     * @covers ::getSugarQuery
     * @expectedException \Exception
     */
    public function testLoadException()
    {
        $def = [
            'join_key_rhs' => 'id-right',
            'join_key_lhs' => 'id-left',
            'name' => 'link-test',
            'lhs_module' => 'LeftModule',
            'rhs_module' => 'RightModule',
        ];
        $relationship = $this->getMockBuilder('M2MRelationship')
            ->setMethods(['linkIsLHS', 'getLinkedDefForModuleByRelationship'])
            ->setConstructorArgs([$def])
            ->getMock();
        $relationship->expects($this->once())
            ->method('linkIsLHS')
            ->will($this->returnValue(true));
        $relationship->load($this->getMock('Link2', [], [], '', false), []);
    }
}
