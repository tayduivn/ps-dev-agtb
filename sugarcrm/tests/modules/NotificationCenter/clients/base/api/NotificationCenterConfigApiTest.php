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

require_once 'modules/NotificationCenter/clients/base/api/NotificationCenterConfigApi.php';
require_once 'modules/Users/User.php';

use NotificationCenterConfigApi;
use Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface;
use SugarTestRestServiceMock;
use SugarApiExceptionMissingParameter;
use Sugarcrm\Sugarcrm\Notification\CarrierRegistry;
use User;
use Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry;
use Sugarcrm\Sugarcrm\Notification\Config\Status as NotificationConfigStatus;
use Sugarcrm\Sugarcrm\Notification\Carrier\ConfigurableInterface;

/**
 * @coversDefaultClass NotificationCenterConfigApi
 *
 * Class NotificationCenterConfigApiTest
 */
class NotificationCenterConfigApiTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var NotificationCenterConfigApi|\PHPUnit_Framework_MockObject_MockObject */
    protected $api = null;

    /** @var SugarTestRestServiceMock */
    protected $service = null;

    /** @var CarrierRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $carrierRegistry = null;

    /** @var string[] of carrier names */
    protected $carriers = array();

    /** @var User|\PHPUnit_Framework_MockObject_MockObject */
    protected $user = null;

    /** @var SubscriptionsRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $subscriptionsRegistry = null;

    /** @var NotificationConfigStatus|\PHPUnit_Framework_MockObject_MockObject */
    protected $notificationConfigStatus = null;

    /** @var array mapping of carriers' names to their objects */
    protected $carriersMap = array();

    /** @var array mapping of carriers' names to their status */
    protected $carrierStatus = array();

    /** @var array mapping of carrier's name to their status for user */
    protected $carrierUserStatus = array();

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
        $this->user = $this->getMock('User');
        $this->user->id = create_guid();
        $this->user->is_admin = true;
        $this->service->user = $this->user;
        $this->api = $this->getMock('NotificationCenterConfigApi', array(
            'updateConfig',
            'getConfig',
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
            /** @var mixed $carrierStub */
            if ($k % 2) {
                $carrierStub = $this->getMock('Sugarcrm\Sugarcrm\Notification\Carrier\ConfigurableInterface');
                $this->carrierStatus[] = array($carrier, true);
                $this->carrierUserStatus[$carrier] = false;
            } else {
                $carrierStub = $this->getMock('Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface');
                $this->carrierStatus[] = array($carrier, false);
                $this->carrierUserStatus[$carrier] = true;
            }
            $this->carriersMap[] = array($carrier, $carrierStub);
            $carrierStub->addressType = $this->getMock('Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\AddressTypeInterface');
            $carrierStub->addressType->options = array(
                'k1' => rand(1000, 1999),
                'k2' => rand(2000, 2999),
            );
            $carrierStub->addressType->method('getOptions')->willReturnCallback(function() use ($carrierStub) {
                return $carrierStub->addressType->options;
            });
            $carrierStub->addressType->selectable = ($k % 2 == 0);
            $carrierStub->addressType->method('isSelectable')->willReturnCallback(function() use ($carrierStub) {
                return $carrierStub->addressType->selectable;
            });
            $carrierStub->method('getAddressType')->willReturn($carrierStub->addressType);
        }

        $this->user
            ->method('getPreference')
            ->with($this->equalTo('carrierStatus'), $this->equalTo('notificationCenter'))
            ->willReturn($this->carrierUserStatus);

        $this->carrierRegistry->method('getCarrier')->willReturnMap($this->carriersMap);
        $this->carrierRegistry->method('getCarriers')->willReturn($this->carriers);
        $this->notificationConfigStatus->method('getCarrierStatus')->willReturnMap($this->carrierStatus);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Throws if personal is missed
     *
     * @covers NotificationCenterConfigApi::updateUserConfig
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: personal
     */
    public function testUpdateUserConfigThrowsIfPersonalIsMissed()
    {
        $args = array();
        $this->api->updateUserConfig($this->service, $args);
    }

    /**
     * Throws if personal['carriers'] are missed
     *
     * @covers NotificationCenterConfigApi::updateUserConfig
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: carriers
     */
    public function testUpdateUserConfigThrowsIfPersonalCarriersAreMissed()
    {
        $args = array(
            'personal' => array(
                'config' => array(),
            ),
        );
        $this->api->updateUserConfig($this->service, $args);
    }

    /**
     * Throws if personal['config'] is missed
     *
     * @covers NotificationCenterConfigApi::updateUserConfig
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: config
     */
    public function testUpdateUserConfigThrowsIfPersonalConfigIsMissed()
    {
        $args = array(
            'personal' => array(
                'carriers' => array(),
            ),
        );
        $this->api->updateUserConfig($this->service, $args);
    }

    /**
     * Data provider for testUpdateUserConfigUpdatesCarrierStatusInUserPreferences
     *
     * @see NotificationCenterConfigApiTest::testUpdateUserConfigUpdatesCarrierStatusInUserPreferences
     * @return array
     */
    public static function updateUserConfigUpdatesCarrierStatusInUserPreferencesProvider()
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
     * setPreference of user is called with correct args
     *
     * @covers NotificationCenterConfigApi::updateUserConfig
     * @dataProvider updateUserConfigUpdatesCarrierStatusInUserPreferencesProvider
     * @param array $carriers
     */
    public function testUpdateUserConfigUpdatesCarrierStatusInUserPreferences($carriers)
    {
        $args = array(
            'personal' => array(
                'carriers' => array(),
                'config' => array(),
            ),
        );

        $expected = array();
        foreach ($this->carriers as $k => $name) {
            $expected[$name] = false;
            if (isset($carriers[$k])) {
                $expected[$name] = $carriers[$k];
                $args['personal']['carriers'][$name]['status'] = $carriers[$k];
            }
        }

        $this->user
            ->expects($this->once())
            ->method('setPreference')
            ->with(
                $this->equalTo('carrierStatus'),
                $this->equalTo($expected),
                $this->equalTo(0),
                $this->equalTo('notificationCenter')
            );

        $this->api->updateUserConfig($this->service, $args);
    }

    /**
     * Data provider for testUpdateUserConfigUpdatesUserConfig
     *
     * @see NotificationCenterConfigApiTest::testUpdateUserConfigUpdatesUserConfig
     * @return array
     */
    public static function updateUserConfigUpdatesUserConfigProvider()
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
     * setUserConfiguration is called with correct args
     *
     * @covers NotificationCenterConfigApi::updateUserConfig
     * @dataProvider updateUserConfigUpdatesUserConfigProvider
     */
    public function testUpdateUserConfigUpdatesUserConfig()
    {
        $args = array(
            'personal' => array(
                'carriers' => array(),
                'config' => array(),
            ),
        );

        $this->subscriptionsRegistry
            ->expects($this->once())
            ->method('setUserConfiguration')
            ->with($this->equalTo($this->user->id), $this->equalTo($args['personal']['config']));

        $this->api->updateUserConfig($this->service, $args);
    }

    /**
     * getUserConfig should return current config
     *
     * @covers NotificationCenterConfigApi::updateUserConfig
     */
    public function testUpdateUserConfigUpdatedConfig()
    {
        $args = array(
            'personal' => array(
                'carriers' => array(),
                'config' => array(),
            ),
        );

        $globalConfiguration = 'GlobalConfiguration ' . rand(1000, 9999);
        $this->subscriptionsRegistry
            ->expects($this->once())
            ->method('getGlobalConfiguration')
            ->willReturn($globalConfiguration);

        $userConfiguration = 'UserConfiguration ' . rand(1000, 9999);
        $this->subscriptionsRegistry
            ->expects($this->once())
            ->method('getUserConfiguration')
            ->with($this->equalTo($this->user->id))
            ->willReturn($userConfiguration);

        $result = $this->api->getUserConfig($this->service, $args);
        foreach ($this->carrierStatus as $data) {
            $this->assertArrayHasKey($data[0], $result['global']['carriers']);
            $this->assertEquals($data[1], $result['global']['carriers'][$data[0]]['status']);
        }
        foreach ($this->carrierUserStatus as $carrier => $status) {
            $this->assertArrayHasKey($carrier, $result['personal']['carriers']);
            $this->assertEquals($status, $result['personal']['carriers'][$carrier]['status']);
        }
        $this->assertEquals($globalConfiguration, $result['global']['config']);
        $this->assertEquals($userConfiguration, $result['personal']['config']);
    }

    /**
     * Returns global configuration of carriers
     *
     * @covers NotificationCenterConfigApi::getUserConfig
     */
    public function testGetUserConfigReturnsGlobalCarriers()
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
        $result = $this->api->getUserConfig($this->service, array());
        foreach ($this->carriers as $k => $carrier) {
            $this->assertArrayHasKey($carrier, $result['global']['carriers']);
            $this->assertEquals($this->carrierStatus[$k][1], $result['global']['carriers'][$carrier]['status']);
            if ($this->carriersMap[$k][1] instanceof ConfigurableInterface) {
                $this->assertTrue($result['global']['carriers'][$carrier]['configurable']);
                $this->assertEquals(get_class($this->carriersMap[$k][1]) . '-config', $result['global']['carriers'][$carrier]['isConfigured']);
                $this->assertEquals(get_class($this->carriersMap[$k][1]) . '-layout', $result['global']['carriers'][$carrier]['configLayout']);
            } else {
                $this->assertFalse($result['global']['carriers'][$carrier]['configurable']);
                $this->assertTrue($result['global']['carriers'][$carrier]['isConfigured']);
            }
        }
    }

    /**
     * Data provider for testGetUserConfigReturnsGlobalConfig
     *
     * @see NotificationCenterConfigApiTest::testGetUserConfigReturnsGlobalConfig
     * @return array
     */
    public static function getUserConfigReturnsGlobalConfigProvider()
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
     * Returns personal configuration of carriers
     *
     * @covers NotificationCenterConfigApi::getUserConfig
     */
    public function testGetUserConfigReturnsPersonalCarriers()
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
        $result = $this->api->getUserConfig($this->service, array());
        /** @var string $carrier */
        foreach ($this->carriers as $k => $carrier) {
            $this->assertArrayHasKey($carrier, $result['personal']['carriers']);
            $this->assertEquals($this->carrierUserStatus[$carrier], $result['personal']['carriers'][$carrier]['status']);
            if ($this->carriersMap[$k][1] instanceof CarrierInterface) {
                $this->assertEquals($this->carriersMap[$k][1]->addressType->selectable, $result['personal']['carriers'][$carrier]['selectable']);
                $this->assertEquals($this->carriersMap[$k][1]->addressType->options, json_decode(json_encode($result['personal']['carriers'][$carrier]['options']), true));
            }
        }
    }

    /**
     * Returns global configuration
     *
     * @covers NotificationCenterConfigApi::getUserConfig
     * @dataProvider getUserConfigReturnsGlobalConfigProvider
     * @param mixed $config
     */
    public function testGetUserConfigReturnsGlobalConfig($config)
    {
        $this->subscriptionsRegistry->method('getGlobalConfiguration')->willReturn($config);
        $result = $this->api->getUserConfig($this->service, array());
        $this->assertEquals($config, $result['global']['config']);
    }

    /**
     * Data provider for testGetUserConfigReturnsPersonalConfig
     *
     * @see NotificationCenterConfigApiTest::testGetUserConfigReturnsPersonalConfig
     * @return array
     */
    public static function getUserConfigReturnsPersonalConfigProvider()
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
     * Returns personal configuration
     *
     * @covers NotificationCenterConfigApi::getUserConfig
     * @dataProvider getUserConfigReturnsPersonalConfigProvider
     * @param mixed $config
     */
    public function testGetUserConfigReturnsPersonalConfig($config)
    {
        $this->subscriptionsRegistry->method('getUserConfiguration')->willReturn($config);
        $result = $this->api->getUserConfig($this->service, array());
        $this->assertEquals($config, $result['personal']['config']);
    }
}
