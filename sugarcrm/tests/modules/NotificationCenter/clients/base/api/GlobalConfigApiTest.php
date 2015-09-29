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
    private $api;

    const NS_REGISTRY = 'Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry';
    const NS_CARRIER_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\CarrierRegistry';
    const NS_STATUS = 'Sugarcrm\\Sugarcrm\\Notification\\Config\\Status';

    protected function setUp()
    {
        parent::setUp();
        $this->api = SugarTestRestUtilities::getRestServiceMock();
    }

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
            'carrierNotExists1' => array('status' =>  false),
            'carrierModule2' =>  array('status' => null),
            'carrierNotExists2' =>  array('status' => true),
            'carrierModule3'=>  array('status' => 0),
            'carrierModule4'=>  array('status' => true),
            'carrierModule5'=>  array('status' => false),
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
        $carrierModules = array('carrierModule1', 'carrierModule2', 'carrierModule3', 'carrierModule4');
        $expect = array();

        $registry = $this->getMock(self::NS_CARRIER_REGISTRY, array('getCarriers'));
        $registry->expects($this->atLeastOnce())->method('getCarriers')->willReturn($carrierModules);

        $status = $this->getMock(self::NS_STATUS, array('getCarrierStatus'));

        $statusMap = array();
        foreach ($carrierModules as $key => $module) {
            $statusVal = (bool)($key % 2);
            $statusMap[] = array($module, $statusVal);
            $expect[$module] = array('status' => $statusVal);
        }
        $status->expects($this->exactly(count($carrierModules)))
            ->method('getCarrierStatus')
            ->will($this->returnValueMap($statusMap));

        $configApi = $this->getMock('GlobalConfigApi', array('getStatus', 'getCarrierRegistry'));
        $configApi->expects($this->atLeastOnce())->method('getStatus')->willReturn($status);
        $configApi->expects($this->atLeastOnce())->method('getCarrierRegistry')->willReturn($registry);

        $resConfig = SugarTestReflection::callProtectedMethod($configApi, 'getCarriersConfig', array());
        $this->assertEquals($expect, $resConfig);
    }
}
