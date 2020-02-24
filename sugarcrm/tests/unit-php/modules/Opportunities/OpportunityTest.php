<?php
// FILE SUGARCRM flav=ent ONLY
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
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;

/**
 * @coversDefaultClass Opportunity
 */
class OpportunityTest extends TestCase
{
    protected function setUp()
    {
        \BeanFactory::setBeanClass('RevenueLineItems', 'RliMock');
        \BeanFactory::setBeanClass('Opportunities', 'OpMock');
    }

    protected function tearDown()
    {
        \BeanFactory::unsetBeanClass('RevenueLineItems');
        \BeanFactory::unsetBeanClass('Opportunities');
    }

    /**
     * @covers ::getClosedWonRenewableRLIs()
     */
    public function testGetClosedWonRenewableRLIs()
    {
        $opBean = $this->createPartialMock('Opportunity', [
            'load_relationship',
            'getRLIClosedWonStages',
        ]);
        $opBean->method('getRLIClosedWonStages')->willReturn(['Closed Won']);
        $opBean->method('load_relationship')->willReturn(true);
        $link2Mock = $this->createPartialMock('Link2', [
            'getRelatedModuleName',
            'getBeans',
        ]);
        $link2Mock->method('getRelatedModuleName')->willReturn('RevenueLineItems');
        $t = 'revenue_line_items';
        $link2Mock->expects($this->once())
            ->method('getBeans')
            ->with([
                'where' => "$t.service = 1 AND $t.sales_stage in ('Closed Won') AND $t.renewable = 1",
            ])
            ->willReturn([]);
        $opBean->revenuelineitems = $link2Mock;
        $opBean->getClosedWonRenewableRLIs();
    }

    /**
     * @covers ::getExistingRenewalOpportunity()
     */
    public function testGetExistingRenewalOpportunity()
    {
        $mockDb = TestMockHelper::getMockForAbstractClass(
            $this,
            '\\DBManager',
            [
                'quoted',
            ]
        );
        $mockDb->method('quoted')->will(
            $this->returnValueMap(
                [
                    ['Closed Won', '\'Closed Won\''],
                    ['Closed Lost', '\'Closed Lost\''],
                ]
            )
        );
        $t = 'opportunities';
        $opBean = $this->createPartialMock('Opportunity', [
            'load_relationship',
            'getTableName',
        ]);
        $opBean->method('getTableName')->willReturn($t);
        $opBean->method('load_relationship')->willReturn(true);
        $opBean->db = $mockDb;
        $link2Mock = $this->createPartialMock('Link2', [
            'getBeans',
        ]);
        $where = "$t.sales_status != 'Closed Won' AND $t.sales_status != 'Closed Lost' AND $t.renewal = 1";
        $link2Mock->expects($this->once())
        ->method('getBeans')
        ->with([
            'where' => $where,
        ])
        ->willReturn([]);
        $opBean->renewal_opportunities = $link2Mock;
        $opBean->getExistingRenewalOpportunity();
    }

    /**
     * @covers ::createNewRenewalOpportunity()
     */
    public function testCreateNewRenewalOpportunity()
    {
        $opBean = $this->createPartialMock('Opportunity', [
            'getModuleName',
        ]);
        $opBean->method('getModuleName')->willReturn('Opportunities');
        $opBean->id = 'id';
        $opBean->name = 'name';
        $renewalBean = $opBean->createNewRenewalOpportunity();
        $this->assertEquals(1, $renewalBean->renewal, 'Op renewal should be 1');
        $this->assertEquals($opBean->id, $renewalBean->renewal_parent_id, 'Op renewal parent should be set');
        $this->assertEquals($opBean->name, $renewalBean->name, 'Op name should be copied');
    }

    /**
     * @covers ::createNewRenewalRLI()
     */
    public function testCreateNewRenewalRLI()
    {
        $opBean = $this->createPartialMock('Opportunity', [
            'load_relationship',
        ]);
        $opBean->method('load_relationship')->willReturn(true);
        $link2Mock = $this->createPartialMock('Link2', [
            'add',
        ]);
        $link2Mock->expects($this->once())
        ->method('add');
        $opBean->revenuelineitems = $link2Mock;
        $rliBean = $this->createPartialMock('RevenueLineItem', [
            'getModuleName',
        ]);
        $rliBean->method('getModuleName')->willReturn('RevenueLineItems');
        $rliBean->service_start_date = '2019-08-11';
        $rliBean->service_end_date = '2019-10-10';
        $rliBean->service_duration_value = '2';
        $rliBean->service_duration_unit = 'month';
        $newRliBean = $opBean->createNewRenewalRLI($rliBean);
        $this->assertEquals('2019-10-11', $newRliBean->service_start_date, 'New RLI start date is wrong');
    }
}

class RliMock extends RevenueLineItem
{
    public function __construct()
    {
    }

    public function getTableName()
    {
        return 'revenue_line_items';
    }

    public function save($check_notify = false)
    {
    }
}

class OpMock extends Opportunity
{
    public function __construct()
    {
    }

    public function getModuleName()
    {
        return 'Opportunities';
    }

    public function load_relationship($link)
    {
        $this->$link = new Link2Mock();
        return true;
    }

    public function findDuplicates()
    {
        return null;
    }

    public function save($check_notify = false)
    {
    }
}

class Link2Mock
{
    public function add($keys)
    {
    }
}
