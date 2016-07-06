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

namespace Sugarcrm\SugarcrmTests\Notification;

use Sugarcrm\Sugarcrm\Util\Uuid;
use Sugarcrm\Sugarcrm\Notification\EmitterRegistry;
use Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry;
use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;
use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event as BeanEvent;
use Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterInterface;

/**
 * Class SubscriptionsRegistryTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry
 */
class SubscriptionsRegistryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var SubscriptionsRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $subscriptionsRegistry = null;

    /** @var \Sugarcrm\Sugarcrm\Notification\CarrierRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $carrierRegistry = null;

    /** @var \Sugarcrm\Sugarcrm\Notification\Config\Status|\PHPUnit_Framework_MockObject_MockObject */
    protected $carrierStatus = null;

    /** @var \SugarTestDatabaseMock */
    protected $db;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        \SugarTestHelper::ensureDir(array(
            'modules/CallsCRYS1301',
            'modules/MeetingsCRYS1301',
            'modules/UsersCRYS1301',
            'modules/AccountsCRYS1301',
        ));

        \SugarTestHelper::saveFile(array(
            sugar_cached(EmitterRegistry::CACHE_FILE),
            'modules/CallsCRYS1301/Emitter.php',
            'modules/MeetingsCRYS1301/Emitter.php',
            'modules/UsersCRYS1301/Emitter.php',
            'modules/AccountsCRYS1301/Emitter.php',
            'modules/CallsCRYS1301/CallCRYS1301.php',
            'modules/MeetingsCRYS1301/MeetingCRYS1301.php',
            'modules/UsersCRYS1301/UserCRYS1301.php',
            'modules/AccountsCRYS1301/AccountCRYS1301.php',
        ));

        static::saveBean('CallsCRYS1301', 'CallCRYS1301', 'Call');
        static::saveBean('MeetingsCRYS1301', 'MeetingCRYS1301', 'Meeting');
        static::saveBean('UsersCRYS1301', 'UserCRYS1301', 'User');


        static::saveEmitter('CallCRYS1301', 'CallsCRYS1301');
        static::saveEmitter('MeetingCRYS1301', 'MeetingsCRYS1301');
        static::saveEmitter('CallCRYS1301', 'CallsCRYS1301');
        static::saveEmitter('MeetingCRYS1301', 'MeetingsCRYS1301');

        static::saveBeanEmitter('UserCRYS1301', 'UsersCRYS1301');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        \SugarTestHelper::setUp('moduleList');
        \SugarTestHelper::setUp('beanList');
        \SugarTestHelper::setUp('files');

        $GLOBALS['moduleList'] = array(
            'CallsCRYS1301',
            'MeetingsCRYS1301',
            'UsersCRYS1301',
            'AccountsCRYS1301',
        );
        $GLOBALS['beanList'] = array(
            'CallsCRYS1301' => 'CallCRYS1301',
            'MeetingsCRYS1301' => 'MeetingCRYS1301',
            'UsersCRYS1301' => 'UserCRYS1301',
            'AccountsCRYS1301' => 'AccountCRYS1301',
        );

        $this->db = \SugarTestHelper::setUp('mock_db');
        if (\SugarAutoLoader::fileExists(sugar_cached(EmitterRegistry::CACHE_FILE))) {
            \SugarAutoLoader::unlink(sugar_cached(EmitterRegistry::CACHE_FILE));
        }

        \BeanFactory::setBeanClass(
            'NotificationCenterSubscriptions',
            'Sugarcrm\SugarcrmTests\Notification\NotificationCenterSubscriptionCRYS1301'
        );

        $this->carrierRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\CarrierRegistry');
        $this->carrierStatus = $this->getMock('Sugarcrm\Sugarcrm\Notification\Config\Status');

        /** @var SubscriptionsRegistry|\PHPUnit_Framework_MockObject_MockObject $subscriptionFilterRegistry */
        $subscriptionFilterRegistry = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterRegistry'
        );

        /** @var SubscriptionFilterInterface|\PHPUnit_Framework_MockObject_MockObject $noSupportedFilter */
        $noSupportedFilter = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterInterface'
        );
        $noSupportedFilter->method('getOrder')->willReturn(rand(1, 99));
        $noSupportedFilter->method('supports')->willReturn(false);

        /** @var SubscriptionFilterInterface|\PHPUnit_Framework_MockObject_MockObject $callFilter */
        $callFilter = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterInterface'
        );
        $callFilter->method('getOrder')->willReturn(rand(100, 199));
        $callFilter->method('supports')->willReturn(true);
        $callFilter->method('__toString')->willReturn('CallFilterCRYS1301');
        $callFilter->method('filterQuery')->willReturnCallback(function ($event, \SugarQuery $query) {
            $query->from(\BeanFactory::getBean('UsersCRYS1301'), array('team_security' => false));
        });

        /** @var SubscriptionFilterInterface|\PHPUnit_Framework_MockObject_MockObject $meetingFilter */
        $meetingFilter = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterInterface'
        );
        $meetingFilter->method('supports')->willReturnCallback(function ($event) {
            return $event instanceof BeanEvent;
        });
        $meetingFilter->method('getOrder')->willReturn(rand(200, 299));
        $meetingFilter->method('__toString')->willReturn('MeetingFilterCRYS1301');
        $meetingFilter->method('filterQuery')->willReturnCallback(function ($event, \SugarQuery $query) {
            $query->from(\BeanFactory::getBean('UsersCRYS1301'), array('team_security' => false));
        });

        /** @var SubscriptionFilterInterface|\PHPUnit_Framework_MockObject_MockObject $meetingFilter */
        $userFilter = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterInterface'
        );
        $userFilter->method('getOrder')->willReturn(rand(300, 399));
        $userFilter->method('supports')->willReturn(true);
        $userFilter->method('__toString')->willReturn('UserFilterCRYS1301');
        $userFilter->method('filterQuery')->willReturnCallback(function ($event, \SugarQuery $query) {
            $query->from(\BeanFactory::getBean('UsersCRYS1301'), array('team_security' => false));
        });

        $this->subscriptionsRegistry = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry',
            array(
                'getSubscriptionFilterRegistry',
                'getCarrierRegistry',
                'getCarrierStatus',
                'getSingleDeliveryCarriers',
            )
        );
        $this->subscriptionsRegistry->method('getSubscriptionFilterRegistry')->willReturn($subscriptionFilterRegistry);
        $this->subscriptionsRegistry->method('getCarrierRegistry')->willReturn($this->carrierRegistry);
        $this->subscriptionsRegistry->method('getCarrierStatus')->willReturn($this->carrierStatus);

        $subscriptionFilterRegistry->method('getFilters')->willReturn(array(
            'NotSupportsFilterCRYS1301',
            'MeetingFilterCRYS1301',
            'CallFilterCRYS1301',
            'UserFilterCRYS1301',
        ));

        $subscriptionFilterRegistry->method('getFilter')->willReturnMap(array(
            array('NotSupportsFilterCRYS1301', $noSupportedFilter),
            array('MeetingFilterCRYS1301', $meetingFilter),
            array('CallFilterCRYS1301', $callFilter),
            array('UserFilterCRYS1301', $userFilter),
        ));

    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        \BeanFactory::setBeanClass('NotificationCenterSubscriptions');
        NotificationCenterSubscriptionCRYS1301::$beans = array();
        NotificationCenterSubscriptionCRYS1301::$fetchParams = array();
        NotificationCenterSubscriptionCRYS1301::$savedArrayFields = array();
        NotificationCenterSubscriptionCRYS1301::$markDeleted = array();
        BeanEventCRYS1301::$currentBean = null;
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Data provider for testGetGlobalConfiguration.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionsRegistryTest::testGetGlobalConfiguration
     * @return array
     */
    public static function getGlobalConfigurationProvider()
    {
        $carrierName1 = 'Carrier' . rand(1000, 1999);
        $carrierName2 = 'Carrier' . rand(2000, 3999);
        $carrierName3 = 'Carrier' . rand(3000, 3999);
        $carrierName4 = 'Carrier' . rand(4000, 4999);
        $carrierName5 = 'Carrier' . rand(5000, 5999);

        $carrierOption1 = array(rand(1000, 1999));
        $carrierOption2 = array(rand(2000, 2999));
        $carrierOption3 = array(rand(3000, 3999));
        $carrierOption4 = array(rand(4000, 4999));
        $carrierOption5 = array(rand(5000, 5999));


        return array(
            'globalConfig' => array(
                'beans' => array(
                    'ApplicationTypeNotReturnGlobalConfig' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'application',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'filter_name' => 'CallFilterCRYS1301',
                        'carrier_name' => $carrierName1,
                        'carrier_option' => $carrierOption1,
                        'event_name' => 'reminder',
                    ),
                    'ApplicationTypeNotSuitableGlobalConfig' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'application',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'filter_name' => 'CallFilterCRYS1301',
                        'carrier_name' => $carrierName1,
                        'carrier_option' => $carrierOption1,
                        'event_name' => 'reminder',
                    ),
                    'ModuleTypeReturnGlobalConfig' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'module',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'filter_name' => 'CallFilterCRYS1301',
                        'carrier_name' => $carrierName2,
                        'carrier_option' => $carrierOption2,
                        'event_name' => 'reminder',
                    ),
                    'BeanTypeReturnGlobalConfig' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'bean',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'filter_name' => 'CallFilterCRYS1301',
                        'carrier_name' => $carrierName2,
                        'carrier_option' => $carrierOption2,
                        'event_name' => 'reminder',
                    ),
                    'ModuleTypeWrongFilterNotReturnGlobalConfig' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'module',
                        'emitter_module_name' => 'MeetingsCRYS1301',
                        'filter_name' => 'CallFilterCRYS1301',
                        'carrier_name' => $carrierName3,
                        'carrier_option' => $carrierOption3,
                        'event_name' => 'reminder',
                    ),
                    'ModuleTypeBeanEventReturnGlobalConfig' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'module',
                        'emitter_module_name' => 'MeetingsCRYS1301',
                        'filter_name' => 'MeetingFilterCRYS1301',
                        'carrier_name' => $carrierName4,
                        'carrier_option' => $carrierOption4,
                        'event_name' => 'reminder',
                    ),
                    'ModuleTypeNotSupportedFilter' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'module',
                        'emitter_module_name' => 'MeetingsCRYS1301',
                        'filter_name' => 'NotSupportsFilterCRYS1301',
                        'carrier_name' => $carrierName5,
                        'carrier_option' => $carrierOption5,
                        'event_name' => 'reminder',
                    ),
                    'BeanEventShouldSetBean' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'bean',
                        'emitter_module_name' => 'UsersCRYS1301',
                        'filter_name' => 'UserFilterCRYS1301',
                        'carrier_name' => $carrierName5,
                        'carrier_option' => $carrierOption5,
                        'event_name' => 'reminder',
                    ),
                ),
                'expectedConfiguration' => array(
                    'CallsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array($carrierName2, $carrierOption2),
                            ),
                            'UserFilterCRYS1301' => array(),
                        ),
                    ),
                    'MeetingsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array($carrierName3, $carrierOption3),
                            ),
                            'UserFilterCRYS1301' => array(),
                        ),
                    ),
                    'UsersCRYS1301' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => array(),
                            'CallFilterCRYS1301' => array(),
                            'UserFilterCRYS1301' => array(),
                        ),
                    ),
                    'BeanEmitter' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => array(),
                            'CallFilterCRYS1301' => array(
                                array($carrierName2, $carrierOption2),
                            ),
                            'UserFilterCRYS1301' => array(
                                array($carrierName5, $carrierOption5),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Should return global configuration.
     *
     * @dataProvider getGlobalConfigurationProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry::getGlobalConfiguration
     * @param array $beans
     * @param array $expectedConfiguration
     */
    public function testGetGlobalConfiguration($beans, $expectedConfiguration)
    {
        /** @var NotificationCenterSubscriptionCRYS1301 $notificationBean */
        $notificationBean = \BeanFactory::newBean('NotificationCenterSubscriptions');
        $notificationBean->new_with_id = true;
        $notificationBean->id = 'query_id';

        $expectedQuery = new \SugarQuery();
        $expectedQuery->from($notificationBean);
        $expectedQuery->where()->isNull('user_id');

        $expectedFields = array(
            'type',
            'emitter_module_name',
            'event_name',
            'filter_name',
            'carrier_name',
            'carrier_option',
        );

        foreach ($beans as $beanData) {
            $bean = new NotificationCenterSubscriptionCRYS1301();
            $bean->id = $beanData['id'];
            $bean->type = $beanData['type'];
            $bean->emitter_module_name = $beanData['emitter_module_name'];
            $bean->event_name = $beanData['event_name'];
            $bean->filter_name = $beanData['filter_name'];
            $bean->carrier_name = $beanData['carrier_name'];
            $bean->carrier_option = $beanData['carrier_option'];
            NotificationCenterSubscriptionCRYS1301::$beans[] = $bean;
        }
        $config = $this->subscriptionsRegistry->getGlobalConfiguration();
        $this->assertEquals($expectedConfiguration, $config);
        $this->assertEquals(
            array($expectedQuery, $expectedFields),
            NotificationCenterSubscriptionCRYS1301::$fetchParams
        );
        $this->assertInstanceOf('UserCRYS1301', BeanEventCRYS1301::$currentBean);
    }

    /**
     * Data provider for testGetUserConfiguration.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionsRegistryTest::testGetUserConfiguration
     * @return array
     */
    public static function getUserConfigurationProvider()
    {
        $carrierName1 = 'Carrier' . rand(1000, 1999);
        $carrierName2 = 'Carrier' . rand(2000, 3999);
        $carrierName3 = 'Carrier' . rand(3000, 3999);
        $carrierName4 = 'Carrier' . rand(4000, 4999);
        $carrierName5 = 'Carrier' . rand(5000, 5999);

        $carrierOption1 = array(rand(1000, 1999));
        $carrierOption2 = array(rand(2000, 2999));
        $carrierOption3 = array(rand(3000, 3999));
        $carrierOption4 = array(rand(4000, 4999));
        $carrierOption5 = array(rand(5000, 5999));


        return array(
            'globalConfig' => array(
                'idUser' => Uuid::uuid1(),
                'beans' => array(
                    'ApplicationTypeNotReturnGlobalConfig' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'application',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'filter_name' => 'CallFilterCRYS1301',
                        'carrier_name' => $carrierName1,
                        'carrier_option' => $carrierOption1,
                    ),
                    'ModuleTypeReturnGlobalConfig' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'module',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'filter_name' => 'CallFilterCRYS1301',
                        'carrier_name' => $carrierName2,
                        'carrier_option' => $carrierOption2,
                    ),
                    'ModuleTypeWrongFilterNotReturnGlobalConfig' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'module',
                        'emitter_module_name' => 'MeetingsCRYS1301',
                        'filter_name' => 'CallFilterCRYS1301',
                        'carrier_name' => $carrierName3,
                        'carrier_option' => $carrierOption3,
                    ),
                    'ModuleTypeBeanEventReturnGlobalConfig' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'module',
                        'emitter_module_name' => 'MeetingsCRYS1301',
                        'filter_name' => 'MeetingFilterCRYS1301',
                        'carrier_name' => $carrierName4,
                        'carrier_option' => $carrierOption4,
                    ),
                    'ModuleTypeNotSupportedFilter' => array(
                        'id' => Uuid::uuid1(),
                        'type' => 'module',
                        'emitter_module_name' => 'MeetingsCRYS1301',
                        'filter_name' => 'NotSupportsFilterCRYS1301',
                        'carrier_name' => $carrierName5,
                        'carrier_option' => $carrierOption5,
                    ),
                ),
                'expectedConfiguration' => array(
                    'CallsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array($carrierName2, $carrierOption2)
                            ),
                            'UserFilterCRYS1301' => SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                        ),
                    ),
                    'MeetingsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array($carrierName3, $carrierOption3),
                            ),
                            'UserFilterCRYS1301' => SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                        ),
                    ),
                    'UsersCRYS1301' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                            'CallFilterCRYS1301' => SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                            'UserFilterCRYS1301' => SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                        ),
                    ),
                    'BeanEmitter' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                            'CallFilterCRYS1301' => SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                            'UserFilterCRYS1301' => SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Should return user configuration.
     *
     * @dataProvider getUserConfigurationProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry::getUserConfiguration
     * @param array $idUser
     * @param array $beans
     * @param array $expectedConfiguration
     */
    public function testGetUserConfiguration($idUser, $beans, $expectedConfiguration)
    {
        /** @var NotificationCenterSubscriptionCRYS1301 $notificationBean */
        $notificationBean = \BeanFactory::newBean('NotificationCenterSubscriptions');
        $notificationBean->new_with_id = true;
        $notificationBean->id = 'query_id';

        $expectedQuery = new \SugarQuery();
        $expectedQuery->from($notificationBean);
        $expectedQuery->where()->equals('user_id', $idUser);

        $expectedFields = array(
            'type',
            'emitter_module_name',
            'event_name',
            'filter_name',
            'carrier_name',
            'carrier_option',
        );

        foreach ($beans as $beanData) {
            $bean = new \NotificationCenterSubscription();
            $bean->id = $beanData['id'];
            $bean->type = $beanData['type'];
            $bean->emitter_module_name = $beanData['emitter_module_name'];
            $bean->event_name = 'reminder';
            $bean->filter_name = $beanData['filter_name'];
            $bean->carrier_name = $beanData['carrier_name'];
            $bean->carrier_option = $beanData['carrier_option'];
            NotificationCenterSubscriptionCRYS1301::$beans[] = $bean;
        }
        $config = $this->subscriptionsRegistry->getUserConfiguration($idUser);
        $this->assertEquals($expectedConfiguration, $config);

        $this->assertEquals(
            array($expectedQuery, $expectedFields),
            NotificationCenterSubscriptionCRYS1301::$fetchParams
        );
        $this->assertInstanceOf('UserCRYS1301', BeanEventCRYS1301::$currentBean);
    }

    /**
     * Data provider for testGetUserConfigurationWrongBeanType.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionsRegistryTest::testGetUserConfigurationWrongBeanType
     * @return array
     */
    public static function getUserConfigurationWrongBeanTypeProvider()
    {
        return array(
            'beanWithoutEmitter' => array(
                'idUser' => Uuid::uuid1(),
                'beanData' => array(
                    'id' => Uuid::uuid1(),
                    'type' => 'type' . rand(1000, 1999),
                    'emitter_module_name' => 'MeetingCRYS1301',
                    'filter_name' => 'CallFilterCRYS1301',
                    'carrier_name' => 'carrier' . rand(1000, 1999),
                    'carrier_option' => 'option' . rand(1000, 1999),
                ),
            ),
        );
    }

    /**
     * Should throw exception if bean type not application or bean or module.
     *
     * @dataProvider getUserConfigurationWrongBeanTypeProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry::getUserConfiguration
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot create emitter for target bean
     * @param string $idUser
     * @param array $beanData
     */
    public function testGetUserConfigurationWrongBeanType($idUser, $beanData)
    {
        $bean = new \NotificationCenterSubscription();
        $bean->id = $beanData['id'];
        $bean->type = $beanData['type'];
        $bean->emitter_module_name = $beanData['emitter_module_name'];
        $bean->event_name = 'reminder';
        $bean->filter_name = $beanData['filter_name'];
        $bean->carrier_name = $beanData['carrier_name'];
        $bean->carrier_option = $beanData['carrier_option'];
        NotificationCenterSubscriptionCRYS1301::$beans[] = $bean;

        $this->subscriptionsRegistry->getUserConfiguration($idUser);
    }

    /**
     * Data provider for setGlobalConfiguration.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionsRegistryTest::testsetGlobalConfiguration
     * @return array
     */
    public static function setGlobalConfigurationProvider()
    {
        $callsCallFilterCarrierBean = array(
            'id' => Uuid::uuid1(),
            'type' => 'module',
            'emitter_module_name' => 'CallsCRYS1301',
            'filter_name' => 'CallFilterCRYS1301',
            'carrier_name' => 'Carrier' . rand(1000, 1999),
            'carrier_option' => '',
            'is_suitable' => true,
        );
        $meetingsMeetingFilterCarrierBean1 = array(
            'id' => Uuid::uuid1(),
            'type' => 'module',
            'emitter_module_name' => 'MeetingsCRYS1301',
            'filter_name' => 'MeetingFilterCRYS1301',
            'carrier_name' => 'Carrier' . rand(2000, 2999),
            'carrier_option' => '',
            'is_suitable' => true,
        );
        $meetingsMeetingFilterCarrierBean2 = array(
            'id' => Uuid::uuid1(),
            'type' => 'module',
            'emitter_module_name' => 'MeetingsCRYS1301',
            'filter_name' => 'MeetingFilterCRYS1301',
            'carrier_name' => 'Carrier' . rand(3000, 3999),
            'carrier_option' => '',
            'is_suitable' => true,
        );
        $meetingsNotSupportsFilterCarrierBean = array(
            'id' => Uuid::uuid1(),
            'type' => 'module',
            'emitter_module_name' => 'MeetingsCRYS1301',
            'filter_name' => 'NotSupportsFilterCRYS1301',
            'carrier_name' => 'Carrier' . rand(4000, 4999),
            'carrier_option' => '',
            'is_suitable' => true,
        );
        $usersUserFilterCarrierBean = array(
            'id' => Uuid::uuid1(),
            'type' => 'module',
            'emitter_module_name' => 'UsersCRYS1301',
            'filter_name' => 'UserFilterCRYS1301',
            'carrier_name' => 'Carrier' . rand(5000, 5999),
            'carrier_option' => '',
            'is_suitable' => true,
        );
        $emptyCarrierBean = array(
            'id' => Uuid::uuid1(),
            'type' => 'module',
            'emitter_module_name' => 'CallsCRYS1301',
            'filter_name' => 'CallFilterCRYS1301',
            'carrier_name' => '',
            'carrier_option' => '',
            'is_suitable' => true,
        );

        return array(
            'setConfigReturnsConfig' => array(
                'beans' => array(
                    $meetingsMeetingFilterCarrierBean1,
                    $meetingsMeetingFilterCarrierBean2,
                    $meetingsNotSupportsFilterCarrierBean,
                    $usersUserFilterCarrierBean,
                ),
                'carriers' => array(
                    $callsCallFilterCarrierBean['carrier_name'] => array(
                        'options' => '',
                        'status' => true,
                    ),
                    $meetingsMeetingFilterCarrierBean1['carrier_name'] => array(
                        'options' => '',
                        'status' => true,
                    ),
                    $meetingsMeetingFilterCarrierBean2['carrier_name'] => array(
                        'options' => '',
                        'status' => true,
                    ),
                    $meetingsNotSupportsFilterCarrierBean['carrier_name'] => array(
                        'options' => '',
                        'status' => false,
                    ),
                    $usersUserFilterCarrierBean['carrier_name'] => array(
                        'options' => '',
                        'status' => false,
                    ),
                ),
                'config' => array(
                    'CallsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array($callsCallFilterCarrierBean['carrier_name'], ''),
                                array($meetingsNotSupportsFilterCarrierBean['carrier_name'], ''),
                                array($usersUserFilterCarrierBean['carrier_name'], ''),
                            ),
                        ),
                    ),
                    'UsersCRYS1301' => array(
                        'reminder' => array(
                            'UserFilterCRYS1301' => SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                        ),
                    ),
                    'MeetingsCRYS1301' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => array($callsCallFilterCarrierBean['carrier_name'], ''),
                        ),
                    ),
                    'MeetingCRYS1301WithNoReminders' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => array($meetingsMeetingFilterCarrierBean1['carrier_name']),
                            'CallFilterCRYS1301' => array(
                                array($meetingsMeetingFilterCarrierBean2['carrier_name'], ''),
                            ),
                        ),
                    ),
                    'BeanEmitter' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                            'CallFilterCRYS1301' => SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                        ),
                    ),
                ),
                'expectedSaved' => array(
                    $callsCallFilterCarrierBean['carrier_name'] => array(
                        'carrier_name' => $callsCallFilterCarrierBean['carrier_name'],
                        'carrier_option' => '',
                        'type' => 'module',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'event_name' => 'reminder',
                        'filter_name' => 'CallFilterCRYS1301',
                    ),
                ),
                'expectedDeletedId' => array(
                    array($meetingsMeetingFilterCarrierBean1['id']),
                    array($meetingsMeetingFilterCarrierBean2['id']),
                ),
            ),
            'existingCarrierEventSelectionIsNotDeletedIfCarrierBecomesDisabled' => array(
                'beans' => array(
                    $meetingsMeetingFilterCarrierBean1,
                ),
                'carriers' => array(
                    $meetingsMeetingFilterCarrierBean1['carrier_name'] => array(
                        'options' => '',
                        'status' => false,
                    ),
                ),
                'config' => array(
                    'MeetingsCRYS1301' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => array(
                                array($meetingsMeetingFilterCarrierBean1['carrier_name'], ''),
                            ),
                        ),
                    ),
                ),
                'expectedSaved' => array(),
                'expectedDeletedId' => array(),
            ),
            'itIsNotPossibleToSaveCarrierEventForDisabledCarrierIfItWasNotSavedPreviously' => array(
                'beans' => array(),
                'carriers' => array(
                    $meetingsMeetingFilterCarrierBean1['carrier_name'] => array(
                        'options' => '',
                        'status' => false,
                    ),
                ),
                'config' => array(
                    'MeetingsCRYS1301' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => array(
                                array($meetingsMeetingFilterCarrierBean1['carrier_name'], ''),
                            ),
                        ),
                    ),
                ),
                'expectedSaved' => array(),
                'expectedDeletedId' => array(),
            ),
            'disabledEventConfigSavesDisabledEventMarkAndDoesNotAffectExistingCarriers' => array(
                'beans' => array(
                    $callsCallFilterCarrierBean,
                ),
                'carriers' => array(
                    $callsCallFilterCarrierBean['carrier_name'] => array(
                        'options' => '',
                        'status' => true,
                    ),
                ),
                'config' => array(
                    'CallsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array('', ''),
                                array($callsCallFilterCarrierBean['carrier_name'], ''),
                            ),
                        ),
                    ),
                ),
                'expectedSaved' => array(
                    '' => array(
                        'carrier_name' => '',
                        'carrier_option' => '',
                        'type' => 'module',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'event_name' => 'reminder',
                        'filter_name' => 'CallFilterCRYS1301',
                    ),
                ),
                'expectedDeletedId' => array(),
            ),
            'disabledEventConfigSavesDisabledEventMarkAlongsideOtherEnabledEventCarriers' => array(
                'beans' => array(),
                'carriers' => array(
                    $callsCallFilterCarrierBean['carrier_name'] => array(
                        'options' => '',
                        'status' => true,
                    ),
                ),
                'config' => array(
                    'CallsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array($callsCallFilterCarrierBean['carrier_name'], ''),
                                array('', ''),
                            ),
                        ),
                    ),
                ),
                'expectedSaved' => array(
                    $callsCallFilterCarrierBean['carrier_name'] => array(
                        'carrier_name' => $callsCallFilterCarrierBean['carrier_name'],
                        'carrier_option' => '',
                        'type' => 'module',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'event_name' => 'reminder',
                        'filter_name' => 'CallFilterCRYS1301',
                    ),
                    '' => array(
                        'carrier_name' => '',
                        'carrier_option' => '',
                        'type' => 'module',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'event_name' => 'reminder',
                        'filter_name' => 'CallFilterCRYS1301',
                    ),
                ),
                'expectedDeletedId' => array(),
            ),
            'enabledEventConfigDeletesDisabledEventMarkAndDoesNotAffectExistingCarriers' => array(
                'beans' => array(
                    $callsCallFilterCarrierBean,
                    $emptyCarrierBean,
                ),
                'carriers' => array(
                    $callsCallFilterCarrierBean['carrier_name'] => array(
                        'options' => '',
                        'status' => true,
                    ),
                ),
                'config' => array(
                    'CallsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array($callsCallFilterCarrierBean['carrier_name'], ''),
                            ),
                        ),
                    ),
                ),
                'expectedSaved' => array(),
                'expectedDeletedId' => array(
                    array($emptyCarrierBean['id']),
                ),
            ),
        );
    }

    /**
     * Should update global configuration.
     * Should save only available and enabled carriers and skip carriers which is not in the list.
     *
     * @dataProvider setGlobalConfigurationProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry::setGlobalConfiguration
     * @param array $beans Existing (previously saved) carrier beans.
     * @param array $carriers All carriers known to the system.
     * @param array $config Input configuration.
     * @param array $expectedSaved Information about carrier beans that expected to be saved.
     * @param array $expectedDeletedId Ids of carrier beans that are expected to be deleted.
     */
    public function testSetGlobalConfiguration($beans, $carriers, $config, $expectedSaved, $expectedDeletedId)
    {
        foreach ($beans as $beanData) {
            $bean = new NotificationCenterSubscriptionCRYS1301();
            $bean->id = $beanData['id'];
            $bean->type = $beanData['type'];
            $bean->emitter_module_name = $beanData['emitter_module_name'];
            $bean->event_name = 'reminder';
            $bean->filter_name = $beanData['filter_name'];
            $bean->carrier_name = $beanData['carrier_name'];
            $bean->carrier_option = $beanData['carrier_option'];
            $bean->isSuitable = $beanData['is_suitable'];
            NotificationCenterSubscriptionCRYS1301::$beans[] = $bean;
        }

        $carrierStatuses = array();
        foreach ($carriers as $name => $carrierInfo) {
            $carrierStatuses[] = array($name, $carrierInfo['status']);
        }
        $this->carrierStatus->method('getCarrierStatus')->willReturnMap($carrierStatuses);
        $this->carrierRegistry->method('getCarriers')->willReturn(array_keys($carriers));
        $this->subscriptionsRegistry->method('getSingleDeliveryCarriers')->willReturn(array());

        $this->subscriptionsRegistry->setGlobalConfiguration($config);
        foreach ($expectedSaved as $name => $expectedData) {
            $this->assertArrayHasKey($name, NotificationCenterSubscriptionCRYS1301::$savedArrayFields);
            $this->assertArraySubset($expectedData, NotificationCenterSubscriptionCRYS1301::$savedArrayFields[$name]);
        }
        $this->assertEquals($expectedDeletedId, NotificationCenterSubscriptionCRYS1301::$markDeleted);
    }


    /**
     * Data provider for setUserConfiguration.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionsRegistryTest::testSetUserConfiguration
     * @return array
     */
    public static function setUserConfigurationProvider()
    {
        $carrierName1 = 'Carrier' . rand(1000, 1999);
        $carrierName2 = 'Carrier' . rand(2000, 2999);
        $carrierName3 = 'Carrier' . rand(3000, 3999);
        $carrierName4 = 'Carrier' . rand(4000, 4999);
        $carrierName5 = 'Carrier' . rand(5000, 5999);
        $carrierName6 = 'Carrier' . rand(6000, 6999);


        $carrierOption1 = array(rand(1000, 1999));
        $carrierOption2 = array(rand(2000, 2999));
        $carrierOption3 = array(rand(3000, 3999));
        $carrierOption4 = array(rand(4000, 4999));
        $carrierOption5 = array(rand(5000, 5999));
        $carrierOption6 = array(rand(6000, 6999));

        $beans = array(
            array(
                'id' => Uuid::uuid1(),
                'type' => 'module',
                'emitter_module_name' => 'CallsCRYS1301',
                'filter_name' => 'CallFilterCRYS1301',
                'carrier_name' => $carrierName1,
                'carrier_option' => $carrierOption1,
                'is_suitable' => true,
            ),
            array(
                'id' => Uuid::uuid1(),
                'type' => 'module',
                'emitter_module_name' => 'CallsCRYS1301',
                'filter_name' => 'CallFilterCRYS1301',
                'carrier_name' => $carrierName2,
                'carrier_option' => $carrierOption2,
                'is_suitable' => false,
            ),
            array(
                'id' => Uuid::uuid1(),
                'type' => 'module',
                'emitter_module_name' => 'CallsCRYS1301',
                'filter_name' => 'CallFilterCRYS1301',
                'carrier_name' => $carrierName1,
                'carrier_option' => $carrierOption1,
                'is_suitable' => true,
            ),
            array(
                'id' => Uuid::uuid1(),
                'type' => 'module',
                'emitter_module_name' => 'MeetingsCRYS1301',
                'filter_name' => 'MeetingFilterCRYS1301',
                'carrier_name' => $carrierName2,
                'carrier_option' => $carrierOption2,
                'is_suitable' => true,
            ),
            array(
                'id' => Uuid::uuid1(),
                'type' => 'module',
                'emitter_module_name' => 'MeetingCRYS1301',
                'filter_name' => 'MeetingFilterCRYS1301',
                'carrier_name' => $carrierName3,
                'carrier_option' => $carrierOption3,
                'is_suitable' => true,
            ),
            array(
                'id' => Uuid::uuid1(),
                'type' => 'module',
                'emitter_module_name' => 'MeetingsCRYS1301',
                'filter_name' => 'NotSupportsFilterCRYS1301',
                'carrier_name' => $carrierName4,
                'carrier_option' => $carrierOption4,
                'is_suitable' => true,
            ),
            array(
                'id' => Uuid::uuid1(),
                'type' => 'module',
                'emitter_module_name' => 'UsersCRYS1301',
                'filter_name' => 'UserFilterCRYS1301',
                'carrier_name' => $carrierName4,
                'carrier_option' => $carrierOption4,
                'is_suitable' => true,
            ),
        );

        $idUser = Uuid::uuid1();
        return array(
            'setConfigReturnsConfig' => array(
                'idUser' => $idUser,
                'beans' => $beans,
                'carriers' => array(
                    $carrierName1 => array(
                        'options' => $carrierOption1,
                        'status' => true,
                    ),
                    $carrierName2 => array(
                        'options' => $carrierOption2,
                        'status' => true,
                    ),
                    $carrierName3 => array(
                        'options' => $carrierOption3,
                        'status' => true,
                    ),
                    $carrierName4 => array(
                        'options' => $carrierOption4,
                        'status' => true,
                    ),
                    $carrierName5 => array(
                        'options' => $carrierOption5,
                        'status' => true,
                    ),
                    $carrierName6 => array(
                        'options' => $carrierOption6,
                        'status' => false,
                    ),
                ),
                'singleDeliveryCarriers' => array(),
                'config' => array(
                    'CallsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array($carrierName1, $carrierOption1),
                                array($carrierName4, $carrierOption4),
                                array($carrierName5),
                                array($carrierName6, $carrierOption6),
                            ),
                        ),
                    ),
                    'UsersCRYS1301' => array(
                        'reminder' => array(
                            'UserFilterCRYS1301' => array(
                                array($carrierName2),
                            ),
                        ),
                    ),
                    'MeetingsCRYS1301' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => array(
                                SubscriptionsRegistry::CARRIER_VALUE_DEFAULT,
                            ),
                        ),
                    ),
                    'MeetingCRYS1301WithNoReminders' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => array($carrierName2),
                            'CallFilterCRYS1301' => array(
                                array($carrierName3, $carrierOption3),
                            ),
                        ),
                    ),
                    'BeanEmitter' => array(
                        'reminder' => array(
                            'MeetingFilterCRYS1301' => array(SubscriptionsRegistry::CARRIER_VALUE_DEFAULT),
                            'CallFilterCRYS1301' => array(SubscriptionsRegistry::CARRIER_VALUE_DEFAULT),
                            'UserFilterCRYS1301' => array($carrierName3, $carrierOption3),
                        ),
                    ),
                ),
                'expectedSaved' => array(
                    $carrierName4 => array(
                        'carrier_name' => $carrierName4,
                        'carrier_option' => $carrierOption4,
                    ),
                    $carrierName5 => array(
                        'type' => 'module',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'event_name' => 'reminder',
                        'filter_name' => 'CallFilterCRYS1301',
                        'user_id' => $idUser,
                        'carrier_name' => $carrierName5,
                        'carrier_option' => '',
                    ),
                    $carrierName2 => array(
                        'carrier_name' => $carrierName2,
                        'carrier_option' => '',
                    ),
                ),
                'expectedDeletedId' => array(
                    array($beans[1]['id']),
                    array($beans[3]['id']),
                    array($beans[4]['id']),
                    array($beans[5]['id']),
                ),
            ),
            'existingCarrierEventSelectionIsNotDeletedIfCarrierBecomesDisabled' => array(
                'idUser' => $idUser,
                'beans' => array(
                    $beans[0],
                ),
                'carriers' => array(
                    $carrierName1 => array(
                        'options' => $carrierOption1,
                        'status' => false,
                    ),
                ),
                'singleDeliveryCarriers' => array(),
                'config' => array(
                    'CallsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array($carrierName1, $carrierOption1),
                            ),
                        ),
                    ),
                ),
                'expectedSaved' => array(),
                'expectedDeletedId' => array(),
            ),
            'itIsNotPossibleToSaveCarrierEventForDisabledCarrierIfItWasNotSavedPreviously' => array(
                'idUser' => $idUser,
                'beans' => array(),
                'carriers' => array(
                    $carrierName1 => array(
                        'options' => $carrierOption1,
                        'status' => false,
                    ),
                ),
                'singleDeliveryCarriers' => array(),
                'config' => array(
                    'CallsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array($carrierName1, $carrierOption1),
                            ),
                        ),
                    ),
                ),
                'expectedSaved' => array(),
                'expectedDeletedId' => array(),
            ),
            'disabledEventConfigSavesDisabledEventMarkAndDoesNotAffectExistingCarriers' => array(
                'idUser' => $idUser,
                'beans' => array(
                    $beans[0],
                ),
                'carriers' => array(
                    $carrierName1 => array(
                        'options' => $carrierOption1,
                        'status' => true,
                    ),
                ),
                'singleDeliveryCarriers' => array(),
                'config' => array(
                    'CallsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array('', ''),
                                array($carrierName1, $carrierOption1),
                            ),
                        ),
                    ),
                ),
                'expectedSaved' => array(
                    '' => array(
                        'carrier_name' => '',
                        'carrier_option' => '',
                        'type' => 'module',
                        'emitter_module_name' => 'CallsCRYS1301',
                        'event_name' => 'reminder',
                        'filter_name' => 'CallFilterCRYS1301',
                        'user_id' => $idUser,
                    ),
                ),
                'expectedDeletedId' => array(),
            ),
            'shouldNotSaveSecondOptionForCarrierOfSingleDeliveryBehavior' => array(
                'idUser' => $idUser,
                'beans' => array(),
                'carriers' => array(
                    $carrierName1 => array(
                        'options' => $carrierOption1,
                        'status' => true,
                    ),
                    $carrierName2 => array(
                        'options' => $carrierOption2,
                        'status' => true,
                    ),
                ),
                'singleDeliveryCarriers' => array(
                    $carrierName1,
                ),
                'config' => array(
                    'CallsCRYS1301' => array(
                        'reminder' => array(
                            'CallFilterCRYS1301' => array(
                                array($carrierName1, $carrierOption1),
                                array($carrierName1, 'secondOption'),
                                array($carrierName2, $carrierOption2),
                            ),
                        ),
                    ),
                ),
                'expectedSaved' => array(
                    $carrierName1 => array(
                        'carrier_name' => $carrierName1,
                        'carrier_option' => $carrierOption1,
                    ),
                    $carrierName2 => array(
                        'carrier_name' => $carrierName2,
                        'carrier_option' => $carrierOption2,
                    ),
                ),
                'expectedDeletedId' => array(),
            ),
        );
    }

    /**
     * Should save user's configuration and save only enabled carriers.
     *
     * @dataProvider setUserConfigurationProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry::setUserConfiguration
     * @param string $idUser Id of a User that saves configuration.
     * @param array $beans Existing (previously saved) carrier beans.
     * @param array $carriers All carriers known to the system.
     * @param array $singleDeliveryCarriers All carriers with single delivery behavior.
     * @param array $config User's input configuration.
     * @param array $expectedSaved Information about carrier beans that expected to be saved.
     * @param array $expectedDeletedId Ids of carrier beans that are expected to be deleted.
     */
    public function testSetUserConfiguration(
        $idUser,
        $beans,
        $carriers,
        $singleDeliveryCarriers,
        $config,
        $expectedSaved,
        $expectedDeletedId
    ) {
        foreach ($beans as $beanData) {
            $bean = new NotificationCenterSubscriptionCRYS1301();
            $bean->id = $beanData['id'];
            $bean->type = $beanData['type'];
            $bean->emitter_module_name = $beanData['emitter_module_name'];
            $bean->event_name = 'reminder';
            $bean->filter_name = $beanData['filter_name'];
            $bean->carrier_name = $beanData['carrier_name'];
            $bean->carrier_option = $beanData['carrier_option'];
            $bean->isSuitable = $beanData['is_suitable'];
            NotificationCenterSubscriptionCRYS1301::$beans[] = $bean;
        }

        $carrierStatuses = array();
        foreach ($carriers as $name => $carrierInfo) {
            $carrierStatuses[] = array($name, $carrierInfo['status']);
        }
        $this->carrierStatus->method('getCarrierStatus')->willReturnMap($carrierStatuses);
        $this->carrierRegistry->method('getCarriers')->willReturn(array_keys($carriers));
        $this->subscriptionsRegistry->method('getSingleDeliveryCarriers')->willReturn($singleDeliveryCarriers);

        $this->subscriptionsRegistry->setUserConfiguration($idUser, $config);

        foreach ($expectedSaved as $name => $expectedData) {
            $this->assertArrayHasKey($name, NotificationCenterSubscriptionCRYS1301::$savedArrayFields);
            $this->assertArraySubset($expectedData, NotificationCenterSubscriptionCRYS1301::$savedArrayFields[$name]);
        }
        $this->assertEquals($expectedDeletedId, NotificationCenterSubscriptionCRYS1301::$markDeleted);
    }

    /**
     * Data provider for testGetUsers.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionsRegistryTest::testGetUsers
     * @return array
     */
    public static function getUsersProvider()
    {
        $carrierName1 = 'Carrier' . rand(1000, 1999);
        $carrierName2 = 'Carrier' . rand(2000, 2999);
        $carrierName3 = 'Carrier' . rand(3000, 3999);
        $carrierName4 = 'Carrier' . rand(4000, 4999);


        $carrierOption1 = rand(1000, 1999);
        $carrierOption2 = rand(2000, 2999);
        $carrierOption3 = rand(3000, 3999);
        $carrierOption4 = rand(4000, 4999);


        $users = array(
            array(
                'user_id' => Uuid::uuid1(),
                'carrier_name' => $carrierName1,
                'type' => 'bean',
                'carrier_option' => $carrierOption1,
            ),
            array(
                'user_id' => Uuid::uuid1(),
                'carrier_name' => $carrierName2,
                'type' => 'main',
                'carrier_option' => $carrierOption2,
            ),
            array(
                'user_id' => Uuid::uuid1(),
                'carrier_name' => $carrierName3,
                'type' => 'bean' . rand(1000, 1999),
                'carrier_option' => $carrierOption3,
            ),
            array(
                'user_id' => Uuid::uuid1(),
                'carrier_name' => $carrierName4,
                'type' => 'bean',
                'carrier_option' => $carrierOption4,
            ),
            array(
                'user_id' => Uuid::uuid1(),
                'carrier_name' => '',
                'type' => '',
                'carrier_option' => '',
            ),
        );

        $globalConfigMain = array(
            'id' => Uuid::uuid1(),
            'type' => 'main',
            'emitter_module_name' => 'CallCRYS1301',
            'filter_name' => 'CallFilterCRYS1301',
            'carrier_name' => 'CarrierMain' . rand(1000, 1999),
            'carrier_option' => 'OptionMain' . rand(1000, 1999),
        );

        $globalConfigMainEmptyCarrier = array(
            'id' => Uuid::uuid1(),
            'type' => 'main',
            'emitter_module_name' => 'CallCRYS1301',
            'filter_name' => 'CallFilterCRYS1301',
            'carrier_name' => '',
            'carrier_option' => 'OptionMain' . rand(1000, 1999),
        );

        $globalConfigBean = array(
            'id' => Uuid::uuid1(),
            'type' => 'bean',
            'emitter_module_name' => 'CallCRYS1301',
            'filter_name' => 'CallFilterCRYS1301',
            'carrier_name' => 'CarrierBean' . rand(1000, 1999),
            'carrier_option' => 'OptionBean' . rand(1000, 1999),
        );

        return array(
            'ApplicationEventUseMainGlobal' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event',
                'executedGlobalResults' => array($globalConfigMain, $globalConfigBean),
                'executedUsersResults' => array(
                    array($users[0], $users[1], $users[2], $users[4])
                ),
                'expectedConfig' => array(
                    $users[0]['user_id'] => array (
                            'filter' => 'CallFilterCRYS1301',
                            'config' => array (
                                array($carrierName1, $carrierOption1),
                            ),
                        ),
                    $users[1]['user_id'] => array(
                            'filter' => 'CallFilterCRYS1301',
                            'config' => array(
                                array($carrierName2, $carrierOption2),
                            ),
                        ),
                    $users[2]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName3, $carrierOption3),
                        ),
                    ),
                    $users[4]['user_id'] => array (
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array (
                            array ($globalConfigMain['carrier_name'], $globalConfigMain['carrier_option']),
                        ),
                    ),
                ),
                'expectedGlobalQuery' => '/^SELECT FROM notification_subscription$/',
                'expectedUserQuery' => array(
                    '/(.+)' . preg_quote(
                        sprintf(
                            '%s %s %s %s %s',
                            "LEFT JOIN notification_subscription ON (notification_subscription.user_id = AND",
                            "notification_subscription.deleted = 0 AND notification_subscription.event_name = ''",
                            "AND notification_subscription.type = 'application'",
                            "AND notification_subscription.emitter_module_name = ''",
                            "AND notification_subscription.filter_name = 'CallFilterCRYS1301'"
                        )
                    ) . '/'
                ),
            ),
            'ApplicationEventUseEmptyMainGlobal' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event',
                'executedGlobalResults' => array($globalConfigMainEmptyCarrier, $globalConfigBean),
                'executedUsersResults' => array(
                    array($users[0], $users[1], $users[2], $users[4])
                ),
                'expectedConfig' => array(
                    $users[2]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName3, $carrierOption3),
                        ),
                    ),
                    $users[1]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName2, $carrierOption2),
                        ),
                    ),
                    $users[0]['user_id'] => array (
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array (
                            array($carrierName1, $carrierOption1),
                        ),
                    ),
                ),
                'expectedGlobalQuery' => '/^SELECT FROM notification_subscription$/',
                'expectedUserQuery' => array(
                    '/(.+)' . preg_quote(
                        sprintf(
                            '%s %s %s %s %s',
                            "LEFT JOIN notification_subscription ON (notification_subscription.user_id = AND",
                            "notification_subscription.deleted = 0 AND notification_subscription.event_name = ''",
                            "AND notification_subscription.type = 'application'",
                            "AND notification_subscription.emitter_module_name = ''",
                            "AND notification_subscription.filter_name = 'CallFilterCRYS1301'"
                        )
                    ) . '/',
                ),
            ),
            'ApplicationEventUseBeanGlobal' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event',
                'executedGlobalResults' => array($globalConfigBean),
                'executedUsersResults' => array(
                    array($users[0], $users[1], $users[2], $users[4])
                ),
                'expectedConfig' => array(
                    $users[0]['user_id'] => array (
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array (
                            array ($carrierName1, $carrierOption1),
                        ),
                    ),
                    $users[1]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName2, $carrierOption2),
                        ),
                    ),
                    $users[2]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName3, $carrierOption3),
                        ),
                    ),
                    $users[4]['user_id'] => array (
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array (
                            array ($globalConfigBean['carrier_name'], $globalConfigBean['carrier_option']),
                        ),
                    ),
                ),
                'expectedGlobalQuery' => '/^SELECT FROM notification_subscription$/',
                'expectedUserQuery' => array(
                    '/(.+)' . preg_quote(
                        sprintf(
                            '%s %s %s %s %s',
                            "LEFT JOIN notification_subscription ON (notification_subscription.user_id = AND",
                            "notification_subscription.deleted = 0 AND notification_subscription.event_name = ''",
                            "AND notification_subscription.type = 'application'",
                            "AND notification_subscription.emitter_module_name = ''",
                            "AND notification_subscription.filter_name = 'CallFilterCRYS1301'"
                        )
                    ) . '/',
                ),
            ),
            'BeanEventNotUseGlobal' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event',
                'executedGlobalResults' => array(),
                'executedUsersResults' => array(
                    array($users[0], $users[1], $users[2]),
                    array($users[2], $users[3]),
                ),
                'expectedConfig' => array (
                    $users[0]['user_id'] => array (
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array (
                            array ($carrierName1, $carrierOption1),
                        ),
                    ),
                    $users[1]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName2, $carrierOption2),
                        ),
                    ),
                    $users[2]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName3, $carrierOption3),
                        ),
                    ),
                    $users[3]['user_id'] => array(
                        'filter' => 'MeetingFilterCRYS1301',
                        'config' => array(
                            array($carrierName4, $carrierOption4),
                        ),
                    ),
                ),
                'expectedGlobalQuery' => '/SELECT FROM notification_subscription/',
                'expectedUserQuery' => array(
                    '/(.+)' . preg_quote(
                        sprintf(
                            "%s %s %s %s %s %s %s",
                            "LEFT JOIN notification_subscription ON (notification_subscription.user_id = AND",
                            "notification_subscription.deleted = 0 AND notification_subscription.event_name = ''",
                            "AND ((notification_subscription.type = 'bean'",
                            "AND notification_subscription.emitter_module_name = '')",
                            "OR (notification_subscription.type = 'module'",
                            "AND notification_subscription.emitter_module_name = ''))",
                            "AND notification_subscription.filter_name = 'CallFilterCRYS1301')"
                        )
                    ) . '/',
                    '/(.+)' . preg_quote(
                        sprintf(
                            "%s %s %s %s %s %s %s",
                            "LEFT JOIN notification_subscription ON (notification_subscription.user_id = AND",
                            "notification_subscription.deleted = 0 AND notification_subscription.event_name = ''",
                            "AND ((notification_subscription.type = 'bean'",
                            "AND notification_subscription.emitter_module_name = '')",
                            "OR (notification_subscription.type = 'module'",
                            "AND notification_subscription.emitter_module_name = ''))",
                            "AND notification_subscription.filter_name = 'MeetingFilterCRYS1301')"
                        )
                    ) . '/',

                ),
            ),
            'BeanEventUseMainGlobal' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event',
                'executedGlobalResults' => array($globalConfigMain, $globalConfigBean),
                'executedUsersResults' => array(
                    array($users[0], $users[1], $users[2], $users[4]),
                    array($users[2], $users[3], $users[4]),
                ),
                'expectedConfig' => array (
                    $users[0]['user_id'] => array (
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array (
                            array ($carrierName1, $carrierOption1),
                        ),
                    ),
                    $users[1]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName2, $carrierOption2),
                        ),
                    ),
                    $users[2]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName3, $carrierOption3),
                        ),
                    ),
                    $users[3]['user_id'] => array(
                        'filter' => 'MeetingFilterCRYS1301',
                        'config' => array(
                            array($carrierName4, $carrierOption4),
                        ),
                    ),
                    $users[4]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array (
                            array ($globalConfigMain['carrier_name'], $globalConfigMain['carrier_option']),
                        ),
                    ),
                ),
                'expectedGlobalQuery' => '/SELECT FROM notification_subscription/',
                'expectedUserQuery' => array(
                    '/(.+)' . preg_quote(
                        sprintf(
                            '%s %s %s %s %s %s %s',
                            "LEFT JOIN notification_subscription ON (notification_subscription.user_id = AND",
                            "notification_subscription.deleted = 0 AND notification_subscription.event_name = ''",
                            "AND ((notification_subscription.type = 'bean'",
                            "AND notification_subscription.emitter_module_name = '')",
                            "OR (notification_subscription.type = 'module'",
                            "AND notification_subscription.emitter_module_name = ''))",
                            "AND notification_subscription.filter_name = 'CallFilterCRYS1301')"
                        )
                    ) . '/',
                    '/(.+)' . preg_quote(
                        sprintf(
                            '%s %s %s %s %s %s %s',
                            "LEFT JOIN notification_subscription ON (notification_subscription.user_id = AND",
                            "notification_subscription.deleted = 0 AND notification_subscription.event_name = ''",
                            "AND ((notification_subscription.type = 'bean'",
                            "AND notification_subscription.emitter_module_name = '')",
                            "OR (notification_subscription.type = 'module'",
                            "AND notification_subscription.emitter_module_name = ''))",
                            "AND notification_subscription.filter_name = 'MeetingFilterCRYS1301')"
                        )
                    ) . '/',
                ),
            ),
            'BeanEventUseBeanGlobal' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event',
                'executedGlobalResults' => array($globalConfigBean),
                'executedUsersResults' => array(
                    array($users[0], $users[1], $users[2], $users[4]),
                    array($users[2], $users[3], $users[4]),
                ),
                'expectedConfig' => array (
                    $users[0]['user_id'] => array (
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array (
                            array ($carrierName1, $carrierOption1),
                        ),
                    ),
                    $users[1]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName2, $carrierOption2),
                        ),
                    ),
                    $users[2]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName3, $carrierOption3),
                        ),
                    ),
                    $users[3]['user_id'] => array(
                        'filter' => 'MeetingFilterCRYS1301',
                        'config' => array(
                            array($carrierName4, $carrierOption4),
                        ),
                    ),
                    $users[4]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array (
                            array ($globalConfigBean['carrier_name'], $globalConfigBean['carrier_option']),
                        ),
                    ),
                ),
                'expectedGlobalQuery' => '/SELECT FROM notification_subscription/',
                'expectedUserQuery' => array(
                    '/(.+)' . preg_quote(
                        sprintf(
                            '%s %s %s %s %s %s %s',
                            "LEFT JOIN notification_subscription ON (notification_subscription.user_id = AND",
                            "notification_subscription.deleted = 0 AND notification_subscription.event_name = ''",
                            "AND ((notification_subscription.type = 'bean'",
                            "AND notification_subscription.emitter_module_name = '')",
                            "OR (notification_subscription.type = 'module'",
                            "AND notification_subscription.emitter_module_name = ''))",
                            "AND notification_subscription.filter_name = 'CallFilterCRYS1301')"
                        )
                    ) . '/',
                    '/(.+)' . preg_quote(
                        sprintf(
                            '%s %s %s %s %s %s %s',
                            "LEFT JOIN notification_subscription ON (notification_subscription.user_id = AND",
                            "notification_subscription.deleted = 0 AND notification_subscription.event_name = ''",
                            "AND ((notification_subscription.type = 'bean'",
                            "AND notification_subscription.emitter_module_name = '')",
                            "OR (notification_subscription.type = 'module'",
                            "AND notification_subscription.emitter_module_name = ''))",
                            "AND notification_subscription.filter_name = 'MeetingFilterCRYS1301')"
                        )
                    ) . '/',
                ),
            ),
            'eventInterfaceUseMainGlobal' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\ModuleEventInterface',
                'executedGlobalResults' => array($globalConfigMain),
                'executedUsersResults' => array(
                    array($users[0], $users[1], $users[2], $users[4]),
                    array($users[2], $users[3]),
                ),
                'expectedConfig' => array (
                    $users[0]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName1, $carrierOption1),
                        ),
                    ),
                    $users[1]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName2, $carrierOption2),
                        ),
                    ),
                    $users[2]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName3, $carrierOption3),
                        ),
                    ),
                    $users[4]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array ($globalConfigMain['carrier_name'], $globalConfigMain['carrier_option']),
                        ),
                    ),
                ),
                'expectedGlobalQuery' => '/SELECT FROM notification_subscription/',
                'expectedUserQuery' => array(
                    '/(.+)' . preg_quote(
                        sprintf(
                            "%s %s %s %s %s",
                            "LEFT JOIN notification_subscription ON (notification_subscription.user_id = AND",
                            "notification_subscription.deleted = 0 AND notification_subscription.event_name = ''",
                            "AND notification_subscription.type = 'module'",
                            "AND notification_subscription.emitter_module_name = ''",
                            "AND notification_subscription.filter_name = 'CallFilterCRYS1301')"
                        )
                    ) . '/',
                ),
            ),
            'eventInterfaceUseBeanGlobal' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\ModuleEventInterface',
                'executedGlobalResults' => array($globalConfigBean),
                'executedUsersResults' => array(
                    array($users[0], $users[1], $users[2], $users[4]),
                    array($users[2], $users[3]),
                ),
                'expectedConfig' => array (
                    $users[0]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName1, $carrierOption1),
                        ),
                    ),
                    $users[1]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName2, $carrierOption2),
                        ),
                    ),
                    $users[2]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array($carrierName3, $carrierOption3),
                        ),
                    ),
                    $users[4]['user_id'] => array(
                        'filter' => 'CallFilterCRYS1301',
                        'config' => array(
                            array ($globalConfigBean['carrier_name'], $globalConfigBean['carrier_option']),
                        ),
                    ),
                ),

                'expectedGlobalQuery' => '/SELECT FROM notification_subscription/',
                'expectedUserQuery' => array(
                    '/(.+)' . preg_quote(
                        sprintf(
                            "%s %s %s %s %s",
                            "LEFT JOIN notification_subscription ON (notification_subscription.user_id = AND",
                            "notification_subscription.deleted = 0 AND notification_subscription.event_name = ''",
                            "AND notification_subscription.type = 'module'",
                            "AND notification_subscription.emitter_module_name = ''",
                            "AND notification_subscription.filter_name = 'CallFilterCRYS1301')"
                        )
                    ) . '/',
                ),
            ),
        );
    }

    /**
     * Should return configuration of suitable users for provided event.
     *
     * @dataProvider getUsersProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry::getUsers
     * @param string $eventClass
     * @param array $executedGlobalResults
     * @param array $executedUsersResults
     * @param array $expectedConfig
     * @param array[] $expectedGlobalQuery
     * @param array[] $expectedUserQuery
     */
    public function testGetUsers(
        $eventClass,
        $executedGlobalResults,
        $executedUsersResults,
        $expectedConfig,
        $expectedGlobalQuery,
        $expectedUserQuery
    ) {
        /** @var ApplicationEvent|BeanEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMock($eventClass, array(), array(), '', false);

        $this->db->addQuerySpy(
            'get_global_config',
            "{$expectedGlobalQuery}",
            $executedGlobalResults
        );
        foreach ($expectedUserQuery as $i => $query) {
            $this->db->addQuerySpy(
                "get_user_config{$i}",
                "{$query}",
                $executedUsersResults[$i]
            );
        }
        $config = $this->subscriptionsRegistry->getUsers($event);
        $this->assertEquals($expectedConfig, $config);
        $this->assertEquals(1, $this->db->getQuerySpyRunCount('get_global_config'));
        foreach ($expectedUserQuery as $i => $queryRegexp) {
            $this->assertEquals(1, $this->db->getQuerySpyRunCount("get_user_config{$i}"));
        }
    }

    /**
     * Data provider for testDecodeEmitter.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionsRegistryTest::testDecodeEmitter
     * @return array
     */
    public static function decodeEmitterProvider()
    {
        return array(
            'ApplicationEmitter' => array(
                'emitterName' => 'ApplicationEmitter',
                'expectedResult' => array(
                    'type' => 'application',
                    'emitter_module_name' => null,
                ),
            ),
            'BeanEmitter' => array(
                'emitterName' => 'BeanEmitter',
                'expectedResult' => array(
                    'type' => 'bean',
                    'emitter_module_name' => null,
                ),
            ),
            'CallEmitter' => array(
                'emitterName' => 'CallEmitter',
                'expectedResult' => array(
                    'type' => 'module',
                    'emitter_module_name' => 'CallEmitter',
                ),
            ),
        );
    }

    /**
     * Should set type application when emitter name is ApplicationEmitter.
     * Should set type bean when emitter name is BeanEmitter.
     * Should set type module when emitter name is not BeanEmitter and not ApplicationEmitter.
     *
     * @dataProvider decodeEmitterProvider
     * @param string $emitterName
     * @param array $expectedResult
     */
    public function testDecodeEmitter($emitterName, $expectedResult)
    {
        $decodedEmitter = \SugarTestReflection::callProtectedMethod(
            $this->subscriptionsRegistry,
            'decodeEmitter',
            array($emitterName)
        );
        $this->assertEquals($expectedResult, $decodedEmitter);
    }

    /**
     * Generate emitter for given module.
     *
     * @param string $beanName
     * @param string $emitterFolder
     */
    protected static function saveEmitter($beanName, $emitterFolder)
    {
        $className = $beanName . 'Emitter';
        $classCode = "<?php
/**
 * Class Emitter
 */
class {$className} implements Sugarcrm\\Sugarcrm\\Notification\\EmitterInterface
{
    /** @var Emitter */
    public \$parent = null;

    /**
     * {@inheritdoc}
     */
    /**
     * @param ReminderEmitter|null \$reminderEmitter
     */
    public function __construct(ReminderEmitter \$reminderEmitter = null)
    {
        if (is_null(\$reminderEmitter)) {
            \$class = \\SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Reminder\\Emitter');
            \$reminderEmitter = new \$class();
        }
        \$this->reminderEmitter = \$reminderEmitter;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return '{$emitterFolder}';
    }

    /**
     * {@inheritdoc}
     */
    public function getEventPrototypeByString(\$eventString)
    {
        return \$this->reminderEmitter->getEventPrototypeByString(\$eventString);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventStrings()
    {
        return \$this->reminderEmitter->getEventStrings();
    }
}
";
        sugar_file_put_contents("modules/{$emitterFolder}/Emitter.php", $classCode);
    }

    /**
     * Generate bean emitter.
     *
     * @param string $beanName
     * @param string $emitterFolder
     */
    protected static function saveBeanEmitter($beanName, $emitterFolder)
    {
        $classCode = "<?php
use Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\Emitter as BeanEmitter;

/**
 * Class Emitter
 */
class {$beanName}Emitter implements Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\BeanEmitterInterface
{
    /** @var BeanEmitter */
    protected \$beanEmitter;

    /**
     * @param BeanEmitter|null \$beanEmitter
     */
    public function __construct(BeanEmitter \$beanEmitter = null)
    {
        if (is_null(\$beanEmitter)) {
            \$class = \\SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\Emitter');
            \$beanEmitter = new \$class();
        }
        \$this->beanEmitter = \$beanEmitter;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return '{$emitterFolder}';
    }

    /**
     * {@inheritdoc}
     */
    public function getEventPrototypeByString(\$eventString)
    {
        return new Sugarcrm\\SugarcrmTests\\Notification\\BeanEventCRYS1301(\$eventString);
    }

    /**
     * @return array
     */
    public function getEventStrings()
    {
        return array('reminder');
    }

    /**
     * {@inheritdoc}
     */
    public function exec(\\SugarBean \$bean, \$event, \$arguments)
    {
    }
}
";
        sugar_file_put_contents("modules/{$emitterFolder}/Emitter.php", $classCode);
    }

    /**
     * @param string $module
     * @param string $beanName
     * @param string $extendsFrom
     */
    public static function saveBean($module, $beanName, $extendsFrom)
    {
        $classCode = "<?php

        class {$beanName} extends {$extendsFrom}
        {

        }
        ";
        sugar_file_put_contents("modules/{$module}/{$beanName}.php", $classCode);
        require_once "modules/{$module}/{$beanName}.php";
    }
}

/**
 * Class NotificationCenterSubscriptionCRYS1301
 */
class NotificationCenterSubscriptionCRYS1301 extends \NotificationCenterSubscription
{
    /** @var bool */
    public $isSuitable = true;

    /** @var array */
    public static $beans = array();

    /** @var array */
    public static $fetchParams = array();

    /** @var array */
    public static $savedArrayFields = array();

    /** @var array */
    public static $markDeleted = array();

    public $field_defs = array();

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->added_custom_field_defs = false;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchFromQuery()
    {
        static::$fetchParams = func_get_args();
        static::$fetchParams[0]->from->id = 'query_id';
        return static::$beans;
    }

    /**
     * {@inheritdoc}
     */
    public function mark_deleted()
    {
        static::$markDeleted[] = func_get_args();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        static::$savedArrayFields[$this->carrier_name] = get_object_vars($this);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        static::$fetchParams = func_get_args();
        static::$fetchParams[0]->from->id = 'query_id';
        return static::$beans;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldValue($field)
    {
        if (!$this->isSuitable && $field == 'type') {
            return $this->type . rand(1000, 1999);
        }
        return parent::getFieldValue($field);
    }
}

/**
 * Class BeanEventCRYS1301
 * @package Sugarcrm\SugarcrmTests\Notification
 */
class BeanEventCRYS1301 extends BeanEvent
{
    /** @var \SugarBean */
    public static $currentBean = null;

    /**
     * @param \SugarBean $bean
     * @return BeanEvent
     */
    public function setBean($bean)
    {
        static::$currentBean = $bean;
        return parent::setBean($bean);
    }
}
