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

/**
 * Class CalDavCalendarTest
 *
 * @coversDefaultClass \CalDavCalendar
 */
class CalDavCalendarTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function isChangedProvider()
    {
        return array(
            array(
                'beanData' => array(
                    'name' => 'test',
                    'description' => 'test',
                    'timezone' => null,
                    'calendarorder' => 1,
                    'calendarcolor' => null,
                    'transparent' => 0,
                ),
                'changedData' => array(
                    'name' => 'test',
                    'description' => 'test',
                    'timezone' => null,
                    'calendarorder' => 1,
                    'calendarcolor' => null,
                    'transparent' => 1,
                ),
                'result' => true
            ),
            array(
                'beanData' => array(
                    'name' => 'test',
                    'description' => 'test',
                    'timezone' => null,
                    'calendarorder' => 1,
                    'calendarcolor' => null,
                    'transparent' => 0,
                ),
                'changedData' => array(
                    'name' => 'test',
                    'description' => 'test',
                    'timezone' => null,
                    'calendarorder' => 1,
                    'calendarcolor' => "",
                    'transparent' => 0,
                ),
                'result' => false
            )
        );
    }

    public function getTransparentProvider()
    {
        return array(
            array(
                'beanTranparent' => 1,
                'davTranparent' => 'transparent',
            ),
            array(
                'beanTranparent' => 0,
                'davTranparent' => 'opaque',
            ),
        );
    }

    public function getComponentsProvider()
    {
        return array(
            array(
                'beanComponents' => 'VEVENT,VTODO',
                'davComponents' => array('VEVENT','VTODO')
            ),
            array(
                'beanComponents' => "",
                'davComponents' => null
            ),
        );
    }

    /**
     * @param array $beanData
     * @param array $changedData
     * @param bool $expectedResult
     *
     * @covers       \CalDavCalendar::isChanged
     *
     * @dataProvider isChangedProvider
     */
    public function testIsChanged($beanData, $changedData, $expectedResult)
    {
        $calendarMock = $this->getMockBuilder('CalDavCalendar')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

        foreach ($beanData as $key => $value) {
            $calendarMock->$key = $value;
            $calendarMock->fetched_row[$key] = $value;
        }

        foreach ($changedData as $key => $value) {
            $calendarMock->$key = $value;
        }

        $result = $calendarMock->isChanged();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param int $beanTransparent
     * @param string $davTransparent
     *
     * @covers \CalDavCalendar::getTransparent
     *
     * @dataProvider getTransparentProvider
     */
    public function testGetTransparent($beanTransparent, $davTransparent)
    {
        $calendarMock = $this->getMockBuilder('CalDavCalendar')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

        $calendarMock->transparent = $beanTransparent;

        $result = $calendarMock->getTransparent();

        $this->assertEquals($davTransparent, $result);
    }

    /**
     * @param string $beanComponents
     * @param array|null $davComponents
     *
     * @covers \CalDavCalendar::getComponents
     *
     * @dataProvider getComponentsProvider
     */
    public function testGetComponents($beanComponents, $davComponents)
    {
        $calendarMock = $this->getMockBuilder('CalDavCalendar')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

        $calendarMock->components = $beanComponents;

        $result = $calendarMock->getComponents();

        $this->assertEquals($davComponents, $result);
    }
}
