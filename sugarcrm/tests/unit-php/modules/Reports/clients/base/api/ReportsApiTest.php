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
namespace Sugarcrm\SugarcrmTestsUnit\modules\Reports\clients\base\api;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \ReportsApi
 */
class ReportsApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::needOptions
     * @dataProvider providerTestNeedOptions
     */
    public function testNeedOptions($type, $expectedVal)
    {
        $sut = $this->getReportsApiMock();
        $op = TestReflection::callProtectedMethod($sut, 'needOptions', array($type));
        $this->assertSame($expectedVal, $op);
    }

    public function providerTestNeedOptions()
    {
        return array(
            array('name', false),
            array('enum', true),
            array('url', false),
            array('phone', false),
            array('varchar', false),
            array('bool', false),
            array('int', false),
            array('radioenum', true),
        );
    }

    /**
     * @covers ::massageFilterValue
     * @dataProvider providerTestMassageFilterValue
     */
    public function testMassageFilterValue($type, $value, $fieldDef, $mockedData, $expectedVal)
    {
        $sut = $this->getReportsApiMock(array('getOptionKeyFromValue', 'getAppListStrings', 'getAppStrings'));
        $sut->method('getAppStrings')->willReturn(array('LBL_CHART_UNDEFINED' => 'Undefined'));
        $sut->method('getAppListStrings')->willReturn($mockedData);
        $sut->method('getOptionKeyFromValue')->willReturn($mockedData);
        $op = TestReflection::callProtectedMethod($sut, 'massageFilterValue', array($type, $value, $fieldDef));
        $this->assertSame(array($expectedVal), $op);
    }

    public function providerTestMassageFilterValue()
    {
        return array(
            array('radioenum', array('Quote'), array('options'=>'quote_type_dom'), 'Quotes', 'Quotes'),
            array('enum', array('Analyst'), array('options'=>'account_type_dom'), 'Analyst', 'Analyst'),
            array('bool', array('Yes'), array(), array('dom_switch_bool'=>array('on' => 'Yes', 'off' => 'No',)), '1'),
            array('bool', array('No'), array(), array('dom_switch_bool'=>array('on' => 'Yes', 'off' => 'No',)), '0'),
            array('bool', array('1'), array(), array('dom_switch_bool'=>array('on' => 'Yes', 'off' => 'No',)), '1'),
            array('bool', array('0'), array(), array('dom_switch_bool'=>array('on' => 'Yes', 'off' => 'No',)), '0'),
            array('name', array('abc'), array(), array(), 'abc'),
            array('name', array(''), array(), array(), ''),
            array('name', array('Undefined'), array(), array(), ''),
        );
    }

    /**
     * @covers ::getOptionKeyFromValue
     * @dataProvider providerTestGetOptionKeyFromValue
     */
    public function testGetOptionKeyFromValue($value, $fieldDef, $expectedVal)
    {
        $sut = $this->getReportsApiMock();
        $op = TestReflection::callProtectedMethod($sut, 'getOptionKeyFromValue', array($value, $fieldDef));
        $this->assertSame($expectedVal, $op);
    }

    public function providerTestGetOptionKeyFromValue()
    {
        return array(
            array('Quote',
                array('options_array' => array('Quotes' => 'Quote', 'Orders' => 'Order',)),
                'Quotes'),
            array('Order',
                array('options_array' => array('Quotes' => 'Quote', 'Orders' => 'Order',)),
                'Orders'),
            array('Analyst',
                array('options_array' => array('Analyst' => 'Analyst',)),
                'Analyst'),
            array('',
                array('options_array' => array('Analyst' => '',)),
                ''),
            array('Analyst',
                array('options_array' => array('' => 'Analyst',)),
                ''),
        );
    }

    /**
     * @param null|array $methods
     * @return \ReportsApi
     */
    protected function getReportsApiMock($methods = null)
    {
        return $this->getMockBuilder('ReportsApi')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
