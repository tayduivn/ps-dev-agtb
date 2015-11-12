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

require_once 'modules/NotificationCenter/clients/base/api/NotificationCenterConfigApi.php';
require_once 'modules/CarrierEmail/Carrier.php';

/**
 * @coversDefaultClass \NotificationCenterConfigApi
 *
 * Class NotificationCenterConfigApiTest
 */
class NotificationCenterConfigApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    const NS_REGISTRY = 'Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry';
    const NS_CARRIER_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\CarrierRegistry';
    const NS_ADDRESS_TYPE = 'Sugarcrm\\Sugarcrm\\Notification\\Carrier\\AddressType\\Email';

    private $api;

    protected function setUp()
    {
        parent::setUp();
        $this->api = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    public function testUserConfig()
    {
        $this->api->user = SugarTestUserUtilities::createAnonymousUser();

        $globalDeliveryConfig = array('some', 'test', 'Global', 'Delivery', 'Config');
        $globalCarrierConfig = array('some', 'test', 'Global', 'Carrier', 'Config');
        $personalCarrierConfig = array('some', 'test', 'personal', 'Carrier', 'Config');
        $personalDeliveryConfig = array('some', 'test', 'personal', 'Delivery', 'Config');

        $registry = $this->getMock(static::NS_REGISTRY, array('getGlobalConfiguration', 'getUserConfiguration'));
        $registry->expects($this->once())->method('getGlobalConfiguration')->willReturn($globalDeliveryConfig);
        $registry->expects($this->once())->method('getUserConfiguration')
            ->with($this->equalTo($this->api->user->id))
            ->willReturn($personalDeliveryConfig);

        $configApi = $this->getMock(
            'NotificationCenterConfigApi',
            array('getSubscriptionsRegistry', 'getCarriersConfig', 'getPersonalCarriers')
        );

        $configApi->expects($this->atLeastOnce())->method('getSubscriptionsRegistry')->willReturn($registry);
        $configApi->expects($this->once())->method('getCarriersConfig')->willReturn($globalCarrierConfig);
        $configApi->expects($this->once())->method('getPersonalCarriers')
            ->with($this->equalTo($this->api->user))
            ->willReturn($personalCarrierConfig);

        $userConfig = $configApi->getUserConfig($this->api, array());

        $this->assertArrayHasKey('global', $userConfig);
        $this->assertArrayHasKey('personal', $userConfig);
        $this->assertArrayHasKey('carriers', $userConfig['global']);
        $this->assertArrayHasKey('config', $userConfig['global']);
        $this->assertArrayHasKey('carriers', $userConfig['personal']);
        $this->assertArrayHasKey('config', $userConfig['personal']);

        $this->assertEquals($globalDeliveryConfig, $userConfig['global']['config']);
        $this->assertEquals($globalCarrierConfig, $userConfig['global']['carriers']);
        $this->assertEquals($personalCarrierConfig, $userConfig['personal']['carriers']);
        $this->assertEquals($personalCarrierConfig, $userConfig['personal']['carriers']);
    }

    public function testUpdateUserConfig()
    {
        $this->api->user = SugarTestUserUtilities::createAnonymousUser();

        $args = array(
            'personal' => array(
                'carriers' => array('some', 'test', 'personal', 'Carrier', 'Config'),
                'config' => array('some', 'test', 'personal', 'Delivery', 'Config'),
            ),
            'someKey' => array('some', 'other', 'data')
        );

        $confSaved = array('saved', 'config');

        $registry = $this->getMock(static::NS_REGISTRY, array('setUserConfiguration'));
        $registry->expects($this->once())->method('setUserConfiguration')
            ->with($this->equalTo($this->api->user->id), $this->equalTo($args['personal']['config']));

        $configApi = $this->getMock(
            'NotificationCenterConfigApi',
            array('getSubscriptionsRegistry', 'getUserConfig', 'updatePersonalCarriers', 'requireArgs')
        );
        $configApi->expects($this->atLeastOnce())->method('getSubscriptionsRegistry')->willReturn($registry);

        $configApi->expects($this->at(0))->method('requireArgs')
            ->with($this->equalTo($args), $this->contains('personal'));
        $configApi->expects($this->at(1))->method('requireArgs')
            ->with(
                $this->equalTo($args['personal']),
                $this->logicalAnd($this->contains('carriers'), $this->contains('config'))
            );

        $configApi->expects($this->once())->method('updatePersonalCarriers')
            ->with($this->equalTo($this->api->user), $this->equalTo($args['personal']['carriers']));

        $configApi->expects($this->once())->method('getUserConfig')
            ->with($this->equalTo($this->api), $this->equalTo($args))
            ->willReturn($confSaved);

        $res = $configApi->updateUserConfig($this->api, $args);

        $this->assertEquals($confSaved, $res);
    }

    public function testUpdatePersonalCarriers()
    {
        $inputCarriers = array(
            'CarrierExists1' => array(
                'status' => true,
                'selectable' => false,
                'options' => array('id' => 'userid')
            ),
            'CarrierExists2' => array(
                'status' => false,
                'selectable' => false,
                'options' => array('id' => 'userid')
            ),
            'CarrierExists3' => array(
                'status' => 1,
                'selectable' => false,
            ),
            'InvalidCarrierName' => array(
                'status' => 1,
                'selectable' => false,
            )
        );

        $expectedCarriers = array(
            'CarrierExists1' => true,
            'CarrierExists2' => false,
            'CarrierExists3' => true,
            'CarrierNotExists1' => false,
            'CarrierNotExists2' => false
        );

        $carriersList = array_keys($expectedCarriers);

        $registry = $this->getMock(static::NS_CARRIER_REGISTRY, array('getCarriers'));
        $registry->expects($this->once())->method('getCarriers')->willReturn($carriersList);

        $configApi = $this->getMock('NotificationCenterConfigApi', array('getCarrierRegistry'));
        $configApi->expects($this->atLeastOnce())->method('getCarrierRegistry')->willReturn($registry);

        $user = $this->getMock('User', array('setPreference'));
        $user->expects($this->once())->method('setPreference')
            ->with(
                $this->equalTo(NotificationCenterConfigApi::CARRIER_STATUS_NAME),
                $this->equalTo($expectedCarriers),
                $this->anything(),
                $this->equalTo(NotificationCenterConfigApi::CARRIER_STATUS_CATEGORY)
            );

        SugarTestReflection::callProtectedMethod($configApi, 'updatePersonalCarriers', array($user, $inputCarriers));
    }

    public function testGetPersonalCarriers()
    {
        $carriersSaved = array(
            'CarrierExists1' => true,
            'CarrierExists2' => false,
            'CarrierNotExists1' => false,
            'CarrierNotExists2' => false
        );

        $expectedCarriers = array(
            'CarrierExists1' => array(
                'status' => true,
                'selectable' => false,
                'options' => array('id' => 'userid')
            ),
            'CarrierExists2' => array(
                'status' => false,
                'selectable' => true,
                'options' => array('key1' => 'keyValue1')
            ),
        );

        $user = $this->getMock('User', array('getPreference'));
        $user->expects($this->once())->method('getPreference')
            ->willReturn($carriersSaved)
            ->with(
                $this->equalTo(NotificationCenterConfigApi::CARRIER_STATUS_NAME),
                $this->equalTo(NotificationCenterConfigApi::CARRIER_STATUS_CATEGORY)
            );

        $addressType1 = $this->getMock(self::NS_ADDRESS_TYPE, array('isSelectable', 'getOptions'));
        $addressType1->expects($this->once())->method('isSelectable')
            ->willReturn($expectedCarriers['CarrierExists1']['selectable']);
        $addressType1->expects($this->once())->method('getOptions')
            ->willReturn($expectedCarriers['CarrierExists1']['options'])
            ->with($this->equalTo($user));

        $carrier1 = $this->getMock('CarrierEmailCarrier', array('getAddressType'));
        $carrier1->expects($this->atLeastOnce())->method('getAddressType')->willReturn($addressType1);

        $addressType2 = $this->getMock(self::NS_ADDRESS_TYPE, array('isSelectable', 'getOptions'));
        $addressType2->expects($this->once())->method('isSelectable')
            ->willReturn($expectedCarriers['CarrierExists2']['selectable']);
        $addressType2->expects($this->once())->method('getOptions')
            ->willReturn($expectedCarriers['CarrierExists2']['options'])
            ->with($this->equalTo($user));

        $carrier2 = $this->getMock('CarrierEmailCarrier', array('getAddressType'));
        $carrier2->expects($this->atLeastOnce())->method('getAddressType')->willReturn($addressType2);

        $registry = $this->getMock(static::NS_CARRIER_REGISTRY, array('getCarriers', 'getCarrier'));
        $registry->expects($this->atLeastOnce())->method('getCarriers')->willReturn(array_keys($expectedCarriers));

        $carriersMap = array(
            array('CarrierExists1', $carrier1),
            array('CarrierExists2', $carrier2),
        );
        $registry->expects($this->exactly(count($expectedCarriers)))->method('getCarrier')
            ->will($this->returnValueMap($carriersMap));

        $configApi = $this->getMock('NotificationCenterConfigApi', array('getCarrierRegistry', 'getCarriersConfig'));
        $configApi->expects($this->once())->method('getCarriersConfig')->willReturn(array(
            'CarrierExists1' => array('status' => $expectedCarriers['CarrierExists1']['status']),
            'CarrierExists2' => array('status' => $expectedCarriers['CarrierExists2']['status']),
            'CarrierNotExists1' => array('status' => false),
        ));
        $configApi->expects($this->atLeastOnce())->method('getCarrierRegistry')->willReturn($registry);

        $res = SugarTestReflection::callProtectedMethod($configApi, 'getPersonalCarriers', array($user));

        $this->assertArrayHasKey('CarrierExists1', $expectedCarriers);
        $this->assertEquals($expectedCarriers['CarrierExists1']['status'], $res['CarrierExists1']['status']);
        $this->assertEquals($expectedCarriers['CarrierExists1']['selectable'], $res['CarrierExists1']['selectable']);
        $this->assertEquals((object)$expectedCarriers['CarrierExists1']['options'], $res['CarrierExists1']['options']);
        $this->assertArrayHasKey('CarrierExists2', $expectedCarriers);
        $this->assertEquals($expectedCarriers['CarrierExists2']['status'], $res['CarrierExists2']['status']);
        $this->assertEquals($expectedCarriers['CarrierExists2']['selectable'], $res['CarrierExists2']['selectable']);
        $this->assertEquals((object)$expectedCarriers['CarrierExists2']['options'], $res['CarrierExists2']['options']);
        $this->assertCount(count($expectedCarriers), $res);
    }
}
