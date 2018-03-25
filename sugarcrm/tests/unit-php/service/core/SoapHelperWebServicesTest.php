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

namespace Sugarcrm\SugarcrmTestsUnit\data;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;

/**
 * @coversDefaultClass \SoapHelperWebServices
 */
class SoapHelperWebServicesTest extends TestCase
{
    private $helper;
    private $idmConfig;

    protected function setUp()
    {
        $this->idmConfig = $this->createMock(Config::class);
        $this->helper = $this->getMockBuilder(\SoapHelperWebServices::class)
            ->setMethods(['getIDMConfig'])
            ->getMock();
        $this->helper->method('getIDMConfig')
            ->willReturn($this->idmConfig);
    }

    /**
     * @dataProvider isIDMModeDataProvider
     * @covers ::isIDMMode()
     */
    public function testIsIDMMode($idmModeEnabled, $expected)
    {
        $this->idmConfig->expects($this->once())
            ->method('isIDMModeEnabled')
            ->willReturn($idmModeEnabled);
        $this->assertEquals($expected, $this->helper->isIDMMode());
    }

    public function isIDMModeDataProvider()
    {
        return [
            'enabled' => [true, true],
            'disabled' => [false, false],
        ];
    }

    /**
     * @dataProvider isIDMModeModuleDataProvider
     * @covers ::isIDMModeModule()
     */
    public function testIsIDMModeModule($module, $expected)
    {
        $this->idmConfig->expects($this->once())
            ->method('getIDMModeDisabledModules')
            ->willReturn(['Users', 'Employees']);
        $this->assertEquals($expected, $this->helper->isIDMModeModule($module));
    }

    public function isIDMModeModuleDataProvider()
    {
        return [
            ['Users', true],
            ['Employees', true],
            ['Accounts', false],
        ];
    }

    /**
     * @dataProvider isIDMModeFieldProvider
     * @covers ::isIDMModeField()
     */
    public function testIsIDMModeField($field, $expected)
    {
        $this->idmConfig->expects($this->once())
            ->method('getIDMModeDisabledFields')
            ->willReturn([
                'first_name' => ['firstnamevardefs'],
                'last_name' => ['lastnamevardefs'],
            ]);
        $this->assertEquals($expected, $this->helper->isIDMModeField($field));
    }

    public function isIDMModeFieldProvider()
    {
        return [
            ['first_name', true],
            ['salutation', false],
        ];
    }
}
