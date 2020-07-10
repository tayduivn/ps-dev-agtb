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
namespace Sugarcrm\SugarcrmTestsUnit\modules\Opportunities;

use Opportunity;
use OpportunityHooks;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass OpportunityHooks
 */
class OpportunityHooksTest extends TestCase
{
    /**
     * @covers ::generateRenewalOpportunity()
     */
    public function testGenerateRenewalOpportunity()
    {
        $rliBean = $this->createMock('RevenueLineItem');
        // three cases using one RLI, so we expect save to be called three times total
        $rliBean->expects($this->exactly(3))->method('save');
        $newRliBean = $this->createMock('RevenueLineItem');
        $newRliBean->id = 'id';
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
            'canRenew',
        ]);
        $opBean->method('canRenew')->willReturn(true);
        $opBean->method('getClosedWonRenewableRLIs')->willReturn([$rliBean]);
        $opBean->method('getRenewalParent')->willReturn($parentBean);
        $args['dataChanges']['sales_status']['after'] = Opportunity::STATUS_CLOSED_WON;
        $this->assertTrue(OpportunityHooks::generateRenewalOpportunity($opBean, 'after_save', $args));

        // check that the renewal RLI ID on the original RLI was set
        $this->assertEquals($rliBean->renewal_rli_id, $newRliBean->id);

        // case2: existing renewal
        $opBean = $this->createPartialMock('Opportunity', [
            'getClosedWonRenewableRLIs',
            'getRenewalParent',
            'getExistingRenewalOpportunity',
            'canRenew',
        ]);
        $opBean->method('canRenew')->willReturn(true);
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
            'canRenew',
        ]);
        $opBean->method('canRenew')->willReturn(true);
        $opBean->method('getClosedWonRenewableRLIs')->willReturn([$rliBean]);
        $opBean->method('getRenewalParent')->willReturn(null);
        $opBean->method('getExistingRenewalOpportunity')->willReturn(null);
        $opBean->method('createNewRenewalOpportunity')->willReturn($renewalBean);
        $this->assertTrue(OpportunityHooks::generateRenewalOpportunity($opBean, 'after_save', $args));
    }
}
