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

require_once 'modules/Filters/upgrade/scripts/post/7_UpdateTeamsFilters.php';

/**
 * @coversDefaultClass \SugarUpgradeUpdateTeamsFilters
 */
class UpgradeUpdateTeamsFiltersTest extends TestCase
{
    /**
     * @dataProvider providerTestConvertTeamsFilterValue
     * @covers ::convertTeamsFilterValue
     */
    public function testConvertTeamsFilterValue($filter, $expected)
    {
        $script = $this->getMockBuilder('SugarUpgradeUpdateTeamsFilters')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->assertEquals($expected, $script->convertTeamsFilterValue($filter));
    }

    public function providerTestConvertTeamsFilterValue()
    {
        return array(
            array('someTeamId', array('$in' => array('someTeamId'))),
            array(array('$equals' => 'someOtherTeamId'), array('$in' => array('someOtherTeamId'))),
            array(array('$not_equals' => 'anotherTeamId'), array('$not_in' => array('anotherTeamId'))),
            array(array('$custom_operator' => 'yetAnotherTeamId'), null),
        );
    }

    /**
     * @dataProvider providerTestParseFilterComponent
     * @covers ::parseFilterComponent
     */
    public function testParseFilterComponent($filter, $expected, $convertTeamsFilterValue)
    {
        $script = $this->getMockBuilder('SugarUpgradeUpdateTeamsFilters')
            ->disableOriginalConstructor()
            ->setMethods(array('convertTeamsFilterValue'))
            ->getMock();
        $script
            ->method('convertTeamsFilterValue')
            ->willReturn($convertTeamsFilterValue);

        $filterComponent = $script->parseFilterComponent($filter);
        $this->assertEquals($expected, $filterComponent);
    }

    public function providerTestParseFilterComponent()
    {
        return array(
            array(
                array(
                    array('some field name' => 'some value'),
                    array('team_id' => 'someOtherTeamId'),
                    array('another field name' => 'another value'),
                ),
                array(
                    array('some field name' => 'some value'),
                    array('team_id' => array('$in' => array('someOtherTeamId'))),
                    array('another field name' => 'another value'),
                ),
                array('$in' => array('someOtherTeamId')),
            ),
        );
    }

    /**
     * @covers ::convertFilter
     * @expectedException InvalidArgumentException
     */
    public function testConvertFilterInvalidJSON()
    {
        $script = $this->getMockBuilder('SugarUpgradeUpdateTeamsFilters')
            ->disableOriginalConstructor()
            ->setMethods(array('parseFilterComponent'))
            ->getMock();
        $script->convertFilter('Invalid JSON');
    }

    /**
     * @covers ::convertFilter
     */
    public function testConvertFilter()
    {
        $script = $this->getMockBuilder('SugarUpgradeUpdateTeamsFilters')
            ->disableOriginalConstructor()
            ->setMethods(array('parseFilterComponent'))
            ->getMock();

        $jsonFilter = array(
            array('team_id' => array('$in' => array('aTeamId'))),
        );
        $script
            ->method('parseFilterComponent')
            ->willReturn($jsonFilter);

        $originalFilterString = '[{"team_id":{"$equals":"aTeamId"}}]';
        $expectedFilterString = '[{"team_id":{"$in":["aTeamId"]}}]';
        $this->assertEquals($expectedFilterString, $script->convertFilter($originalFilterString));
    }
}
