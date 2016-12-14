<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Console\Command\Thorn;

use Sugarcrm\Sugarcrm\Console\Command\Thorn\BuildMetadataCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Thorn\BuildMetadataCommand
 */
class BuildMetadataCommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    protected static $fixturePath = __DIR__ . '/../../Fixtures/Thorn/';

    /**
     * @covers ::execute
     * @covers ::filterByRequiredFields
     * @covers ::getFields
     * @covers ::getModuleList
     * @dataProvider providerTestExecute
     */
    public function testExecute($input, $output)
    {
        $cmd = $this->getMock();

        $tester = new CommandTester($cmd);
        $tester->execute($input);

        $output = self::$fixturePath . $output;

        $this->assertStringEqualsFile($output, $tester->getDisplay(true));
    }

    public function providerTestExecute()
    {
        return array(
            array(
                // no modules supplied, fallback to all available modules
                array(),
                'BuildMetadataCommand_0.txt',
            ),
            array(
                // one module supplied
                array('--modules' => 'Module1'),
                'BuildMetadataCommand_1.txt',
            ),
            array(
                // multiple modules supplied
                array('--modules' => 'Module1,Module2'),
                'BuildMetadataCommand_2.txt',
            )
        );
    }

    protected function getMock()
    {
        $mock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Console\Command\Thorn\BuildMetadataCommand')
            ->setMethods(array('getFields', 'getModuleList'))
            ->getMock();

        $mock->expects($this->any())
            ->method('getFields')
            ->will(
                $this->returnCallback(
                    function ($module) {
                        switch ($module) {
                            case 'Module1':
                                return array(
                                    'field1.1' => array(
                                        'name' => 'field1.1',
                                        'required' => true,
                                    ),
                                    'field1.2' => array(
                                        'name' => 'field1.2',
                                        'required' => false,
                                    ),
                                    'field1.3' => array(
                                        'name' => 'field1.3',
                                        'required' => true,
                                        'source' => 'non-db',
                                    ),
                                    'field1.4' => array(
                                        'name' => 'field1.4',
                                        'required' => true,
                                        'readonly' => true,
                                    ),
                                    'field1.5' => array(
                                        'name' => 'field1.5',
                                        'required' => true,
                                        'type' => 'id',
                                    )
                                );
                            case 'Module2':
                            default:
                                return array(
                                    'field2.1' => array(
                                        'name' => 'field2.1',
                                        'required' => true,
                                    ),
                                );
                        }
                    }
                )
            );

        $mock->expects($this->any())
            ->method('getModuleList')
            ->will($this->returnValue(array('Module1', 'Module2')));

        return $mock;
    }
}
