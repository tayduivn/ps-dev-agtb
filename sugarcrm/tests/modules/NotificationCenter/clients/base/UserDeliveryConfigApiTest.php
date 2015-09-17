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

require_once 'modules/NotificationCenter/clients/base/api/UserDeliveryConfigApi.php';

/**
 * Class UserDeliveryConfigApiTest
 * @coversDefaultClass \UserDeliveryConfigApi
 */
class UserDeliveryConfigApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api;

    const NS_REGISTRY = 'Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry';

    protected function setUp()
    {
        parent::setUp();
        $this->api = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers ::getConfig
     */
    public function testGetConfig()
    {
        $this->api->user = \SugarTestUserUtilities::createAnonymousUser(false);
        $config = array('some', 'User', 'Delivery', 'ConfigApiTest');

        $registry = $this->getMock(self::NS_REGISTRY, array('getUserConfiguration'));
        $registry->expects($this->once())->method('getUserConfiguration')
            ->willReturn($config)
            ->with($this->equalTo($this->api->user->id));

        $api = $this->getMock('UserDeliveryConfigApi', array('getSubscriptionsRegistry'));
        $api->expects($this->once())->method('getSubscriptionsRegistry')->willReturn($registry);

        $res = $api->getConfig($this->api, array());
        $this->assertEquals($config, $res);
    }

    /**
     * @covers ::putConfig
     */
    public function testPutConfig()
    {
        $this->api->user = \SugarTestUserUtilities::createAnonymousUser(false);
        $config = array('some', 'User', 'Delivery', 'ConfigApiTest');
        $args = array('config' => $config);

        $registry = $this->getMock(self::NS_REGISTRY, array('setUserConfiguration', 'getUserConfiguration'));
        $registry->expects($this->once())->method('getUserConfiguration')->willReturn($config);
        $registry->expects($this->once())->method('setUserConfiguration')
            ->with($this->equalTo($this->api->user->id), $this->equalTo($config));

        $api = $this->getMock('UserDeliveryConfigApi', array('getSubscriptionsRegistry', 'requireArgs'));
        $api->expects($this->once())->method('getSubscriptionsRegistry')->willReturn($registry);
        $api->expects($this->once())->method('requireArgs')
            ->with($this->equalTo($args), $this->equalTo(array('config')));

        $res = $api->putConfig($this->api, $args);
        $this->assertEquals($config, $res);
    }
}
