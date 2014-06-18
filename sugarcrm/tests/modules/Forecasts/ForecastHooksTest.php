<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/Forecasts/ForecastHooks.php';

class ForecastHooksTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testSetCommitStageWhenNotSetup()
    {
        $hook = new MockForecastHooks();

        /* @var $bean Opportunity */
        $bean = $this->getMock('Opportunity', array('save'));
        $bean->probability = 90;

        /* @var $hook ForecastHooks */
        $hook->setCommitStageIfEmpty($bean, 'before_save');

        $this->assertEmpty($bean->commit_stage, $bean->commit_stage);
    }

    public function testSetCommitStageToInclude()
    {
        $hook = new MockForecastHooks();

        $settings = array(
            'forecast_ranges' => 'show_binary',
            'show_binary_ranges' => array(
                'include' =>
                array(
                    'min' => 70,
                    'max' => 100,
                    'in_included_total' => true,
                ),
                'exclude' =>
                array(
                    'min' => 0,
                    'max' => 69,
                ),
            )
        );

        /* @var $bean Opportunity */
        $bean = $this->getMock('Opportunity', array('save'));
        $bean->probability = 90;

        /* @var $hook ForecastHooks */
        $hook::$settings = $settings;
        $hook->setCommitStageIfEmpty($bean, 'before_save');

        $this->assertEquals('include', $bean->commit_stage);
    }

    public function testSetCommitStageToExclude()
    {
        $hook = new MockForecastHooks();

        $settings = array(
            'forecast_ranges' => 'show_binary',
            'show_binary_ranges' => array(
                'include' =>
                array(
                    'min' => 70,
                    'max' => 100,
                    'in_included_total' => true,
                ),
                'exclude' =>
                array(
                    'min' => 0,
                    'max' => 69,
                ),
            )
        );

        /* @var $bean Opportunity */
        $bean = $this->getMock('Opportunity', array('save'));
        $bean->probability = 50;

        /* @var $hook ForecastHooks */
        $hook::$settings = $settings;
        $hook->setCommitStageIfEmpty($bean, 'before_save');

        $this->assertEquals('exclude', $bean->commit_stage);
    }

    public function testSetCommitStageDoesNotChangeValue()
    {
        $hook = new MockForecastHooks();

        $settings = array(
            'forecast_ranges' => 'show_binary',
            'show_binary_ranges' => array(
                'include' =>
                array(
                    'min' => 70,
                    'max' => 100,
                    'in_included_total' => true,
                ),
                'exclude' =>
                array(
                    'min' => 0,
                    'max' => 69,
                ),
            )
        );

        /* @var $bean Opportunity */
        $bean = $this->getMock('Opportunity', array('save'));
        $bean->probability = 50;
        $bean->commit_stage = 'include';

        /* @var $hook ForecastHooks */
        $hook::$settings = $settings;
        $hook->setCommitStageIfEmpty($bean, 'before_save');

        $this->assertEquals('include', $bean->commit_stage);
    }

    public function testSetBestWorstEqualToLikelyAmountWorks()
    {
        $hook = new MockForecastHooks();

        /** @var Opportunity $bean */
        $bean = $this->getMock('Opportunity', array('save'));
        $bean->amount = 500;
        $bean->best_case = 600;
        $bean->worst_case = 400;
        $bean->sales_stage = 'Closed Won';

        /* @var $hook ForecastHooks */
        $hook->setBestWorstEqualToLikelyAmount($bean, 'before_save');

        $this->assertEquals($bean->amount, $bean->best_case);
        $this->assertEquals($bean->amount, $bean->worst_case);
    }

    public function testSetBestWorstEqualToLikelyAmountDoesntCopyValues()
    {
        $hook = new MockForecastHooks();

        /** @var Opportunity $bean */
        $bean = $this->getMock('Opportunity', array('save'));
        $bean->amount = 500;
        $bean->best_case = 600;
        $bean->worst_case = 400;
        $bean->sales_stage = 'Prospecting';

        /* @var $hook ForecastHooks */
        $hook->setBestWorstEqualToLikelyAmount($bean, 'before_save');

        $this->assertEquals(600, $bean->best_case);
        $this->assertEquals(400, $bean->worst_case);
    }
}

class MockForecastHooks extends ForecastHooks
{
    public static function isForecastSetup()
    {
        return true;
    }

    public static function getForecastClosedStages()
    {
        return array('Closed Won');
    }
}
