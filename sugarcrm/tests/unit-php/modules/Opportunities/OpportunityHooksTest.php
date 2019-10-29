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

/**
 * @coversDefaultClass OpportunityHooks
 */
class OpportunityHooksTest extends TestCase
{
    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @covers ::generateRenewalOpportunity()
     */
    public function testGenerateRenewalOpportunity()
    {
        $rliBean = $this->createMock('RevenueLineItem');
        $newRliBean = $this->createMock('RevenueLineItem');
        $renewalBean = $this->createMock('Opportunity', [
            'load_relationship',
            'createNewRenewalRLI',
        ]);
        $renewalBean->method('load_relationship')->willReturn(true);
        $renewalBean->expects($this->exactly(3))
        ->method('createNewRenewalRLI')
        ->with($rliBean)
        ->willReturn($newRliBean);
        // case1: parent renewal
        $parentBean = $this->createPartialMock('Opportunity', [
            'getExistingRenewalOpportunity',
        ]);
        $parentBean->expects($this->once())
        ->method('getExistingRenewalOpportunity')
        ->willReturn($renewalBean);
        $opBean = $this->createPartialMock('Opportunity', [
            'getClosedWonRenewableRLIs',
            'getRenewalParent',
            'useRevenueLineItems',
        ]);
        $opBean->method('useRevenueLineItems')->willReturn(true);
        $opBean->method('getClosedWonRenewableRLIs')->willReturn([$rliBean]);
        $opBean->method('getRenewalParent')->willReturn($parentBean);
        $args['dataChanges']['sales_status']['after'] = Opportunity::STATUS_CLOSED_WON;
        $this->assertTrue(OpportunityHooks::generateRenewalOpportunity($opBean, 'after_save', $args));
        // case2: existing renewal
        $opBean = $this->createPartialMock('Opportunity', [
            'getClosedWonRenewableRLIs',
            'getRenewalParent',
            'getExistingRenewalOpportunity',
            'useRevenueLineItems',
        ]);
        $opBean->method('useRevenueLineItems')->willReturn(true);
        $opBean->method('getClosedWonRenewableRLIs')->willReturn([$rliBean]);
        $opBean->method('getRenewalParent')->willReturn(null);
        $opBean->method('getExistingRenewalOpportunity')->willReturn($renewalBean);
        $this->assertTrue(OpportunityHooks::generateRenewalOpportunity($opBean, 'after_save', $args));
        // case3: new renewal
        $opBean = $this->createPartialMock('Opportunity', [
            'getClosedWonRenewableRLIs',
            'getRenewalParent',
            'getExistingRenewalOpportunity',
            'createNewRenewalOpportunity',
            'useRevenueLineItems',
        ]);
        $opBean->method('useRevenueLineItems')->willReturn(true);
        $opBean->method('getClosedWonRenewableRLIs')->willReturn([$rliBean]);
        $opBean->method('getRenewalParent')->willReturn(null);
        $opBean->method('getExistingRenewalOpportunity')->willReturn(null);
        $opBean->method('createNewRenewalOpportunity')->willReturn($renewalBean);
        $this->assertTrue(OpportunityHooks::generateRenewalOpportunity($opBean, 'after_save', $args));
    }
    //END SUGARCRM flav=ent ONLY
}
