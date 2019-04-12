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

class OpportunitySalesStageExpressionTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function setUp() {
        SugarTestHelper::setUp('app_list_strings');

        $GLOBALS['app_list_strings'] = array(
            'sales_stage_dom' => array (
                'Prospecting' => 'Prospecting',
                'Qualification' => 'Qualification',
                'Needs Analysis' => 'Needs Analysis',
                'Value Proposition' => 'Value Proposition',
                'Id. Decision Makers' => 'Id. Decision Makers',
                'Perception Analysis' => 'Perception Analysis',
                'Proposal/Price Quote' => 'Proposal/Price Quote',
                'Negotiation/Review' => 'Negotiation/Review',
                'Closed Won' => 'Closed Won',
                'Closed Lost' => 'Closed Lost',
            ),
        );
    }

    public function tearDown()
    {
        Forecast::$settings = array();
        unset($GLOBALS['app_list_strings']);
        SugarTestHelper::tearDown();
    }

    public static function evaluateDataProvider()
    {
        $noClosed = array(
            array(
                'sales_stage' => 'Prospecting',
            ),
            array(
                'sales_stage' => 'Proposal/Price Quote',
            ),
            array(
                'sales_stage' => 'Qualification',
            ),
        );

        $oneClosedWon = array(
            array(
                'sales_stage' => 'Prospecting',
            ),
            array(
                'sales_stage' => 'Closed Won',
            ),
            array(
                'sales_stage' => 'Qualification',
            ),
        );

        $allClosed = array(
            array(
                'sales_stage' => 'Closed Won',
            ),
            array(
                'sales_stage' => 'Closed Lost',
            ),
            array(
                'sales_stage' => 'Closed Lost',
            ),
        );

        $allCustomClosed = array(
            array(
                'sales_stage' => 'Closed Test Won',
            ),
            array(
                'sales_stage' => 'Closed Lost',
            ),
            array(
                'sales_stage' => 'Closed Lost',
            ),
        );

        $allClosedLost = array(
            array(
                'sales_stage' => 'Closed Lost',
            ),
            array(
                'sales_stage' => 'Closed Lost',
            ),
            array(
                'sales_stage' => 'Closed Lost',
            ),
        );

        $oneRLI = array(
            array(
                'sales_stage' => 'Prospecting',
            ),
        );

        return array(
            array(
                'Proposal/Price Quote',
                $noClosed,
            ),
            array(
                'Qualification',
                $oneClosedWon,
            ),
            array(
                'Closed Lost',
                $allClosedLost,
            ),
            array(
                'Closed Test Won',
                $allClosed,
            ),
            array(
                'Closed Test Won',
                $allCustomClosed,
            ),
            array(
                'Prospecting',
                $oneRLI,
            ),
        );
    }

    /**
     * @dataProvider evaluateDataProvider
     * @param $probability
     * @param $expected
     * @param $range_type
     * @param array $ranges
     * @throws Exception
     */
    public function testEvaluate($expected, $beanData)
    {
        Forecast::$settings = array(
            'is_setup' => 1,
            'sales_stage_won' => ['Closed Test Won', 'Closed Won'],
            'sales_stage_lost' => ['Closed Lost'],
        );

        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save', 'load_relationship'))
            ->getMock();


        $link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $opp->revenuelineitems = $link2;

        $rlis = array();
        // lets create 3 rlis which with 10 * the index, which will give us the total of 60
        for ($x = 0; $x < count($beanData); $x++) {
            $rli = $this->getMockBuilder('RevenueLineItem')
                ->setMethods(array('save'))
                ->getMock();

            $rli->sales_stage = $beanData[$x]['sales_stage'];

            $rlis[] = $rli;
        }

        $opp->expects($this->any())
            ->method('load_relationship')
            ->will($this->returnValue(true));

        $link2->expects($this->any())
            ->method('getBeans')
            ->will($this->returnValue($rlis));

        $expr = 'opportunitySalesStage($revenuelineitems, "sales_stage")';

        $result = Parser::evaluate($expr, $opp)->evaluate();

        $this->assertSame($expected, $result);
    }
}
