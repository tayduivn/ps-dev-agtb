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

require_once 'modules/NotificationCenter/clients/base/api/GlobalConfigApi.php';

class GlobalConfigApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    const NS_REGISTRY = 'Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry';
    const NS_CARRIER_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\CarrierRegistry';
    const NS_STATUS = 'Sugarcrm\\Sugarcrm\\Notification\\Config\\Status';
    const NS_CARRIER_CONFIGURABLE = 'Sugarcrm\\Sugarcrm\\Notification\\Carrier\\ConfigurableInterface';
    const NS_CARRIER_BASE = 'Sugarcrm\\Sugarcrm\\Notification\\Carrier\\CarrierInterface';
    private $api;

    public function testGetConfig()
    {
        $configDelivery = array('some', 'Global', 'Delivery', 'ConfigApiTest');
        $configCarriers = array('some', 'Global', 'Carriers', 'ConfigApiTest');

        $registry = $this->getMock(self::NS_REGISTRY, array('getGlobalConfiguration'));
        $registry->expects($this->once())->method('getGlobalConfiguration')->willReturn($configDelivery);

        $api = $this->getMock(
            'GlobalConfigApi',
            array('getSubscriptionsRegistry', 'checkIsAdmin', 'getCarriersConfig')
        );
        $api->expects($this->once())->method('getSubscriptionsRegistry')->willReturn($registry);
        $api->expects($this->once())->method('checkIsAdmin')->with($this->equalTo($this->api));
        $api->expects($this->once())->method('getCarriersConfig')->willReturn($configCarriers);

        $res = $api->getConfig($this->api, array());
        $this->assertEquals(array('carriers' => $configCarriers, 'config' => $configDelivery), $res);
    }

    public function testUpdateConfig()
    {
        $configRes = array('some', 'Global', 'ConfigApiTest');
        $configDelivery = array('some', 'Global', 'Delivery', 'ConfigApiTest');
        $configCarriers = array('some', 'Global', 'Carriers', 'ConfigApiTest');
        $args = array('carriers' => $configCarriers, 'config' => $configDelivery);

        $api = $this->getMock(
            'GlobalConfigApi',
            array('checkIsAdmin', 'getConfig', 'requireArgs', 'updateStatus', 'getSubscriptionsRegistry')
        );
        $api->expects($this->once())->method('checkIsAdmin')->with($this->equalTo($this->api));

        $api->expects($this->once())->method('requireArgs')
            ->with(
                $this->equalTo($args),
                $this->logicalAnd($this->contains('carriers'), $this->contains('carriers'))
            );

        $api->expects($this->once())->method('updateStatus')
            ->with($this->equalTo($configCarriers));

        $registry = $this->getMock(self::NS_REGISTRY, array('setGlobalConfiguration'));
        $registry->expects($this->once())->method('setGlobalConfiguration')->with($this->equalTo($configDelivery));

        $api->expects($this->once())->method('getSubscriptionsRegistry')->willReturn($registry);

        $api->expects($this->once())->method('getConfig')
            ->with($this->equalTo($this->api))
            ->willReturn($configRes);


        $res = $api->updateConfig($this->api, $args);
        $this->assertEquals($configRes, $res);
    }

    public function testCheckIfAdmin()
    {
        $user = $this->getMock('User', array('isAdmin'));
        $user->expects($this->once())->method('isAdmin')->willReturn(true);

        $this->api->user = $user;
        $api = new GlobalConfigApi();
        SugarTestReflection::callProtectedMethod($api, 'checkIsAdmin', array($this->api));
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testCheckIfNotAdmin()
    {
        $user = $this->getMock('User', array('isAdmin'));
        $user->expects($this->once())->method('isAdmin')->willReturn(false);

        $this->api->user = $user;
        $api = new GlobalConfigApi();
        SugarTestReflection::callProtectedMethod($api, 'checkIsAdmin', array($this->api));
    }

    public function testHandleSave()
    {
        $carrierModules = array(
            'carrierModule1',
            'carrierModule2',
            'carrierModule3',
            'carrierModule4',
            'carrierModule5'
        );
        $carriers = array(
            'carrierModule1' => array('status' => 1),
            'carrierNotExists1' => array('status' => false),
            'carrierModule2' => array('status' => null),
            'carrierNotExists2' => array('status' => true),
            'carrierModule3' => array('status' => 0),
            'carrierModule4' => array('status' => true),
            'carrierModule5' => array('status' => false),
        );

        $registry = $this->getMock(self::NS_CARRIER_REGISTRY, array('getCarriers'));
        $registry->expects($this->atLeastOnce())->method('getCarriers')->willReturn($carrierModules);

        $status = $this->getMock(self::NS_STATUS, array('setCarrierStatus'));
        $status->expects($this->exactly(count($carrierModules)))
            ->method('setCarrierStatus')
            ->withConsecutive(
                array($this->equalTo('carrierModule1'), $this->isTrue()),
                array($this->equalTo('carrierModule2'), $this->isFalse()),
                array($this->equalTo('carrierModule3'), $this->isFalse()),
                array($this->equalTo('carrierModule4'), $this->isTrue()),
                array($this->equalTo('carrierModule5'), $this->isFalse())
            );

        $configApi = $this->getMock('GlobalConfigApi', array('getStatus', 'getCarrierRegistry'));
        $configApi->expects($this->atLeastOnce())->method('getStatus')->willReturn($status);
        $configApi->expects($this->atLeastOnce())->method('getCarrierRegistry')->willReturn($registry);

        SugarTestReflection::callProtectedMethod($configApi, 'updateStatus', array($carriers));
    }

    public function testGetCarriersConfig()
    {
        $expect = array(
            'carrierModule1' => array(
                'status' => true,
                'configurable' => true,
                'isConfigured' => true,
                'configLayout' => 'carrierModule1ConfigLayout'
            ),
            'carrierModule2' => array(
                'status' => false,
                'configurable' => true,
                'isConfigured' => true,
                'configLayout' => 'carrierModule2ConfigLayout'
            ),
            'carrierModule3' => array(
                'status' => true,
                'configurable' => true,
                'isConfigured' => false,
                'configLayout' => 'carrierModule3ConfigLayout'
            ),
            'carrierModule4' => array(
                'status' => true,
                'configurable' => false,
                'isConfigured' => true,
                'configLayout' => null
            ),
            'carrierModule5' => array(
                'status' => false,
                'configurable' => false,
                'isConfigured' => true,
                'configLayout' => null
            )
        );

        $registry = $this->getMock(self::NS_CARRIER_REGISTRY, array('getCarriers', 'getCarrier'));
        $registry->expects($this->atLeastOnce())->method('getCarriers')->willReturn(array_keys($expect));

        $status = $this->getMock(self::NS_STATUS, array('getCarrierStatus'));

        $statusMap = array();
        $carrierMap = array();
        foreach ($expect as $module => $moduleConfig) {
            $statusMap[] = array($module, $moduleConfig['status']);

            if ($moduleConfig['configurable']) {
                $carrier = $this->getMock(self::NS_CARRIER_CONFIGURABLE, array('getConfigLayout', 'isConfigured'));
                $carrier->expects($this->once())->method('getConfigLayout')
                    ->willReturn($moduleConfig['configLayout']);
                $carrier->expects($this->once())->method('isConfigured')
                    ->willReturn($moduleConfig['isConfigured']);
            } else {
                $carrier = $this->getMock(
                    self::NS_CARRIER_BASE,
                    array('getConfigLayout', 'isConfigured', 'getTransport', 'getMessageSignature', 'getAddressType')
                );
                $carrier->expects($this->never())->method('getConfigLayout');
                $carrier->expects($this->never())->method('isConfigured');
            }

            $carrierMap[] = array($module, $carrier);
        }
        $registry->expects($this->exactly(count($expect)))->method('getCarrier')
            ->will($this->returnValueMap($carrierMap));

        $status->expects($this->exactly(count($expect)))
            ->method('getCarrierStatus')
            ->will($this->returnValueMap($statusMap));

        $configApi = $this->getMock('GlobalConfigApi', array('getStatus', 'getCarrierRegistry'));
        $configApi->expects($this->atLeastOnce())->method('getStatus')->willReturn($status);
        $configApi->expects($this->atLeastOnce())->method('getCarrierRegistry')->willReturn($registry);

        $resConfig = SugarTestReflection::callProtectedMethod($configApi, 'getCarriersConfig', array());
        $this->assertEquals($expect, $resConfig);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->api = SugarTestRestUtilities::getRestServiceMock();
    }
}
