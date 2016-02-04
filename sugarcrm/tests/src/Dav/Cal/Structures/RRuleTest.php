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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Structures;

/**
 * Class RRuleTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Structures
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule
 */
class RRuleTest extends \PHPUnit_Framework_TestCase
{
    public function setFrequencyProvider()
    {
        return array(
            array('value' => 'DAILY', 'expected' => 'DAILY', 'exception' => null),
            array('value' => 'WEEKLY', 'expected' => 'WEEKLY', 'exception' => null),
            array('value' => 'test', 'expected' => null, 'exception' => '\InvalidArgumentException'),
        );
    }

    public function setByMonthDayProvider()
    {
        return array(
            array(
                'value' => array(0, 2, 5),
                'freqValue' => 'MONTHLY',
                'expected' => null,
                'exception' => '\InvalidArgumentException',
            ),
            array(
                'value' => array(23),
                'freqValue' => 'DAILY',
                'expected' => array(23),
                'exception' => null
            ),
            array(
                'value' => array(- 1),
                'freqValue' => 'DAILY',
                'expected' => array(- 1),
                'exception' => null,
            ),
            array(
                'value' => array(20, 60),
                'freqValue' => 'DAILY',
                'expected' => null,
                'exception' => '\InvalidArgumentException',
            ),
            array(
                'value' => array(61),
                'freqValue' => null,
                'expected' => null,
                'exception' => '\LogicException'
            ),
            array(
                'value' => array(20),
                'freqValue' => 'WEEKLY',
                'expected' => null,
                'exception' => '\LogicException'
            ),
            array(
                'value' => null,
                'freqValue' => 'DAILY',
                'expected' => null,
                'exception' => null
            ),
        );
    }

    public function setByYearDayProvider()
    {
        return array(
            array(
                'value' => array(0),
                'freqValue' => 'MONTHLY',
                'expected' => null,
                'exception' => '\LogicException',
            ),
            array(
                'value' => array(0),
                'freqValue' => 'YEARLY',
                'expected' => null,
                'exception' => '\InvalidArgumentException',
            ),
            array(
                'value' => array(23),
                'freqValue' => 'YEARLY',
                'expected' => array(23),
                'exception' => null
            ),
            array(
                'value' => array(- 366),
                'freqValue' => 'YEARLY',
                'expected' => array(- 366),
                'exception' => null,
            ),
            array(
                'value' => array(- 77, 60),
                'freqValue' => 'YEARLY',
                'expected' => array(- 77, 60),
                'exception' => null,
            ),
            array(
                'value' => array(370),
                'freqValue' => null,
                'expected' => null,
                'exception' => '\LogicException'
            ),
            array(
                'value' => array(370),
                'freqValue' => 'YEARLY',
                'expected' => null,
                'exception' => '\InvalidArgumentException'
            ),
            array(
                'value' => null,
                'freqValue' => 'DAILY',
                'expected' => null,
                'exception' => null
            ),
        );
    }

    public function setByWeekNoProvider()
    {
        return array(
            array(
                'value' => array(0),
                'freqValue' => 'YEARLY',
                'expected' => null,
                'exception' => '\InvalidArgumentException',
            ),
            array(
                'value' => array(- 54),
                'freqValue' => 'YEARLY',
                'expected' => null,
                'exception' => '\InvalidArgumentException',
            ),
            array(
                'value' => array(23),
                'freqValue' => 'YEARLY',
                'expected' => array(23),
                'exception' => null
            ),
            array(
                'value' => array(1),
                'freqValue' => 'WEEKLY',
                'expected' => null,
                'exception' => '\LogicException',
            ),
            array(
                'value' => array(- 1, 1),
                'freqValue' => 'YEARLY',
                'expected' => array(- 1, 1),
                'exception' => null,
            ),
            array(
                'value' => array(1),
                'freqValue' => null,
                'expected' => null,
                'exception' => '\LogicException'
            ),
            array(
                'value' => null,
                'freqValue' => 'DAILY',
                'expected' => null,
                'exception' => null
            ),
        );
    }

    public function setByMonthProvider()
    {
        return array(
            array(
                'value' => array(1, 2),
                'freqValue' => 'YEARLY',
                'expected' => array(1, 2),
                'exception' => null
            ),
            array(
                'value' => array(1, 2),
                'freqValue' => 'YEARLY',
                'expected' => array(1, 2),
                'exception' => null
            ),
            array(
                'value' => array(0),
                'freqValue' => 'DAILY',
                'expected' => null,
                'exception' => '\InvalidArgumentException',
            ),
            array(
                'value' => array(14),
                'freqValue' => 'DAILY',
                'expected' => null,
                'exception' => '\InvalidArgumentException',
            ),
            array(
                'value' => array(5),
                'freqValue' => null,
                'expected' => null,
                'exception' => '\LogicException'
            ),
            array(
                'value' => null,
                'freqValue' => 'DAILY',
                'expected' => null,
                'exception' => null
            ),
        );
    }

    public function setBySetPosProvider()
    {
        return array(
            array(
                'value' => array(0),
                'freqValue' => 'YEARLY',
                'expected' => null,
                'exception' => '\InvalidArgumentException',
            ),
            array(
                'value' => array(- 54),
                'freqValue' => 'YEARLY',
                'expected' => array(- 54),
                'exception' => null,
            ),
            array(
                'value' => array(23),
                'freqValue' => 'YEARLY',
                'expected' => array(23),
                'exception' => null
            ),
            array(
                'value' => array(1),
                'freqValue' => 'WEEKLY',
                'expected' => array(1),
                'exception' => null,
            ),
            array(
                'value' => array(1),
                'freqValue' => null,
                'expected' => null,
                'exception' => '\LogicException'
            ),
            array(
                'value' => null,
                'freqValue' => 'DAILY',
                'expected' => null,
                'exception' => null
            ),
        );
    }

    public function setIntervalProvider()
    {
        return array(
            array(
                'value' => 1,
                'freqValue' => 'YEARLY',
                'expected' => 1,
                'exception' => null
            ),
            array(
                'value' => 0,
                'freqValue' => 'DAILY',
                'expected' => null,
                'exception' => '\InvalidArgumentException',
            ),
            array(
                'value' => 5,
                'freqValue' => null,
                'expected' => null,
                'exception' => '\LogicException'
            ),
            array(
                'value' => null,
                'freqValue' => 'DAILY',
                'expected' => 1,
                'exception' => null
            ),
        );
    }

    /**
     * @param string $value
     * @param string $expected
     * @param string $exception
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::setFrequency
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::getFrequency
     * @dataProvider setFrequencyProvider
     */
    public function testSetFrequency($value, $expected, $exception)
    {
        $rRuleMock = $this->getRRuleMock();
        $this->setExpectedException($exception);
        $rRuleMock->setFrequency($value);
        $this->assertEquals($expected, $rRuleMock->getFrequency());
    }

    /**
     * @param array $value
     * @param string $frequency
     * @param string $expected
     * @param string $exception
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::setByMonthDay
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::getByMonthDay
     * @dataProvider setByMonthDayProvider
     */
    public function testSetByMonthDay($value, $frequency, $expected, $exception)
    {
        $rRuleMock = $this->getRRuleMock();
        if ($frequency) {
            $rRuleMock->setFrequency($frequency);
        }
        $this->setExpectedException($exception);
        $rRuleMock->setByMonthDay($value);
        $this->assertEquals($expected, $rRuleMock->getByMonthDay());
    }

    /**
     * @param array $value
     * @param string $frequency
     * @param string $expected
     * @param string $exception
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::setByYearDay
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::getByYearDay
     * @dataProvider setByYearDayProvider
     */
    public function testSetByYearDay($value, $frequency, $expected, $exception)
    {
        $rRuleMock = $this->getRRuleMock();
        if ($frequency) {
            $rRuleMock->setFrequency($frequency);
        }
        $this->setExpectedException($exception);
        $rRuleMock->setByYearDay($value);
        $this->assertEquals($expected, $rRuleMock->getByYearDay());
    }

    /**
     * @param array $value
     * @param string $frequency
     * @param string $expected
     * @param string $exception
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::setByWeekNo
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::getByWeekNo
     * @dataProvider setByWeekNoProvider
     */
    public function testSetByWeekNo($value, $frequency, $expected, $exception)
    {
        $rRuleMock = $this->getRRuleMock();
        if ($frequency) {
            $rRuleMock->setFrequency($frequency);
        }
        $this->setExpectedException($exception);
        $rRuleMock->setByWeekNo($value);
        $this->assertEquals($expected, $rRuleMock->getByWeekNo());
    }

    /**
     * @param array $value
     * @param string $frequency
     * @param string $expected
     * @param string $exception
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::setByMonth
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::getByMonth
     * @dataProvider setByMonthProvider
     */
    public function testSetMonth($value, $frequency, $expected, $exception)
    {
        $rRuleMock = $this->getRRuleMock();
        if ($frequency) {
            $rRuleMock->setFrequency($frequency);
        }
        $this->setExpectedException($exception);
        $rRuleMock->setByMonth($value);
        $this->assertEquals($expected, $rRuleMock->getByMonth());
    }

    /**
     * @param array $value
     * @param string $frequency
     * @param string $expected
     * @param string $exception
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::setInterval
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::getInterval
     * @dataProvider setIntervalProvider
     */
    public function testSetInterval($value, $frequency, $expected, $exception)
    {
        $rRuleMock = $this->getRRuleMock();
        if ($frequency) {
            $rRuleMock->setFrequency($frequency);
        }
        $this->setExpectedException($exception);
        $rRuleMock->setInterval($value);
        $this->assertEquals($expected, $rRuleMock->getInterval());
    }

    /**
     * @param array $value
     * @param string $frequency
     * @param string $expected
     * @param string $exception
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::setBySetPos
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule::getBySetPos
     * @dataProvider setBySetPosProvider
     */
    public function testSetByPos($value, $frequency, $expected, $exception)
    {
        $rRuleMock = $this->getRRuleMock();
        if ($frequency) {
            $rRuleMock->setFrequency($frequency);
        }
        $this->setExpectedException($exception);
        $rRuleMock->setBySetPos($value);
        $this->assertEquals($expected, $rRuleMock->getBySetPos());
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule
     */
    public function getRRuleMock()
    {
        $rRule = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule')
                      ->setConstructorArgs(array(null))
                      ->setMethods(null)
                      ->getMock();

        return $rRule;
    }
}
