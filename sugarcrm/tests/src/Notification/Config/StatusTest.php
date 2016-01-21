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

namespace Sugarcrm\SugarcrmTests\Notification\Config;

use Sugarcrm\Sugarcrm\Notification\Config\Status as ConfigStatus;
use Sugarcrm\Sugarcrm\Notification\CarrierRegistry;

/**
 * Testing functionality Config/Status
 *
 * Class StatusTest
 * @package Notification
 */
class StatusTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var ConfigStatus */
    protected $configStatus = null;

    /** @var CarrierRegistry */
    protected $carrierRegistry = null;

    /** @var array list of carriers */
    protected $carriers = array();

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->carriers = array(
            'Carrier' . rand(1000, 1999),
            'Carrier' . rand(2000, 2999),
            'Carrier' . rand(3000, 3999),
            'Carrier' . rand(4000, 4999),
        );
        \BeanFactory::setBeanClass('Administration', 'Sugarcrm\SugarcrmTests\Notification\Config\AdministrationCRYS1275');
        $this->carrierRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\CarrierRegistry');
        $this->carrierRegistry->method('getCarriers')->willReturn($this->carriers);
        $this->configStatus = $this->getMock('Sugarcrm\Sugarcrm\Notification\Config\Status', array(
            'getCarrierRegistry',
        ));
        $this->configStatus->method('getCarrierRegistry')->willReturn($this->carrierRegistry);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        AdministrationCRYS1275::$getSettingsArgs = array();
        AdministrationCRYS1275::$getSettingsReturn = null;
        AdministrationCRYS1275::$saveSettingArgs = array();
        AdministrationCRYS1275::$saveSettingReturn = null;
        \BeanFactory::setBeanClass('Administration');
        parent::tearDown();
    }

    /**
     * getInstance should return object of own class
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Config\Status::getInstance
     */
    public function testGetInstanceReturnsOwnObject()
    {
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Notification\Config\Status', $this->configStatus->getInstance());
    }

    /**
     * Data provider for testGetCarrierStatusThrowsIfModuleIsNotCarrier
     *
     * @see StatusTest::testGetCarrierStatusThrowsIfModuleIsNotCarrier
     * @return array
     */
    public static function getCarrierStatusThrowsIfModuleIsNotCarrierProvider()
    {
        return array(
            'emptyName' => array(
                'carrierName' => '',
            ),
            'invalidCarrier' => array(
                'carrierName' => 'Carrier',
            ),
        );
    }

    /**
     * getCarrierStatus throws on invalid carrier name
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Config\Status::getCarrierStatus
     * @dataProvider getCarrierStatusThrowsIfModuleIsNotCarrierProvider
     * @expectedException \LogicException
     * @param string $carrierName
     */
    public function testGetCarrierStatusThrowsIfModuleIsNotCarrier($carrierName)
    {
        $this->configStatus->getCarrierStatus($carrierName);
    }

    /**
     * getCarrierStatus returns correct result
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Config\Status::getCarrierStatus
     */
    public function testGetCarrierStatusReturnsCorrectResult()
    {
        $rand = rand(0, 1); // random number for expected value
        foreach ($this->carriers as $k => $carrierName) {
            $expected = ($k % 2 == $rand);
            AdministrationCRYS1275::$getSettingsReturn = new \stdClass();
            AdministrationCRYS1275::$getSettingsReturn->settings = array(
                ConfigStatus::CONFIG_CATEGORY . '_' . $carrierName => $expected,
            );

            $result = $this->configStatus->getCarrierStatus($carrierName);
            $this->assertEquals($expected, $result);
            $this->assertEquals(array(ConfigStatus::CONFIG_CATEGORY), AdministrationCRYS1275::$getSettingsArgs);
        }
    }

    /**
     * Data provider for testSetCarrierStatusThrowsIfModuleIsNotCarrier
     *
     * @see StatusTest::testSetCarrierStatusThrowsIfModuleIsNotCarrier
     * @return array
     */
    public static function setCarrierStatusThrowsIfModuleIsNotCarrierProvider()
    {
        return array(
            'emptyName' => array(
                'carrierName' => '',
            ),
            'invalidCarrier' => array(
                'carrierName' => 'Carrier',
            ),
        );
    }

    /**
     * setCarrierStatus throws on invalid carrier name
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Config\Status::setCarrierStatus
     * @dataProvider setCarrierStatusThrowsIfModuleIsNotCarrierProvider
     * @expectedException \LogicException
     * @param string $carrierName
     */
    public function testSetCarrierStatusThrowsIfModuleIsNotCarrier($carrierName)
    {
        $this->configStatus->setCarrierStatus($carrierName, true);
    }

    /**
     * setCarrierStatus updates and returns correct result
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Config\Status::setCarrierStatus
     */
    public function testSetCarrierStatusUpdatesStatusCorrectly()
    {
        $rand = rand(0, 1); // random number for expected value
        foreach ($this->carriers as $k => $carrierName) {
            $expected = ($k % 2 == $rand);
            $result = $this->configStatus->setCarrierStatus($carrierName, $expected);
            $this->assertEquals($expected, $result);
            $this->assertEquals(array(ConfigStatus::CONFIG_CATEGORY, $carrierName, $expected), AdministrationCRYS1275::$saveSettingArgs);
        }
    }
}

/**
 * Stub class for Administration bean
 *
 * Class AdministrationCRYS1275
 * @package Sugarcrm\SugarcrmTests\Notification\Config
 */
class AdministrationCRYS1275 extends \Administration
{
    /** @var array collects args of getSettings method */
    public static $getSettingsArgs = array();

    /** @var mixed return value of getSettings method */
    public static $getSettingsReturn = null;

    /** @var array collects args of saveSetting method */
    public static $saveSettingArgs = array();

    /** @var mixed return value of saveSetting method */
    public static $saveSettingReturn = null;

    /**
     * @inheritDoc
     */
    public static function getSettings($category = false, $clean = false)
    {
        static::$getSettingsArgs = func_get_args();
        return static::$getSettingsReturn;
    }

    /**
     * @inheritDoc
     */
    public function saveSetting($category, $key, $value, $platform = '')
    {
        static::$saveSettingArgs = func_get_args();
        return static::$saveSettingReturn;
    }
}
