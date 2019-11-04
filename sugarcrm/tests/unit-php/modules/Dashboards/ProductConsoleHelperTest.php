<?php
//FILE SUGARCRM flav=ent ONLY
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

/**
 * @coversDefaultClass \ProductConsoleHelper
 */
class ProductConsoleHelperTest extends TestCase
{
    private $bean;

    protected function setUp()
    {
        $this->bean = $this->createMock('Dashboard');
    }

    /**
     * @covers ::removeRenewalsConsole
     */
    public function testRemoveRenewalsConsole()
    {
        $helper = $this->createPartialMock('\ProductConsoleHelper', [
            'useRevenueLineItems',
        ]);
        $helper->expects($this->once())
        ->method('useRevenueLineItems')
        ->willReturn(false);
        $sugarQuery = $this->createMock('SugarQuery');
        $sugarQueryWhere = $this->createMock('SugarQuery_Builder_Where');
        $sugarQuery->expects($this->any())
            ->method('where')
            ->willReturn($sugarQueryWhere);
        $sugarQueryWhere->expects($this->once())
            ->method('notEquals')
            ->with($this->equalTo('id'), $this->equalTo(ProductConsoleHelper::$renewalsConsoleId));
        $helper->removeRenewalsConsole($this->bean, 'before_filter', [$sugarQuery]);

        $helper = $this->createPartialMock('\ProductConsoleHelper', [
            'useRevenueLineItems',
        ]);
        $helper->expects($this->once())
        ->method('useRevenueLineItems')
        ->willReturn(true);
        $sugarQuery = $this->createMock('SugarQuery');
        $sugarQuery->expects($this->never())
            ->method($this->anything());
        $helper->removeRenewalsConsole($this->bean, 'before_filter', [$sugarQuery]);
    }

    /**
     * @covers ::checkRenewalsConsole
     */
    public function testCheckRenewalsConsole()
    {
        $helper = $this->createPartialMock('\ProductConsoleHelper', [
            'useRevenueLineItems',
        ]);
        $helper->expects($this->once())
        ->method('useRevenueLineItems')
        ->willReturn(true);
        $helper->checkRenewalsConsole(
            $this->bean,
            'before_retrieve',
            ['id' => 'da438c86-df5e-11e9-9801-3c15c2c53980']
        );
        $this->assertTrue(true);

        $helper = $this->createPartialMock('\ProductConsoleHelper', [
            'useRevenueLineItems',
        ]);
        $helper->expects($this->once())
        ->method('useRevenueLineItems')
        ->willReturn(false);
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $helper->checkRenewalsConsole(
            $this->bean,
            'before_retrieve',
            ['id' => 'da438c86-df5e-11e9-9801-3c15c2c53980']
        );
    }
}
