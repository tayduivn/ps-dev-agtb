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
     * @covers ::diffBranch
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

        $diff = \SugarTestReflection::callProtectedMethod($sr, 'diffBranch', array($beans, array_values($carriers)));

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
     * @covers getUserConfiguration
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
            array(
                'event_name' => 'event2',
                'filter_name' => 'sf3',
                'user_id' => $userId,
                'carrier_name' => SubscriptionsRegistry::CARRIER_VALUE_DISABLED,
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
                        'sf3' => array()
                    ),
                )
        );

        $expectConfig = $tree;
        $expectConfig[$emitterName]['event1']['sf1'] = array(array('cn1', 'co1'));
        $expectConfig[$emitterName]['event1']['sf2'] = array(array('cn1', 'co1'));
        $expectConfig[$emitterName]['event2']['sf1'] = array(array('cn1', 'co1'));
        $expectConfig[$emitterName]['event2']['sf3'] = SubscriptionsRegistry::CARRIER_VALUE_DISABLED;

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
     * @covers getGlobalConfiguration
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
     * @covers setConfiguration
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
            'moveBeans',
            'diffBranch',
            'mergeDiff',
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
                    'sf2' => array(),
                    'sf3' => array()
                ),
            ),
        );

        $subscriptionsRegistry->expects($this->once())->method('getTree')->willReturn($tree);

        $config = $tree;
        $config['emitter1']['event1']['sf1'] = array(array('cn1', 'co1'), array('cn2', 'co2'));
        $config['emitter2']['event2']['sf2'] = SubscriptionsRegistry::CARRIER_VALUE_DISABLED;
        $config['emitter2']['event2']['sf3'] = SubscriptionsRegistry::CARRIER_VALUE_DEFAULT;

        $subscriptionsRegistry->expects($this->exactly(3))->method('pathToBranch')
            ->with(
                $this->logicalOr($this->equalTo('emitter1'), $this->equalTo('emitter2')),
                $this->logicalOr($this->equalTo('event1'), $this->equalTo('event2')),
                $this->logicalOr($this->equalTo('sf1'), $this->equalTo('sf2'), $this->equalTo('sf3'))
            )->will($this->onConsecutiveCalls(array(0 => 'path1'), array(0 => 'path2'), array(0 => 'path3')));

        $subscriptionsRegistry->expects($this->exactly(3))->method('moveBeans')
            ->with(
                $this->equalTo($beanList),
                $this->logicalOr(
                    $this->equalTo(array(0 => 'path1')),
                    $this->equalTo(array(0 => 'path2')),
                    $this->equalTo(array(0 => 'path3'))
                )
            )->will($this->onConsecutiveCalls('movedBeans1', 'movedBeans2', 'movedBeans3'));

        $subscriptionsRegistry->expects($this->exactly(3))->method('diffBranch')
            ->with(
                $this->logicalOr(
                    $this->equalTo('movedBeans1'),
                    $this->equalTo('movedBeans2'),
                    $this->equalTo('movedBeans3')
                ),
                $this->logicalOr(
                    $this->equalTo(array(array('cn1'), array('cn2'))),
                    $this->equalTo(array()),
                    $this->equalTo(array(array(SubscriptionsRegistry::CARRIER_VALUE_DISABLED)))
                )
            )->will($this->onConsecutiveCalls(
                array('delete' => 'delete1', 'insert' => 'insert1'),
                array('delete' => 'delete2', 'insert' => 'insert2'),
                array('delete' => 'delete3', 'insert' => 'insert3')
            ));

        $subscriptionsRegistry->expects($this->exactly(3))->method('mergeDiff')
            ->with(
                $this->logicalOr(
                    $this->equalTo(array(0 => 'path1', 'user_id' => $userId)),
                    $this->equalTo(array(0 => 'path2', 'user_id' => $userId)),
                    $this->equalTo(array(0 => 'path3', 'user_id' => $userId))
                ),
                $this->logicalOr($this->equalTo('delete1'), $this->equalTo('delete2'), $this->equalTo('delete3')),
                $this->logicalOr($this->equalTo('insert1'), $this->equalTo('insert2'), $this->equalTo('insert3'))
            );

        $subscriptionsRegistry->expects($this->once())->method('deleteConfigBeans')->with($this->equalTo($beanList));

        \SugarTestReflection::callProtectedMethod(
            $subscriptionsRegistry,
            'setConfiguration',
            array($userId, $config, true)
        );
    }

    /**
     * @covers pathToBranch
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

}
