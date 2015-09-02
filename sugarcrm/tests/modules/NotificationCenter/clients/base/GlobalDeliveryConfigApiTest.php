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

require_once 'modules/NotificationCenter/clients/base/api/GlobalDeliveryConfigApi.php';

/**
 * Class GlobalDeliveryConfigApiTest
 * @coversDefaultClass \GlobalSearchApi
 */
class GlobalDeliveryConfigApiTest extends Sugar_PHPUnit_Framework_TestCase
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
        $config = array('some', 'Global', 'Delivery', 'ConfigApiTest');

        $registry = $this->getMock(self::NS_REGISTRY, array('getGlobalConfiguration'));
        $registry->expects($this->once())->method('getGlobalConfiguration')->willReturn($config);

        $api = $this->getMock('GlobalDeliveryConfigApi', array('getSubscriptionsRegistry'));
        $api->expects($this->once())->method('getSubscriptionsRegistry')->willReturn($registry);

        $res = $api->getConfig($this->api, array());
        $this->assertEquals($config, $res);
    }

    /**
     * @covers ::putConfig
     */
    public function testPutConfig()
    {
        $config = array('some', 'Global', 'Delivery', 'ConfigApiTest');
        $args = array('config' => $config);

        $registry = $this->getMock(self::NS_REGISTRY, array('setGlobalConfiguration', 'getGlobalConfiguration'));
        $registry->expects($this->once())->method('getGlobalConfiguration')->willReturn($config);
        $registry->expects($this->once())->method('setGlobalConfiguration')
            ->with($this->equalTo($config));

        $api = $this->getMock('GlobalDeliveryConfigApi', array('getSubscriptionsRegistry', 'requireArgs'));
        $api->expects($this->once())->method('getSubscriptionsRegistry')->willReturn($registry);

        $res = $api->putConfig($this->api, $args);
        $this->assertEquals($config, $res);
    }
}
