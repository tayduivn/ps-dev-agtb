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

namespace Sugarcrm\SugarcrmTests\modules\NotificationCenter\clients\base\api;

require_once 'modules/NotificationCenter/clients/base/api/GlobalConfigApi.php';

use GlobalConfigApi;
use Sugarcrm\Sugarcrm\Notification\Carrier\ConfigurableInterface;
use SugarTestRestServiceMock;
use SugarApiExceptionNotAuthorized;
use SugarApiExceptionMissingParameter;
use Sugarcrm\Sugarcrm\Notification\Config\Status as NotificationConfigStatus;
use Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry;
use Sugarcrm\Sugarcrm\Notification\CarrierRegistry;

class GlobalConfigApiTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var GlobalConfigApi|\PHPUnit_Framework_MockObject_MockObject */
    protected $api = null;

    /** @var SugarTestRestServiceMock */
    protected $service = null;

    /** @var NotificationConfigStatus|\PHPUnit_Framework_MockObject_MockObject */
    protected $notificationConfigStatus = null;

    /** @var SubscriptionsRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $subscriptionsRegistry = null;

    /** @var CarrierRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $carrierRegistry = null;

    /** @var array of carrier names */
    protected $carriers = array();

    /** @var array mapping of carriers' names to their objects */
    protected $carriersMap = array();

    /** @var array mapping of carriers' names to their status */
    protected $carrierStatus = array();

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->notificationConfigStatus = $this->getMock('Sugarcrm\Sugarcrm\Notification\Config\Status');
        $this->subscriptionsRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry');
        $this->carrierRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\CarrierRegistry');
        $this->service = new SugarTestRestServiceMock();
        $this->service->user = new \User();
        $this->service->user->id = create_guid();
        $this->service->user->is_admin = true;
        $this->api = $this->getMock('GlobalConfigApi', array(
            'getStatus',
            'getSubscriptionsRegistry',
            'getCarrierRegistry',
        ));
        $this->api->method('getStatus')->willReturn($this->notificationConfigStatus);
        $this->api->method('getSubscriptionsRegistry')->willReturn($this->subscriptionsRegistry);
        $this->api->method('getCarrierRegistry')->willReturn($this->carrierRegistry);

        $this->carriers = array(
            'Carrier' . rand(1000, 1999),
            'Carrier' . rand(2000, 2999),
            'Carrier' . rand(3000, 3999),
            'Carrier' . rand(4000, 4999),
        );
        foreach ($this->carriers as $k => $carrier) {
            if ($k % 2) {
                $this->carriersMap[] = array($carrier, $this->getMock('Sugarcrm\Sugarcrm\Notification\Carrier\ConfigurableInterface'));
                $this->carrierStatus[] = array($carrier, true);
            } else {
                $this->carriersMap[] = array($carrier, $this->getMock('Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface'));
                $this->carrierStatus[] = array($carrier, false);
            }
        }
        $this->carrierRegistry->method('getCarrier')->willReturnMap($this->carriersMap);
        $this->carrierRegistry->method('getCarriers')->willReturn($this->carriers);
        $this->notificationConfigStatus->method('getCarrierStatus')->willReturnMap($this->carrierStatus);
    }

    /**
     * Throws if user is not admin
     *
     * @covers GlobalConfigApi::updateConfig
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testUpdateConfigThrowsIfCurrentApiUserIsNotAdmin()
    {
        $this->service->user->is_admin = false;
        $this->api->updateConfig($this->service, array());
    }

    /**
     * Throws if carriers are missed
     *
     * @covers GlobalConfigApi::updateConfig
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: carriers
     */
    public function testUpdateConfigThrowsIfCarriersAreMissed()
    {
        $args = array(
            'config' => array(),
        );
        $this->api->updateConfig($this->service, $args);
    }

    /**
     * Throws if config is missed
     *
     * @covers GlobalConfigApi::updateConfig
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: config
     */
    public function testUpdateConfigThrowsIfConfigAreMissed()
    {
        $args = array(
            'carriers' => array(),
        );
        $this->api->updateConfig($this->service, $args);
    }

    /**
     * Data provider for testUpdateConfigUpdatesCarriers
     *
     * @see GlobalConfigApiTest::testUpdateConfigUpdatesCarriers
     * @return array
     */
    public static function updateConfigUpdatesCarriersProvider()
    {
        return array(
            'doesNotSaveAnything' => array(
                array(),
            ),
            'savesAllCarriersCorrectly' => array(
                array(
                    0 => true,
                    1 => true,
                    2 => true,
                    3 => true,
                ),
            ),
            'savesSomeCarriersCorrectly' => array(
                array(
                    1 => true,
                    3 => false,
                ),
            ),
            'savesAnotherCarriersCorrectly' => array(
                array(
                    0 => false,
                    1 => false,
                    3 => false,
                ),
            ),
        );
    }

    /**
     * setCarrierStatus is called with correct args
     *
     * @covers GlobalConfigApi::updateConfig
     * @dataProvider updateConfigUpdatesCarriersProvider
     * @param array $carriers
     */
    public function testUpdateConfigUpdatesCarriers($carriers)
    {
        $args = array(
            'carriers' => array(),
            'config' => array(),
        );

        if (!$carriers) {
            $this->notificationConfigStatus->expects($this->never())->method('setCarrierStatus');
        } else {
            $index = 0;
            foreach ($this->carriers as $k => $v) {
                if (!isset($carriers[$k])) {
                    continue;
                }
                $this->notificationConfigStatus
                    ->expects($this->at($index ++))
                    ->method('setCarrierStatus')
                    ->with($this->equalTo($v), $this->equalTo(!empty($carriers[$k])));
                $args['carriers'][$v]['status'] = $carriers[$k];
            }
        }

        $this->api->updateConfig($this->service, $args);
    }

    /**
     * Data provider for testUpdateConfigUpdatesConfig
     *
     * @see GlobalConfigApiTest::testUpdateConfigUpdatesConfig
     * @return array
     */
    public static function updateConfigUpdatesConfigProvider()
    {
        return array(
            'savingEmptyConfig' => array(
                array(),
            ),
            'savingSomeConfig' => array(
                array(
                    'someKey' => 'someValue' . rand(1000, 9999),
                    'anotherKey' => 'anotherValue' . rand(1000, 9999),
                ),
            ),
        );
    }

    /**
     * setGlobalConfiguration is called with correct args
     *
     * @covers GlobalConfigApi::updateConfig
     * @dataProvider updateConfigUpdatesConfigProvider
     * @param array $config
     */
    public function testUpdateConfigUpdatesConfig($config)
    {
        $args = array(
            'carriers' => array(),
            'config' => $config,
        );
        $this->subscriptionsRegistry
            ->expects($this->once())
            ->method('setGlobalConfiguration')
            ->with($this->equalTo($config));

        $this->api->updateConfig($this->service, $args);
    }

    /**
     * updateConfig should return current config
     *
     * @covers GlobalConfigApi::updateConfig
     */
    public function testUpdateConfigReturnsUpdatedConfig()
    {
        $args = array(
            'carriers' => array(),
            'config' => array(),
        );

        $globalConfiguration = 'GlobalConfiguration ' . rand(1000, 9999);
        $this->subscriptionsRegistry->expects($this->once())->method('getGlobalConfiguration')->willReturn($globalConfiguration);

        $result = $this->api->updateConfig($this->service, $args);
        foreach ($this->carrierStatus as $data) {
            $this->assertArrayHasKey($data[0], $result['carriers']);
            $this->assertEquals($data[1], $result['carriers'][$data[0]]['status']);
        }
        $this->assertEquals($globalConfiguration, $result['config']);
    }

    /**
     * Throws if user is not admin
     *
     * @covers GlobalConfigApi::getConfig
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetConfigThrowsIfCurrentApiUserIsNotAdmin()
    {
        $this->service->user->is_admin = false;
        $this->api->getConfig($this->service, array());
    }

    /**
     * Returns configuration of carriers
     *
     * @covers GlobalConfigApi::getConfig
     */
    public function testGetConfigReturnsCarriers()
    {
        foreach ($this->carriersMap as $data) {
            /** @var \PHPUnit_Framework_MockObject_MockObject $carrier */
            $carrier = $data[1];
            $isConfigurable = ($carrier instanceof ConfigurableInterface);
            if ($isConfigurable) {
                $carrier->method('isConfigured')->willReturn(get_class($data[1]) . '-config');
                $carrier->method('getConfigLayout')->willReturn(get_class($data[1]) . '-layout');
            }
        }
        $result = $this->api->getConfig($this->service, array());
        foreach ($this->carriers as $k => $carrier) {
            $this->assertArrayHasKey($carrier, $result['carriers']);
            $this->assertEquals($this->carrierStatus[$k][1], $result['carriers'][$carrier]['status']);
            if ($this->carriersMap[$k][1] instanceof ConfigurableInterface) {
                $this->assertTrue($result['carriers'][$carrier]['configurable']);
                $this->assertEquals(get_class($this->carriersMap[$k][1]) . '-config', $result['carriers'][$carrier]['isConfigured']);
                $this->assertEquals(get_class($this->carriersMap[$k][1]) . '-layout', $result['carriers'][$carrier]['configLayout']);
            } else {
                $this->assertFalse($result['carriers'][$carrier]['configurable']);
                $this->assertTrue($result['carriers'][$carrier]['isConfigured']);
            }
        }
    }

    /**
     * Data provider for testGetConfigReturnsConfig
     *
     * @see GlobalConfigApiTest::testGetConfigReturnsConfig
     * @return array
     */
    public static function getConfigReturnsConfigProvider()
    {
        return array(
            'nullConfig' => array(
                null,
            ),
            'arrayConfig' => array(
                array(
                    rand(1000, 999),
                ),
            ),
        );
    }

    /**
     * Returns config
     *
     * @dataProvider getConfigReturnsConfigProvider
     * @covers GlobalConfigApi::getConfig
     * @param mixed $config
     */
    public function testGetConfigReturnsConfig($config)
    {
        $this->subscriptionsRegistry->method('getGlobalConfiguration')->willReturn($config);
        $result = $this->api->getConfig($this->service, array());
        $this->assertEquals($config, $result['config']);
    }
}
