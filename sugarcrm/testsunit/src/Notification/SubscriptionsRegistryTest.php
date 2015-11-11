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

namespace Sugarcrm\SugarcrmTestsUnit\Notification;

use Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry;
use Sugarcrm\Sugarcrm\Notification\ApplicationEmitter\Event as ApplicationEvent;

require_once 'tests/SugarTestReflection.php';
require_once 'modules/Users/User.php';

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry
 */
class SubscriptionsRegistryTest extends \PHPUnit_Framework_TestCase
{
    const NS_SUBSCRIPTIONS_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\SubscriptionsRegistry';

    const NS_EMITTER_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\EmitterRegistry';

    const NS_SF_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\SubscriptionFilter\\SubscriptionFilterRegistry';

    const NS_SF_ASSIGNED_TO_ME = 'Sugarcrm\\Sugarcrm\\Notification\\SubscriptionFilter\\AssignedToMe';

    const NS_SF_TEAM = 'Sugarcrm\\Sugarcrm\\Notification\\SubscriptionFilter\\Team';

    const NS_EVENT_APPLICATION = 'Sugarcrm\\Sugarcrm\\Notification\\ApplicationEmitter\\Event';

    const NS_EVENT_BEAN = 'Sugarcrm\\Sugarcrm\\Notification\\BeanEmitter\\Event';

    const NS_EVENT_MODULE = 'Sugarcrm\\Sugarcrm\\Notification\\ModuleEventInterface';

    public function decoderEmitterVariants()
    {
        return array(
            array('ApplicationEmitter', array('type' => 'application', 'emitter_module_name' => null)),
            array('BeanEmitter', array('type' => 'bean', 'emitter_module_name' => null)),
            array('Account', array('type' => 'module', 'emitter_module_name' => 'Account')),
            array('Contact', array('type' => 'module', 'emitter_module_name' => 'Contact')),
        );
    }

    /**
     * @covers ::decodeEmitter
     * @dataProvider decoderEmitterVariants
     * @param $emitter
     * @param $expect
     */
    public function testDecodeEmitter($emitter, $expect)
    {
        $registry = new SubscriptionsRegistry();

        $res = \SugarTestReflection::callProtectedMethod($registry, 'decodeEmitter', array($emitter));
        $this->assertEquals($expect, $res);
    }

    public function getEmitterVariants()
    {
        return array(
            array('getApplicationEmitter', array(), array('type' => 'application', 'emitter_module_name' => null)),
            array('getBeanEmitter', array(), array('type' => 'bean', 'emitter_module_name' => null)),
            array('getModuleEmitter', array('Account'), array('type' => 'module', 'emitter_module_name' => 'Account')),
            array('getModuleEmitter', array('Contact'), array('type' => 'module', 'emitter_module_name' => 'Contact')),
        );
    }

    /**
     * @dataProvider getEmitterVariants
     * @covers ::getEmitter
     * @param $expectedMethod
     * @param $expectedParam
     * @param $decodedEmitter
     */
    public function testGetEmitter($expectedMethod, $expectedParam, $decodedEmitter)
    {
        $bean = $this->getMockBuilder('NotificationCenterSubscription')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $bean->type = $decodedEmitter['type'];
        $bean->emitter_module_name = $decodedEmitter['emitter_module_name'];

        $expectedEmitter = 'expectedEmitter' . microtime();

        $emitterRegistry = $this->getMock(self::NS_EMITTER_REGISTRY, array(
            'getApplicationEmitter', 'getBeanEmitter', 'getModuleEmitter'));

        $method = $emitterRegistry->expects($this->once())->method($expectedMethod)->willReturn($expectedEmitter);
        if ($expectedParam) {
            call_user_func_array(array($method, 'with'), $expectedParam);
        }

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getEmitterRegistry'));

        $subscriptionsRegistry->expects($this->atLeastOnce())->method('getEmitterRegistry')
            ->willReturn($emitterRegistry);

        $res = \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'getEmitter', array($bean));

        $this->assertEquals($expectedEmitter, $res);
    }

    public function isValidBeanForTreeVariants()
    {
        return array(
            array(
                true,
                'ApplicationEmitter',
                array(
                    'type' => 'application',
                    'emitter_module_name' => null,
                    'event_name' => 'event1',
                    'filter_name' => 'sf1'
                )
            ),
            array(
                false,
                'ApplicationEmitter',
                array(
                    'type' => 'application',
                    'emitter_module_name' => null,
                    'event_name' => 'event1',
                    'filter_name' => 'NotExists'
                )
            ),
            array(
                false,
                'ApplicationEmitter',
                array(
                    'type' => 'application',
                    'emitter_module_name' => null,
                    'event_name' => 'NotExists',
                    'filter_name' => 'NotExists'
                )
            ),
            array(
                false,
                'NotExists',
                array(
                    'type' => 'application',
                    'emitter_module_name' => null,
                    'event_name' => 'NotExists',
                    'filter_name' => 'NotExists'
                )
            ),
        );
    }

    /**
     * @dataProvider isValidBeanForTreeVariants
     * @covers ::isValidBeanForTree
     * @param $expects
     * @param $emitterName
     * @param $beanArr
     */
    public function testIsValidBeanForTree($expects, $emitterName, $beanArr)
    {
        $tree = array('ApplicationEmitter' => array('event1' => array('sf1' => 1)));

        $bean = $this->getMockBuilder('NotificationCenterSubscription')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $bean->fromArray($beanArr);

        $emitter = $this->getMock('\stdClass', array('__toString'));
        $emitter->expects($this->once())->method('__toString')
            ->willReturn($emitterName);

        $sr = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getEmitter'));
        $sr->expects($this->once())->method('getEmitter')->willReturn($emitter);

        $res = \SugarTestReflection::callProtectedMethod($sr, 'isValidBeanForTree', array(&$tree, $bean));

        $this->assertEquals($expects, $res);
    }

    /**
     * @covers ::getEmitters
     */
    public function testGetEmitters()
    {
        $appEmitter = 'getApplicationEmitter' . microtime();
        $moduleEmitters = array(
            'module1' => 'module1Emitter' . microtime(),
            'module2' => 'module2Emitter' . microtime(),
            'module3' => 'module3Emitter' . microtime(),
        );
        $emitterRegistry = $this->getMock(self::NS_EMITTER_REGISTRY, array(
            'getApplicationEmitter', 'getModuleEmitters', 'getModuleEmitter'));
        $emitterRegistry->expects($this->once())->method('getApplicationEmitter')->willReturn($appEmitter);
        $emitterRegistry->expects($this->once())->method('getModuleEmitters')->willReturn(array_keys($moduleEmitters));

        $map = array();
        foreach ($moduleEmitters as $module => $emitter) {
            $map[] = array($module, $emitter);
        }

        $emitterRegistry->expects($this->exactly(count($moduleEmitters)))->method('getModuleEmitter')
            ->will($this->returnValueMap($map));

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getEmitterRegistry'));

        $subscriptionsRegistry->expects($this->atLeastOnce())->method('getEmitterRegistry')
            ->willReturn($emitterRegistry);

        $emitters = \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'getEmitters');

        $this->assertArrayHasKey('module1', $emitters);
        $this->assertEquals($moduleEmitters['module1'], $emitters['module1']);

        $this->assertArrayHasKey('module2', $emitters);
        $this->assertEquals($moduleEmitters['module2'], $emitters['module2']);

        $this->assertArrayHasKey('module3', $emitters);
        $this->assertEquals($moduleEmitters['module3'], $emitters['module3']);

        $this->assertContains($appEmitter, $emitters);
        $this->assertTrue(is_int(array_search($appEmitter, $emitters)));
    }

    /**
     * @covers ::getSubscriptionFilters
     */
    public function testGetSubscriptionFilters()
    {
        $filters = array(
            'filterName1' => 'filter1' . microtime(),
            'filterName2' => 'filter2' . microtime(),
            'filterName3' => 'filter3' . microtime(),
        );

        $sfRegistry = $this->getMock(self::NS_SF_REGISTRY, array('getFilters', 'getFilter'));
        $sfRegistry->expects($this->once())->method('getFilters')->willReturn(array_keys($filters));

        $map = array();
        foreach ($filters as $name => $filter) {
            $map[] = array($name, $filter);
        }

        $sfRegistry->expects($this->exactly(count($map)))->method('getFilter')
            ->will($this->returnValueMap($map));

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array(
            'getSubscriptionFilterRegistry'));
        $subscriptionsRegistry->expects($this->atLeastOnce())->method('getSubscriptionFilterRegistry')
            ->willReturn($sfRegistry);

        $filtersRes = \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'getSubscriptionFilters');

        $this->assertContains($filters['filterName1'], $filtersRes);
        $this->assertContains($filters['filterName2'], $filtersRes);
        $this->assertContains($filters['filterName3'], $filtersRes);
    }

    /**
     * @covers ::getDiff
     */
    public function testDiffBranch()
    {
        $beans = array(
            'sameId1' => array('id' => 'sameId1', 'carrier_name' => 'CarrierSame1', 'carrier_option' => 'optionSame'),
            'sameId2' => array('id' => 'sameId2', 'carrier_name' => 'CarrierSame2'),
            'sameId3' => array('id' => 'sameId3', 'carrier_name' => 'CarrierSame3', 'carrier_option' => ''),
            'delId1' => array('id' => 'delId1', 'carrier_name' => 'CarrierDelete1', 'carrier_option' => 'optionDelete'),
            'delId2' => array('id' => 'delId2', 'carrier_name' => 'CarrierDelete2'),
        );

        $carriers = array(
            'same1' => array('CarrierSame1', 'optionSame'),
            'same2' => array('CarrierSame2'),
            'same3' => array('CarrierSame3', ''),
            'insert1' => array('CarrierInsert1', 'optionInsert'),
            'insert2' => array('CarrierInsert2'),
        );

        foreach ($beans as $key => $beanArr) {
            $bean = $this->getMockBuilder('NotificationCenterSubscription')
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();
            $bean->fromArray($beanArr);
            $beans[$key] = $bean;
        }

        $sr = new SubscriptionsRegistry();

        $diff = \SugarTestReflection::callProtectedMethod($sr, 'getDiff', array($beans, array_values($carriers)));

        $this->assertArrayHasKey('insert', $diff);
        $this->assertArrayHasKey('delete', $diff);

        $this->assertContains($carriers['insert1'], $diff['insert']);
        $this->assertContains($carriers['insert2'], $diff['insert']);
        $this->assertNotContains($carriers['same1'], $diff['insert']);
        $this->assertNotContains($carriers['same2'], $diff['insert']);
        $this->assertNotContains($carriers['same3'], $diff['insert']);

        $this->assertNotContains($beans['sameId1'], $diff['delete']);
        $this->assertNotContains($beans['sameId2'], $diff['delete']);
        $this->assertNotContains($beans['sameId3'], $diff['delete']);
        $this->assertContains($beans['delId1'], $diff['delete']);
        $this->assertContains($beans['delId2'], $diff['delete']);
    }

    /**
     * @covers ::fillDefaultConfig
     */
    public function testFillDefaultConfig()
    {
        $tree = array(
            'emitter' =>
                array(
                    'event1' => array(
                        'sf1' => array(),
                        'sf2' => array(array('carrier1', 'option1')),
                        'sf3' => array()
                    ),
                    'event2' => array('sf1' => array(array('carrier2', 'option2'))),
                )
        );

        $treeExpect = $tree;
        $treeExpect['emitter']['event1']['sf1'] = SubscriptionsRegistry::CARRIER_VALUE_DEFAULT;
        $treeExpect['emitter']['event1']['sf3'] = SubscriptionsRegistry::CARRIER_VALUE_DEFAULT;

        $subscriptionsRegistry = new SubscriptionsRegistry();

        \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'fillDefaultConfig', array(&$tree));

        $this->assertEquals($treeExpect, $tree);
    }

    /**
     * @covers ::getUserConfiguration
     */
    public function testGetUserConfiguration()
    {
        $userId = 'some-user-id';
        $emitterName = 'emitterName';
        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->getMock();

        $beanListArr = array(
            array(
                'event_name' => 'event1',
                'filter_name' => 'sf1',
                'user_id' => $userId,
                'carrier_name' => 'cn1',
                'carrier_option' => 'co1'
            ),
            array(
                'event_name' => 'event1',
                'filter_name' => 'sf2',
                'user_id' => $userId,
                'carrier_name' => 'cn1',
                'carrier_option' => 'co1'
            ),
            array(
                'event_name' => 'event2',
                'filter_name' => 'sf1',
                'user_id' => $userId,
                'carrier_name' => 'cn1',
                'carrier_option' => 'co1'
            ),
        );

        $beanList = array();
        foreach ($beanListArr as $key => $beanArr) {
            $bean = $this->getMockBuilder('NotificationCenterSubscription')
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();
            $bean->fromArray($beanArr);
            $beanList[$key] = $bean;
        }

        $emitter = $this->getMock('\stdClass', array('__toString'));
        $emitter->expects($this->atLeastOnce())->method('__toString')->willReturn($emitterName);

        $tree = array(
            $emitterName =>
                array(
                    'event1' => array(
                        'sf1' => array(),
                        'sf2' => array(),
                    ),
                    'event2' => array(
                        'sf1' => array(),
                    ),
                )
        );

        $expectConfig = $tree;
        $expectConfig[$emitterName]['event1']['sf1'] = array(array('cn1', 'co1'));
        $expectConfig[$emitterName]['event1']['sf2'] = array(array('cn1', 'co1'));
        $expectConfig[$emitterName]['event2']['sf1'] = array(array('cn1', 'co1'));

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array(
            'getEmitter', 'getTree', 'getSugarQuery', 'getBeans', 'isValidBeanForTree', 'fillDefaultConfig'));

        $subscriptionsRegistry->expects($this->exactly(count($beanListArr)))->method('getEmitter')
            ->willReturn($emitter);

        $subscriptionsRegistry->expects($this->once())->method('getTree')->willReturn($tree);

        $subscriptionsRegistry->expects($this->once())->method('fillDefaultConfig')->willReturnArgument(0);

        $subscriptionsRegistry->expects($this->once())->method('getSugarQuery')->willReturn($sugarQuery);
        $subscriptionsRegistry->expects($this->once())->method('getBeans')
            ->willReturn($beanList)->with($this->equalTo($sugarQuery));

        $subscriptionsRegistry->expects($this->exactly(count($beanListArr)))->method('isValidBeanForTree')
            ->willReturn(true)->with($this->anything(), $this->isInstanceOf('NotificationCenterSubscription'));

        $config = $subscriptionsRegistry->getUserConfiguration($userId);

        $this->assertEquals($expectConfig, $config);
    }

    /**
     * @covers ::getGlobalConfiguration
     */
    public function testGetGlobalConfiguration()
    {
        $emitterName = 'emitterName';
        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->getMock();

        $beanListArr = array(
            array(
                'event_name' => 'event1',
                'filter_name' => 'sf1',
                'carrier_name' => 'cn1',
                'carrier_option' => 'co1'
            ),
            array(
                'event_name' => 'event1',
                'filter_name' => 'sf2',
                'carrier_name' => 'cn1',
                'carrier_option' => 'co1'
            ),
            array(
                'event_name' => 'event2',
                'filter_name' => 'sf1',
                'carrier_name' => 'cn1',
                'carrier_option' => 'co1'
            )
        );

        $beanList = array();
        foreach ($beanListArr as $key => $beanArr) {
            $bean = $this->getMockBuilder('NotificationCenterSubscription')
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();
            $bean->fromArray($beanArr);
            $beanList[$key] = $bean;
        }

        $emitter = $this->getMock('\stdClass', array('__toString'));
        $emitter->expects($this->atLeastOnce())->method('__toString')->willReturn($emitterName);

        $tree = array(
            $emitterName =>
                array(
                    'event1' => array(
                        'sf1' => array(),
                        'sf2' => array(),
                    ),
                    'event2' => array(
                        'sf1' => array(),
                    ),
                )
        );

        $expectConfig = $tree;
        $expectConfig[$emitterName]['event1']['sf1'] = array(array('cn1', 'co1'));
        $expectConfig[$emitterName]['event1']['sf2'] = array(array('cn1', 'co1'));
        $expectConfig[$emitterName]['event2']['sf1'] = array(array('cn1', 'co1'));

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array(
            'getEmitter', 'getTree', 'getSugarQuery', 'getBeans', 'isValidBeanForTree'));

        $subscriptionsRegistry->expects($this->exactly(count($beanListArr)))->method('getEmitter')
            ->willReturn($emitter);

        $subscriptionsRegistry->expects($this->once())->method('getTree')->willReturn($tree);

        $subscriptionsRegistry->expects($this->once())->method('getSugarQuery')->willReturn($sugarQuery);
        $subscriptionsRegistry->expects($this->once())->method('getBeans')
            ->willReturn($beanList)->with($this->equalTo($sugarQuery));

        $subscriptionsRegistry->expects($this->exactly(count($beanListArr)))->method('isValidBeanForTree')
            ->willReturn(true)->with($this->anything(), $this->isInstanceOf('NotificationCenterSubscription'));

        $config = $subscriptionsRegistry->getGlobalConfiguration();

        $this->assertEquals($expectConfig, $config);
    }

    /**
     * @group ft1
     * @covers ::setConfiguration
     */
    public function testSetConfiguration()
    {
        $userId = 'some-user-id-value';
        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->getMock();

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array(
            'getTree',
            'getSugarQuery',
            'getBeans',
            'pathToBranch',
            'reduceBeans',
            'getDiff',
            'processDiff',
            'deleteConfigBeans'
        ));

        $subscriptionsRegistry->expects($this->once())->method('getSugarQuery')
            ->with($this->equalTo($userId))->willReturn($sugarQuery);

        $beanListArr = array(
            array(
                'user_id' => $userId,
                'event_name' => 'event1',
                'filter_name' => 'sf1',
                'carrier_name' => 'cn1',
                'carrier_option' => 'co1'
            ),
            array(
                'user_id' => $userId,
                'event_name' => 'event1',
                'filter_name' => 'sf2',
                'carrier_name' => 'cn1',
                'carrier_option' => 'co1'
            ),
            array(
                'user_id' => $userId,
                'event_name' => 'event2',
                'filter_name' => 'sf1',
                'carrier_name' => 'cn1',
                'carrier_option' => 'co1'
            )
        );
        $beanList = array();
        foreach ($beanListArr as $key => $beanArr) {
            $bean = $this->getMockBuilder('NotificationCenterSubscription')
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();
            $bean->fromArray($beanArr);
            $beanList[$key] = $bean;
        }

        $subscriptionsRegistry->expects($this->once())->method('getBeans')
            ->willReturn($beanList)->with($this->equalTo($sugarQuery));

        $tree = array(
            'emitter1' => array(
                'event1' => array(
                    'sf1' => array(),
                ),
            ),
            'emitter2' => array(
                'event2' => array(
                    'sf3' => array()
                ),
            ),
        );

        $subscriptionsRegistry->expects($this->once())->method('getTree')->willReturn($tree);

        $config = $tree;
        $config['emitter1']['event1']['sf1'] = array(array('cn1', 'co1'), array('cn2', 'co2'));
        $config['emitter2']['event2']['sf3'] = SubscriptionsRegistry::CARRIER_VALUE_DEFAULT;

        $subscriptionsRegistry->expects($this->exactly(2))->method('pathToBranch')
            ->with(
                $this->logicalOr($this->equalTo('emitter1'), $this->equalTo('emitter2')),
                $this->logicalOr($this->equalTo('event1'), $this->equalTo('event2')),
                $this->logicalOr($this->equalTo('sf1'), $this->equalTo('sf3'))
            )->will($this->onConsecutiveCalls(array(0 => 'path1'), array(0 => 'path2')));

        $subscriptionsRegistry->expects($this->exactly(2))->method('reduceBeans')
            ->with(
                $this->equalTo($beanList),
                $this->logicalOr(
                    $this->equalTo(array(0 => 'path1')),
                    $this->equalTo(array(0 => 'path2'))
                )
            )->will($this->onConsecutiveCalls('movedBeans1', 'movedBeans2'));

        $subscriptionsRegistry->expects($this->exactly(2))->method('getDiff')
            ->with(
                $this->logicalOr(
                    $this->equalTo('movedBeans1'),
                    $this->equalTo('movedBeans2')
                ),
                $this->logicalOr(
                    $this->equalTo(array(array('cn1'), array('cn2'))),
                    $this->equalTo(array())
                )
            )->will($this->onConsecutiveCalls(
                array('delete' => array('delete1'), 'insert' => array('insert1')),
                array('delete' => array('delete2'), 'insert' => array('insert2'))
            ));

        $subscriptionsRegistry->expects($this->exactly(2))->method('processDiff')
            ->with(
                $this->logicalOr(
                    $this->equalTo(array(0 => 'path1', 'user_id' => $userId)),
                    $this->equalTo(array(0 => 'path2', 'user_id' => $userId))
                ),
                $this->logicalOr($this->equalTo(array('delete1')), $this->equalTo(array('delete2'))),
                $this->logicalOr($this->equalTo(array('insert1')), $this->equalTo(array('insert2')))
            );

        $subscriptionsRegistry->expects($this->once())->method('deleteConfigBeans')->with($this->equalTo($beanList));

        \SugarTestReflection::callProtectedMethod(
            $subscriptionsRegistry,
            'setConfiguration',
            array($userId, $config, true)
        );
    }

    /**
     * @covers ::pathToBranch
     */
    public function testPathToBranch()
    {
        $emitter = 'EmitterName';
        $event = 'EventName';
        $filterName = 'filterName';
        $emitterArr = array('type' => 'emitterType', 'emitter_module_name' => 'emitterModule');

        $expect = array(
            'type' => $emitterArr['type'],
            'emitter_module_name' => $emitterArr['emitter_module_name'],
            'filter_name' => $filterName,
            'event_name' => $event
        );

        $sr = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('decodeEmitter'));

        $sr->expects($this->once())->method('decodeEmitter')
            ->with($this->equalTo($emitter))
            ->willReturn($emitterArr);

        $path = \SugarTestReflection::callProtectedMethod($sr, 'pathToBranch', array($emitter, $event, $filterName));

        $this->assertEquals($expect, $path);
    }

    /**
     * @covers ::getBeans
     */
    public function testGetBeansWithFields()
    {
        $beanList = 'BeansValue';
        $fields = array('field1', 'field2');

        $expectsFields = $fields;

        $query = $this->getMock('SugarQuery', array(), array(), '', false);

        $sr = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getNewBaseBean'));
        $bean = $this->getMockBuilder('NotificationCenterSubscription')
            ->disableOriginalConstructor()
            ->setMethods(array('fetchFromQuery'))
            ->getMock();

        $bean->expects($this->once())->method('fetchFromQuery')
            ->with($this->equalTo($query), $this->equalTo($expectsFields))
            ->willReturn($beanList);

        $sr->expects($this->once())->method('getNewBaseBean')->willReturn($bean);


        $resBeans = \SugarTestReflection::callProtectedMethod($sr, 'getBeans', array($query, $fields));

        $this->assertEquals($beanList, $resBeans);
    }

    /**
     * @covers ::getBeans
     */
    public function testGetBeansWithOutNoFields()
    {
        $beanList = 'BeansValue';

        $expectsFields = array(
            'type',
            'emitter_module_name',
            'event_name',
            'filter_name',
            'carrier_name',
            'carrier_option'
        );

        $query = $this->getMock('SugarQuery', array(), array(), '', false);

        $sr = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getNewBaseBean'));
        $bean = $this->getMockBuilder('NotificationCenterSubscription')
            ->disableOriginalConstructor()
            ->setMethods(array('fetchFromQuery'))
            ->getMock();

        $bean->expects($this->once())->method('fetchFromQuery')
            ->with($this->equalTo($query), $this->equalTo($expectsFields))
            ->willReturn($beanList);

        $sr->expects($this->once())->method('getNewBaseBean')->willReturn($bean);

        $resBeans = \SugarTestReflection::callProtectedMethod($sr, 'getBeans', array($query));

        $this->assertEquals($beanList, $resBeans);
    }

    /**
     * @covers ::setGlobalConfiguration
     */
    public function testSetGlobalConfiguration()
    {
        $config = array('config' => 'value');

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('setConfiguration'));
        $subscriptionsRegistry->expects($this->once())->method('setConfiguration')
            ->with($this->isNull(), $this->equalTo($config), $this->isTrue());

        $subscriptionsRegistry->setGlobalConfiguration($config);
    }

    /**
     * @covers ::setUserConfiguration
     */
    public function testSetUserConfiguration()
    {
        $userId = 'some-user-id-value';
        $config = array('config' => 'value');

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('setConfiguration'));
        $subscriptionsRegistry->expects($this->once())->method('setConfiguration')
            ->with($this->equalTo($userId), $this->equalTo($config), $this->isFalse());

        $subscriptionsRegistry->setUserConfiguration($userId, $config);
    }

    /**
     * @covers ::getUsers
     */
    public function testGetUsers()
    {
        $event = new ApplicationEvent('ApplicationEvent');
        $userConfig = array(
            'Team-Name' => array(
                'userId1' => array(
                    'Team-Name' => array('userId1-Filter-Team')
                ),
                'userId2' => array(
                    'Team-Name' => array('userId2-Filter-Team')
                )
            ),
            'Assign' => array(
                'userId2' => array(
                    'Assign' => array('userId2-Filter-AssignedToMe-Name')
                ),
                'userId3' => array(
                    'Assign' => array('userId3-Filter-AssignedToMe-Name')
                ),
            )
        );

        $globalConfig = array(
            'Assign' => array('Global-Filter-AssignedToMe-Name'),
        );

        $expectConfig = array(
            'userId1' => array(
                'filter' => 'Team-Name',
                'config' => 'User1ConfigTeam'
            ),
            'userId2' => array(
                'filter' => 'Assign',
                'config' => 'User2Assigned'
            ),
            'userId3' => array(
                'filter' => 'Assign',
                'config' => 'User2Assigned'
            )
        );

        $sfAssignedToMe = $this->getMock(self::NS_SF_ASSIGNED_TO_ME, array('__toString'));
        $sfAssignedToMe->expects($this->once())->method('__toString')
            ->willReturn('Assign');
        $sfTeam = $this->getMock(self::NS_SF_TEAM, array('__toString'));
        $sfTeam->expects($this->atLeastOnce())->method('__toString')
            ->willReturn('Team-Name');
        $usersFilters = array($sfTeam, $sfAssignedToMe);

        $subscriptionsRegistry = $this->getMock(
            self::NS_SUBSCRIPTIONS_REGISTRY,
            array('getGlobalEventConfig', 'getUsersFilters', 'getUsersEventConfig', 'calculateUserConfig')
        );

        $subscriptionsRegistry->expects($this->once())->method('getGlobalEventConfig')
            ->with($this->equalTo($event))
            ->willReturn($globalConfig);

        $subscriptionsRegistry->expects($this->once())->method('getUsersFilters')
            ->with($this->equalTo($event))
            ->willReturn($usersFilters);

        $subscriptionsRegistry->expects($this->exactly(count($usersFilters)))->method('getUsersEventConfig')
            ->with(
                $this->equalTo($event),
                $this->logicalOr($this->equalTo($sfTeam), $this->equalTo($sfAssignedToMe))
            )->will($this->returnValueMap(array(
                array($event, $sfTeam, $userConfig['Team-Name']),
                array($event, $sfAssignedToMe, $userConfig['Assign']),
            )));

        $totalFoundUsers = count($userConfig['Team-Name']) + count($userConfig['Assign']);
        $subscriptionsRegistry->expects($this->exactly($totalFoundUsers))->method('calculateUserConfig')
            ->with(
                $this->logicalOr(
                    $this->equalTo($userConfig['Team-Name']['userId1']['Team-Name']),
                    $this->equalTo($userConfig['Team-Name']['userId2']['Team-Name']),
                    $this->equalTo($userConfig['Assign']['userId2']['Assign']),
                    $this->equalTo($userConfig['Assign']['userId3']['Assign'])
                ),
                $this->logicalOr(
                    $this->equalTo(array()),
                    $this->equalTo($globalConfig['Assign'])
                )
            )->will($this->returnValueMap(array(
                array($userConfig['Team-Name']['userId1']['Team-Name'], array(), 'User1ConfigTeam'),
                array($userConfig['Team-Name']['userId2']['Team-Name'], array(), 'User2ConfigTeam'),
                array($userConfig['Assign']['userId2']['Assign'], $globalConfig['Assign'], 'User2Assigned'),
                array($userConfig['Assign']['userId3']['Assign'], $globalConfig['Assign'], 'User2Assigned')
            )));

        $res = $subscriptionsRegistry->getUsers($event);

        $this->assertEquals($expectConfig, $res);
    }

    /**
     * @covers ::getGlobalEventList
     */
    public function testGetGlobalEventList()
    {
        $expectedList = array('row1', 'row2', 'row3', 'row4');
        $event = new ApplicationEvent('ApplicationEvent');

        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->disableOriginalConstructor()
            ->getMock();

        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->setMethods(array('where', 'execute'))
            ->disableOriginalConstructor()
            ->getMock();

        $sugarQuery->expects($this->once())->method('execute')
            ->willReturn($expectedList);

        $sugarQuery->expects($this->once())->method('where')
            ->willReturn($where);

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getSugarQuery', 'eventWhere'));
        $subscriptionsRegistry->expects($this->once())->method('getSugarQuery')
            ->with($this->isNull())
            ->willReturn($sugarQuery);
        $subscriptionsRegistry->expects($this->once())->method('eventWhere')
            ->with($this->equalTo($event));

        $res = \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'getGlobalEventList', array($event));

        $this->assertEquals($expectedList, $res);
    }

    public function calculationConfigVersions()
    {
        $userData1 = array(
            'main' => array('CarrierUserMain' => array('Opt1')),
            'bean' => array('CarrierUserBean' => array('Opt2')),
        );
        $globalData1 = array(
            'main' => array('CarrierGlobalMain' => array('Opt1')),
            'bean' => array('CarrierGlobalBean' => array('Opt2')),
        );
        $expects1 = $userData1['main'];

        $userData2 = array(
            'bean' => array('CarrierUserBean' => array('Opt2')),
        );
        $globalData2 = array(
            'main' => array('CarrierGlobalMain' => array('Opt1')),
            'bean' => array('CarrierGlobalBean' => array('Opt2')),
        );
        $expects2 = $userData2['bean'];

        $userData3 = array();
        $globalData3 = array(
            'main' => array('CarrierGlobalMain' => array('Opt1')),
            'bean' => array('CarrierGlobalBean' => array('Opt2')),
        );
        $expects3 = $globalData3['main'];

        $userData4 = array();
        $globalData4 = array(
            'bean' => array('CarrierGlobalBean' => array('Opt2')),
        );
        $expects4 = $globalData4['bean'];

        $userData5 = array();
        $globalData5 = array();
        $expects5 = array();

        $userData6 = array(
            'main' => array('CarrierUserMain' => array('Opt1'), '' => array()),
            'bean' => array('CarrierUserBean' => array('Opt2')),
        );
        $globalData6 = array(
            'main' => array('CarrierGlobalMain' => array('Opt1')),
            'bean' => array('CarrierGlobalBean' => array('Opt2')),
        );
        $expects6 = array();

        return array(
            array($userData1, $globalData1, $expects1),
            array($userData2, $globalData2, $expects2),
            array($userData3, $globalData3, $expects3),
            array($userData4, $globalData4, $expects4),
            array($userData5, $globalData5, $expects5),
            array($userData6, $globalData6, $expects6),
        );
    }

    /**
     * @dataProvider calculationConfigVersions
     * @covers ::calculateUserConfig
     */
    public function testCalculateUserConfig($userData, $globalData, $expects)
    {
        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('formatParsedUsersData'));
        $subscriptionsRegistry->expects($this->any())->method('formatParsedUsersData')
            ->will($this->returnArgument(0));

        $res = \SugarTestReflection::callProtectedMethod(
            $subscriptionsRegistry,
            'calculateUserConfig',
            array($userData, $globalData)
        );

        $this->assertEquals($res, $expects);
    }

    public function typesForNormalize()
    {
        return array(
            array('application', 'main'),
            array('module', 'main'),
            array('bean', 'bean'),
        );
    }

    /**
     * @dataProvider typesForNormalize
     * @covers ::normalizeType
     */
    public function testNormalizeType($arg, $expect)
    {
        $subscriptionsRegistry = new SubscriptionsRegistry();

        $res = \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'normalizeType', array($arg));

        $this->assertEquals($res, $expect);
    }

    /**
     * @covers ::getGlobalEventConfig
     */
    public function testGetGlobalEventConfig()
    {
        $list = array(
            array(
                'type' => 'bean',
                'filter_name' => 'Team',
                'carrier_name' => 'carrier1',
                'carrier_option' => '',
            ),
            array(
                'type' => 'bean',
                'filter_name' => 'Team',
                'carrier_name' => 'carrier2',
                'carrier_option' => '',
            ),
            array(
                'type' => 'module',
                'filter_name' => 'Team',
                'carrier_name' => 'carrier1',
                'carrier_option' => '',
            ),
        );
        $expects = array(
            'Team' => array(
                'bean' => array(
                    'carrier1' => array(''),
                    'carrier2' => array(''),
                ),
                'main' => array(
                    'carrier1' => array(''),
                )
            )
        );

        $typeMap = array(
            array('bean', 'bean'),
            array('module', 'main'),
        );

        $event = new ApplicationEvent('AppEvent');
        $subscriptionReg = $this->getMock(
            self::NS_SUBSCRIPTIONS_REGISTRY,
            array('getGlobalEventList', 'normalizeType')
        );
        $subscriptionReg->expects($this->once())->method('getGlobalEventList')
            ->with($this->equalTo($event))
            ->willReturn($list);

        $subscriptionReg->expects($this->any())->method('normalizeType')
            ->with($this->logicalOr($this->equalTo('bean'), $this->equalTo('module')))
            ->will($this->returnValueMap($typeMap));

        $res = \SugarTestReflection::callProtectedMethod($subscriptionReg, 'getGlobalEventConfig', array($event));

        $this->assertEquals($expects, $res);
    }

    /**
     * @covers ::getUsersFilters
     */
    public function testGetUsersFilters()
    {
        $first = $this->getMock(self::NS_SF_ASSIGNED_TO_ME, array('getOrder'));
        $first->expects($this->atLeastOnce())->method('getOrder')
            ->willReturn(100);
        $second = $this->getMock(self::NS_SF_ASSIGNED_TO_ME, array('getOrder'));
        $second->expects($this->atLeastOnce())->method('getOrder')
            ->willReturn(200);
        $third = $this->getMock(self::NS_SF_ASSIGNED_TO_ME, array('getOrder'));
        $third->expects($this->atLeastOnce())->method('getOrder')
            ->willReturn(300);
        $fourth = $this->getMock(self::NS_SF_ASSIGNED_TO_ME, array('getOrder'));
        $fourth->expects($this->atLeastOnce())->method('getOrder')
            ->willReturn(400);

        $event = new ApplicationEvent('AppEvent');

        $subscriptionReg = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getSupportedFilters'));

        $subscriptionReg->expects($this->atLeastOnce())->method('getSupportedFilters')
            ->with($this->equalTo($event))
            ->willReturn(array($fourth, $first, $third, $second));


        $sortedList = \SugarTestReflection::callProtectedMethod($subscriptionReg, 'getUsersFilters', array($event));

        $this->assertEquals(array($first, $second, $third, $fourth), $sortedList);
    }

    /**
     * @covers ::getUsersEventConfig
     */
    public function testGetUsersEventConfig()
    {
        $filterName = 'AssignedToMe';
        $list = array(
            array(
                'user_id' => 'userId1',
                'type' => 'bean',
                'filter_name' => $filterName,
                'carrier_name' => 'carrier1',
                'carrier_option' => 'u1c1k1',
            ),
            array(
                'user_id' => 'userId1',
                'type' => 'bean',
                'filter_name' => $filterName,
                'carrier_name' => 'carrier1',
                'carrier_option' => 'u1c1k2',
            ),
            array(
                'user_id' => 'userId1',
                'type' => 'bean',
                'filter_name' => $filterName,
                'carrier_name' => 'carrier2',
                'carrier_option' => 'u1c2k1',
            ),
            array(
                'user_id' => 'userId1',
                'type' => 'module',
                'filter_name' => $filterName,
                'carrier_name' => 'carrier1',
                'carrier_option' => 'u1c1k1',
            ),
            array(
                'user_id' => 'userId2',
                'type' => 'bean',
                'filter_name' => $filterName,
                'carrier_name' => 'carrier1',
                'carrier_option' => 'u2c1k1',
            ),
            array(
                'user_id' => 'userId2',
                'type' => 'bean',
                'filter_name' => $filterName,
                'carrier_name' => ' ',
                'carrier_option' => 'empty',
            ),
        );

        $expects = array(
            'userId1' => array(
                $filterName => array(
                    'bean' => array(
                        'carrier1' => array('u1c1k1', 'u1c1k2'),
                        'carrier2' => array('u1c2k1')
                    ),
                    'main' => array(
                        'carrier1' => array('u1c1k1')
                    ),
                ),
            ),
            'userId2' => array(
                $filterName => array(
                    'bean' => array(
                        'carrier1' => array('u2c1k1'),
                        '' => array('empty')
                    ),
                )
            )
        );

        $typeMap = array(
            array('bean', 'bean'),
            array('module', 'main'),
        );

        $event = new ApplicationEvent('AppEvent');

        $sf = $this->getMock(self::NS_SF_ASSIGNED_TO_ME, array('__toString'));
        $sf->expects($this->atLeastOnce())->method('__toString')
            ->willReturn($filterName);

        $subscriptionReg = $this->getMock(
            self::NS_SUBSCRIPTIONS_REGISTRY,
            array('getUsersList', 'normalizeType')
        );
        $subscriptionReg->expects($this->once())->method('getUsersList')
            ->with($this->equalTo($event))
            ->willReturn($list);

        $subscriptionReg->expects($this->any())->method('normalizeType')
            ->with($this->logicalOr($this->equalTo('bean'), $this->equalTo('module')))
            ->will($this->returnValueMap($typeMap));

        $res = \SugarTestReflection::callProtectedMethod($subscriptionReg, 'getUsersEventConfig', array($event, $sf));

        $this->assertEquals($expects, $res);
    }

    /**
     * @covers ::eventWhere
     */
    public function testEventWhere4ApplicationEvent()
    {
        $eventName = 'EventName1';

        $event = $this->getMock(self::NS_EVENT_APPLICATION, array('__toString'), array($eventName));
        $event->expects($this->atLeastOnce())->method('__toString')
            ->willReturn($eventName);

        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->setMethods(array('equals'))
            ->disableOriginalConstructor()
            ->getMock();

        $where->expects($this->exactly(3))->method('equals')
            ->with(
                $this->logicalOr(
                    $this->equalTo('notification_subscription.type'),
                    $this->equalTo('notification_subscription.emitter_module_name'),
                    $this->equalTo('notification_subscription.event_name')
                ),
                $this->logicalOr(
                    $this->equalTo('application'),
                    $this->equalTo(''),
                    $this->equalTo($eventName)
                )
            )
            ->will($this->returnSelf());

        $subscriptionReg = new SubscriptionsRegistry;
        \SugarTestReflection::callProtectedMethod($subscriptionReg, 'eventWhere', array($event, $where));

    }

    /**
     * @covers ::eventWhere
     */
    public function testEventWhere4ModuleEvent()
    {
        $eventName = 'EventName2';
        $moduleName = 'Module1';

        $event = $this->getMock(self::NS_EVENT_MODULE, array('__toString', 'getModuleName'), array($eventName));
        $event->expects($this->atLeastOnce())->method('__toString')
            ->willReturn($eventName);
        $event->expects($this->atLeastOnce())->method('getModuleName')
            ->willReturn($moduleName);

        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->setMethods(array('equals'))
            ->disableOriginalConstructor()
            ->getMock();

        $where->expects($this->exactly(3))->method('equals')
            ->with(
                $this->logicalOr(
                    $this->equalTo('notification_subscription.type'),
                    $this->equalTo('notification_subscription.emitter_module_name'),
                    $this->equalTo('notification_subscription.event_name')
                ),
                $this->logicalOr(
                    $this->equalTo('module'),
                    $this->equalTo($moduleName),
                    $this->equalTo($eventName)
                )
            )
            ->will($this->returnSelf());

        $subscriptionReg = new SubscriptionsRegistry;
        \SugarTestReflection::callProtectedMethod($subscriptionReg, 'eventWhere', array($event, $where));
    }

    /**
     * @covers ::eventWhere
     */
    public function testEventWhere4BeanEvent()
    {
        $eventName = 'EventName2';
        $moduleName = 'Module1';

        $event = $this->getMock(self::NS_EVENT_BEAN, array('__toString', 'getModuleName'), array($eventName));
        $event->expects($this->atLeastOnce())->method('__toString')
            ->willReturn($eventName);
        $event->expects($this->atLeastOnce())->method('getModuleName')
            ->willReturn($moduleName);

        $whereAnd = $this->getMockBuilder('SugarQuery_Builder_Andwhere')
            ->setMethods(array('equals'))
            ->disableOriginalConstructor()
            ->getMock();

        $whereAnd->expects($this->exactly(4))->method('equals')
            ->with(
                $this->logicalOr(
                    $this->equalTo('notification_subscription.type'),
                    $this->equalTo('notification_subscription.emitter_module_name')
                ),
                $this->logicalOr(
                    $this->equalTo('module'),
                    $this->equalTo($moduleName),
                    $this->equalTo('bean'),
                    $this->equalTo('')
                )
            )
            ->will($this->returnSelf());

        $whereOr = $this->getMockBuilder('SugarQuery_Builder_Orwhere')
            ->setMethods(array('queryAnd'))
            ->disableOriginalConstructor()
            ->getMock();
        $whereOr->expects($this->exactly(2))->method('queryAnd')->willReturn($whereAnd);

        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->setMethods(array('queryOr', 'equals'))
            ->disableOriginalConstructor()
            ->getMock();
        $where->expects($this->once())->method('equals')
            ->with($this->equalTo('notification_subscription.event_name'), $this->equalTo($eventName));

        $where->expects($this->once())->method('queryOr')->willReturn($whereOr);

        $subscriptionReg = new SubscriptionsRegistry;
        \SugarTestReflection::callProtectedMethod($subscriptionReg, 'eventWhere', array($event, $where));
    }

    /**
     * @covers ::getSupportedFilters
     */
    public function testGetSupportedFilters()
    {
        $event = new ApplicationEvent('AppEvent');

        $sfSupport1 = $this->getMock(self::NS_SF_ASSIGNED_TO_ME, array('supports'));
        $sfSupport1->expects($this->once())->method('supports')
            ->with($this->equalTo($event))
            ->willReturn(true);

        $sfSupport2 = $this->getMock(self::NS_SF_ASSIGNED_TO_ME, array('supports'));
        $sfSupport2->expects($this->once())->method('supports')
            ->with($this->equalTo($event))
            ->willReturn(true);

        $sfUnSupport1 = $this->getMock(self::NS_SF_ASSIGNED_TO_ME, array('supports'));
        $sfUnSupport1->expects($this->once())->method('supports')
            ->with($this->equalTo($event))
            ->willReturn(false);

        $sfUnSupport2 = $this->getMock(self::NS_SF_ASSIGNED_TO_ME, array('supports'));
        $sfUnSupport2->expects($this->once())->method('supports')
            ->with($this->equalTo($event))
            ->willReturn(false);

        $subscriptionReg = $this->getMock(
            self::NS_SUBSCRIPTIONS_REGISTRY,
            array('getSubscriptionFilters')
        );
        $subscriptionReg->expects($this->once())->method('getSubscriptionFilters')
            ->willReturn(array($sfSupport1, $sfUnSupport1, $sfSupport2, $sfUnSupport2));

        $res = \SugarTestReflection::callProtectedMethod($subscriptionReg, 'getSupportedFilters', array($event));

        $this->assertContains($sfSupport1, $res);
        $this->assertContains($sfSupport2, $res);
        $this->assertNotContains($sfUnSupport1, $res);
        $this->assertNotContains($sfUnSupport2, $res);
    }

    /**
     * @covers ::formatParsedUsersData
     */
    public function testFormatParsedUsersData()
    {
        $userConfig = array(
            'carrier1' => array('option1', 'option1', 'option2'),
            'carrier2' => array('option3')
        );

        $expects = array(
            array('carrier2', 'option3'),
            array('carrier1', 'option2'),
            array('carrier1', 'option1'),
        );

        $subscriptionReg = new SubscriptionsRegistry();
        $res = \SugarTestReflection::callProtectedMethod($subscriptionReg, 'formatParsedUsersData', array($userConfig));

        $this->assertContains($expects[0], $res);
        $this->assertContains($expects[1], $res);
        $this->assertContains($expects[2], $res);
    }

    /**
     * @covers ::getUsersList
     */
    public function testGetUsersList()
    {
        $userAlias = 'UserJoinAlias';

        $event = new ApplicationEvent('someApplicationEvent');

        $queryResult = array('expected', 'query', 'result');

        $subscriptionFilter = $this->getMock(self::NS_SF_ASSIGNED_TO_ME);

        $subscriptionReg = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getBaseSugarQuery', 'eventWhere'));

        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->setMethods(array('joinTable', 'select', 'execute'))
            ->disableOriginalConstructor()
            ->getMock();

        $joinWhere = $this->getMockBuilder('SugarQuery_Builder_Andwhere')
            ->setMethods(array('equalsField', 'equals'))
            ->disableOriginalConstructor()
            ->getMock();

        $join = $this->getMockBuilder('SugarQuery_Builder_Join')
            ->setMethods(array('on'))
            ->disableOriginalConstructor()
            ->getMock();

        $subscriptionReg->expects($this->atLeastOnce())->method('getBaseSugarQuery')
            ->willReturn($sugarQuery);

        $subscriptionReg->expects($this->atLeastOnce())->method('getBaseSugarQuery')
            ->willReturn($sugarQuery);

        $subscriptionReg->expects($this->once())->method('eventWhere')
            ->with(
                $this->equalTo($event),
                $this->equalTo($joinWhere),
                $this->equalTo($subscriptionFilter)
            );

        $subscriptionFilter->expects($this->once())->method('filterQuery')
            ->with($this->equalTo($event), $this->equalTo($sugarQuery))
            ->willReturn($userAlias);

        $sugarQuery->expects($this->once())->method('joinTable')
            ->with(
                $this->equalTo('notification_subscription'),
                $this->equalTo(array('team_security' => false, 'joinType' => 'LEFT'))
            )->willReturn($join);
        $sugarQuery->expects($this->once())->method('select')
            ->with(
                $this->logicalAnd(
                    $this->contains(array("{$userAlias}.id", 'user_id')),
                    $this->contains('notification_subscription.carrier_name'),
                    $this->contains('notification_subscription.carrier_option'),
                    $this->contains('notification_subscription.type')
                )
            );

        $sugarQuery->expects($this->once())->method('execute')
            ->willReturn($queryResult);

        $join->expects($this->once())->method('on')
            ->willReturn($joinWhere);

        $joinWhere->expects($this->once())->method('equalsField')
            ->with(
                $this->equalTo('notification_subscription.user_id'),
                $this->equalTo("{$userAlias}.id")
            )->will($this->returnSelf());

        $joinWhere->expects($this->once())->method('equals')
            ->with(
                $this->equalTo('notification_subscription.deleted'),
                $this->equalTo('0')
            )->will($this->returnSelf());

        $listRes = \SugarTestReflection::callProtectedMethod(
            $subscriptionReg,
            'getUsersList',
            array($event, $subscriptionFilter)
        );

        $this->assertEquals($queryResult, $listRes);
    }

    /**
     * @covers ::getBeanEmitterTree
     */
    public function testGetBeanEmitterTree()
    {
        $tree = array(
            'Accounts' => array(
                'event1' => array(
                    'Team' => array(),
                    'AccountsLocalFilter' => array(),
                ),
                'event2' => array(
                    'Team' => array(),
                ),
            ),
            'Contacts' => array(
                'event3' => array(
                    'Team' => array(),
                ),
                'event2' => array(
                    'AssignedToMe' => array(),
                ),
            ),
        );

        $expectEmitterTree = array(
            'event2' => array(
                'Team' => array(),
                'AssignedToMe' => array(),
            ),
            'event3' => array(
                'Team' => array(),
            ),
        );

        $beanEvent = $this->getMock(self::NS_EVENT_BEAN, array('__toString', 'getModuleName'), array('beanEv'));

        $moduleEvent = $this->getMock(
            self::NS_EVENT_MODULE,
            array('__toString', 'getModuleName'),
            array('moduleEvent')
        );

        $contactEmitter = $this->getMock(
            'AccountEmitter',
            array('getEventStrings', 'getEventPrototypeByString'),
            array(),
            '',
            false
        );
        $contactEmitter->expects($this->atLeastOnce())->method('getEventStrings')
            ->willReturn(array('event2', 'event3'));

        $mapContact = array(
            array('event2', $beanEvent),
            array('event3', $beanEvent),
        );
        $contactEmitter->expects($this->exactly(2))->method('getEventPrototypeByString')
            ->will($this->returnValueMap($mapContact));

        $accountEmitter = $this->getMock(
            'AccountEmitter',
            array('getEventStrings', 'getEventPrototypeByString'),
            array(),
            '',
            false
        );
        $accountEmitter->expects($this->atLeastOnce())->method('getEventStrings')
            ->willReturn(array('event1', 'event2'));
        $mapAccount = array(
            array('event1', $moduleEvent),
            array('event2', $beanEvent),
        );
        $accountEmitter->expects($this->exactly(2))->method('getEventPrototypeByString')
            ->will($this->returnValueMap($mapAccount));

        $emitterRegistry = $this->getMock(self::NS_EMITTER_REGISTRY, array('getModuleEmitter', 'getModuleEmitters'));
        $mapEmitters = array(
            array('Accounts', $accountEmitter),
            array('Contacts', $contactEmitter),
        );
        $emitterRegistry->expects($this->exactly(2))->method('getModuleEmitter')
            ->will($this->returnValueMap($mapEmitters));
        $emitterRegistry->expects($this->atLeastOnce())->method('getModuleEmitters')
            ->willReturn(array('Accounts', 'Contacts'));

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getEmitterRegistry'));

        $subscriptionsRegistry->expects($this->atLeastOnce())->method('getEmitterRegistry')
            ->willReturn($emitterRegistry);

        $emitterTree = \SugarTestReflection::callProtectedMethod(
            $subscriptionsRegistry,
            'getBeanEmitterTree',
            array($tree)
        );

        $this->assertEquals($expectEmitterTree, $emitterTree);
    }
}
