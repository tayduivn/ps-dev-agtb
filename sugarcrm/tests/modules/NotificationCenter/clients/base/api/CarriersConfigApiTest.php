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

require_once 'modules/NotificationCenter/clients/base/api/CarriersConfigApi.php';

/**
 * Testing CarriersConfigApi
 *
 * Class CarriersConfigApiTest
 */
class CarriersConfigApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api;

    const NS_CARRIER_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\CarrierRegistry';
    const NS_STATUS = 'Sugarcrm\\Sugarcrm\\Notification\\Config\\Status';

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
        $this->api = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testGetConfig()
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
            $expect[$module] = $statusVal;
        }
        $status->expects($this->exactly(count($carrierModules)))
            ->method('getCarrierStatus')
            ->will($this->returnValueMap($statusMap));

        $configApi = $this->getMock('CarriersConfigApi', array('getStatus', 'getCarrierRegistry'));
        $configApi->expects($this->atLeastOnce())->method('getStatus')->willReturn($status);
        $configApi->expects($this->atLeastOnce())->method('getCarrierRegistry')->willReturn($registry);

        $this->assertEquals($expect, $configApi->getConfig($this->api, array()));
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
        $args = array(
            'carrierModule1' => 1,
            'carrierNotExists1' => false,
            'carrierModule2' => null,
            'carrierNotExists2' => true,
            'carrierModule3'=> 0,
            'carrierModule4'=> true,
            'carrierModule5'=> false,
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

        $configApi = $this->getMock('CarriersConfigApi', array('getStatus', 'getCarrierRegistry'));
        $configApi->expects($this->atLeastOnce())->method('getStatus')->willReturn($status);
        $configApi->expects($this->atLeastOnce())->method('getCarrierRegistry')->willReturn($registry);

        $configApi->handleSave($this->api, $args);
    }
}
