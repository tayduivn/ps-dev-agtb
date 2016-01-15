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

require_once('modules/Forecasts/clients/base/api/ForecastsChartApi.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 * @group forecastapi
 * @group forecasts
 * @coversDefaultClass ::ForecastChartApi
 */
class ForecastsChartApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @param string $_file
     * @param string $_klass
     * @covers ::getClass
     * @dataProvider dataProviderGetClass
     */
    public function testGetClass($_file, $_klass)
    {
        $api = new ForecastsChartApi();
        $klass = SugarTestReflection::callProtectedMethod($api, 'getClass', array($_file, $_klass, array()));
        $this->assertInstanceOf($_klass, $klass);
    }
    /**
     * @param string $_file
     * @param string $_klass
     * @covers ::getClass
     * @dataProvider dataProviderGetClass
     */
    public function testGetClassReturnsCustomClass($_file, $_klass)
    {
        SugarAutoLoader::load($_file);
        $file = <<<FILE
<?php
class Custom$_klass {}
FILE;
        sugar_file_put_contents('custom/' . $_file, $file);
        $api = new ForecastsChartApi();
        $klass = SugarTestReflection::callProtectedMethod($api, 'getClass', array($_file, $_klass, array()));
        $this->assertInstanceOf('Custom' . $_klass, $klass);
        SugarAutoLoader::unlink('custom/' . $_file);
    }

    /**
     * @param string $_file
     * @param string $_klass
     * @param int $display_manager
     * @param int $manager_total
     * @covers ::chart
     * @dataProvider dataProviderChart
     */
    public function testChart($_file, $_klass, $display_manager, $manager_total)
    {
        $api = $this->getMockBuilder('ForecastsChartApi')
            ->setMethods(array('getClass'))
            ->getMock(0);

        $user = $this->getMockBuilder('User')
            ->setMethods(array('save'))
            ->disableOriginalConstructor()
            ->getMock();

        $user->id = 'test-id';

        $dbMock = SugarTestHelper::setup('mock_db');
        $dbMock->addQuerySpy(
            'auditQuery',
            '/WHERE reports_to_id =/',
            array(
                array(
                    'total' => $manager_total
                ),
            )
        );

        $args = array(
            'timeperiod_id' => 'test-timeperiod-id',
            'user_id' => 'test-user-id',
            'display_manager' => $display_manager
        );

        SugarAutoLoader::load($_file);
        $klass = $this->getMockBuilder($_klass)
            ->setMethods(array('process'))
            ->disableOriginalConstructor()
            ->getMock();

        $klass->expects($this->once())
            ->method('process')
            ->willReturn(true);

        $api->expects($this->once())
            ->method('getClass')
            ->with($_file, $_klass)
            ->willReturn($klass);


        $this->assertTrue($api->chart(SugarTestRestUtilities::getRestServiceMock($user), $args));
    }

    public static function dataProviderChart()
    {
        return array(
            array(
                'include/SugarForecasting/Chart/Individual.php',
                'SugarForecasting_Chart_Individual',
                0,
                0
            ),
            array(
                'include/SugarForecasting/Chart/Manager.php',
                'SugarForecasting_Chart_Manager',
                1,
                10
            ),
        );
    }

    public static function dataProviderGetClass()
    {
        return array(
            array(
                'include/SugarForecasting/Chart/Individual.php',
                'SugarForecasting_Chart_Individual'
            ),
            array(
                'include/SugarForecasting/Chart/Manager.php',
                'SugarForecasting_Chart_Manager'
            ),
        );
    }

}
