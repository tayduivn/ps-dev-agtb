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

    private $list = array(
        array(
            'type' => 'application',
            'emitter_module_name' => null,
            'event_name' => 'application_event1',
            'relation_name' => 'application_relation',
            'carrier_name' => 'Email',
            'carrier_option' => 'email1'
        ),
        array(
            'type' => 'application',
            'emitter_module_name' => null,
            'event_name' => 'application_event2',
            'relation_name' => 'application_relation',
            'carrier_name' => 'Email',
            'carrier_option' => 'email1'
        ),
        array(
            'type' => 'application',
            'emitter_module_name' => null,
            'event_name' => 'application_event1',
            'relation_name' => 'application_relation',
            'carrier_name' => 'SMS',
            'carrier_option' => 'phone'
        ),
        array(
            'type' => 'bean',
            'emitter_module_name' => null,
            'event_name' => 'bean_event1',
            'relation_name' => 'AssignedToMe',
            'carrier_name' => 'SMS',
            'carrier_option' => 'phone'
        ),
        array(
            'type' => 'module',
            'emitter_module_name' => 'Accounts',
            'event_name' => 'account_event1',
            'relation_name' => 'AssignedToMe',
            'carrier_name' => 'SMS',
            'carrier_option' => 'phone'
        ),
        array(
            'type' => 'module',
            'emitter_module_name' => 'Accounts',
            'event_name' => 'account_event1',
            'relation_name' => 'AssignedToMe',
            'carrier_name' => 'Email',
            'carrier_option' => 'email1'
        ),
        array(
            'type' => 'module',
            'emitter_module_name' => 'Accounts',
            'event_name' => 'account_event1',
            'relation_name' => 'Team',
            'carrier_name' => 'Email',
            'carrier_option' => 'email1'
        ),
    );

    private $tree = array(
        'ApplicationEmitter' => array(
            'application_event1' => array(
                'application_relation' => array(
                    'Email' => 'email1',
                    'SMS' => 'phone',
                )
            ),
            'application_event2' => array(
                'application_relation' => array(
                    'Email' => 'email1'
                )
            )
        ),
        'BeanEmitter' => array(
            'bean_event1' => array(
                'AssignedToMe' => array(
                    'SMS' => 'phone',
                )
            )
        ),
        'Accounts' => array(
            'account_event1' => array(
                'AssignedToMe' => array(
                    'SMS' => 'phone',
                    'Email' => 'email1'
                ),
                'Team' => array(
                    'Email' => 'email1'
                ),
            )
        )
    );

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

        $emitterRegistry = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Notification\\EmitterRegistry',
            array('getApplicationEmitter', 'getBeanEmitter', 'getModuleEmitter')
        );

        $method = $emitterRegistry->expects($this->once())->method($expectedMethod)->willReturn($expectedEmitter);
        if ($expectedParam) {
            call_user_func_array(array($method, 'with'), $expectedParam);
        }

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getRegistry'));
        $subscriptionsRegistry->expects($this->atLeastOnce())->method('getRegistry')->willReturn($emitterRegistry);

        $res = \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'getEmitter', array($bean));

        $this->assertEquals($expectedEmitter, $res);
    }

    /**
     * @expectedException \LogicException
     * @covers ::getEmitter
     */
    public function testGetEmitterException()
    {
        $bean = $this->getMockBuilder('NotificationCenterSubscription')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $bean->type = 'UnsupportedType';

        $subscriptionsRegistry = new SubscriptionsRegistry();

        \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'getEmitter', array($bean));
    }

    /**
     * @covers ::deleteOther
     */
    public function testDeleteOther()
    {
        $ids = array('id1', 'id2', 'id3');

        $where = $this->getMock('SugarQuery_Builder_Andwhere', array('notIn'), array(), '', false);
        $where->expects($this->once())->method('notIn')->with($this->equalTo('id'), $this->equalTo($ids));

        $query = $this->getMock('SugarQuery', array('where'), array(), '', false);
        $query->expects($this->once())->method('where')->willReturn($where);

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getSugarQuery', 'getBeans'));
        $subscriptionsRegistry->expects($this->once())->method('getSugarQuery')->willReturn($query);

        $beans = array();

        foreach ($ids as $id) {
            $bean = $this->getMockBuilder('NotificationCenterSubscription')
                ->disableOriginalConstructor()
                ->setMethods(array('mark_deleted'))
                ->getMock();
            $bean->expects($this->once())->method('mark_deleted')->with($this->equalTo($id));
            $bean->id = $id;
            $beans[] = $bean;
        }
        $subscriptionsRegistry->expects($this->once())->method('getBeans')
            ->willReturn($beans)
            ->with($this->equalTo($query), $this->logicalAnd($this->contains('id'), $this->contains('deleted')));

        \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'deleteOther', array($ids));
    }

    /**
     * @expectedException \LogicException
     * @covers ::deleteOther
     */
    public function testDeleteOtherEmpty()
    {
        $subscriptionsRegistry = new SubscriptionsRegistry();
        \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'deleteOther', array(array()));
    }

    /**
     * @covers ::getGlobalConfiguration
     */
    public function testGetGlobalConfiguration()
    {
        $beans = array();
        foreach ($this->list as $row) {
            $bean = $this->getMockBuilder('NotificationCenterSubscription')
                ->disableOriginalConstructor()
                ->setMethods(array('mark_deleted'))
                ->getMock();
//            $bean = \BeanFactory::newBean('NotificationCenterSubscriptions');
            $bean->fromArray($row);
            $beans[] = $bean;
        }

        $query = $this->getMock('SugarQuery', array(), array(), '', false);

        $subscriptionsRegistry = $this->getMock(
            self::NS_SUBSCRIPTIONS_REGISTRY,
            array('getBeans', 'getSugarQuery', 'getEmitter')
        );
        $subscriptionsRegistry->expects($this->atLeastOnce())->method('getSugarQuery')->willReturn($query);
        $subscriptionsRegistry->expects($this->any())->method('getBeans')->willReturn($beans);

        $subscriptionsRegistry->expects($this->any())->method('getEmitter')
            ->will($this->returnCallback(function ($bean) {
                switch ($bean->type) {
                    case 'application':
                        return 'ApplicationEmitter';
                    case 'bean':
                        return 'BeanEmitter';
                    case 'module':
                        return $bean->emitter_module_name;
                }
            }));

        $res = $subscriptionsRegistry->getGlobalConfiguration();
        $this->assertEquals($this->tree, $res);
    }

    public function getBeanFieldLists()
    {
        return array(
            array(array('id', 'deleted', 'field3'), array('id', 'deleted', 'field3')),
            array(
                null,
                array(
                    'type',
                    'emitter_module_name',
                    'event_name',
                    'relation_name',
                    'carrier_name',
                    'carrier_option'
                )
            )
        );
    }

    /**
     * @covers ::getBeans
     * @dataProvider getBeanFieldLists
     */
    public function testGetBeans($fields, $fieldsExp)
    {
        $query = $this->getMock('SugarQuery', array(), array(), '', false);
        $beansExp = array('Some', 'list', 'of', 'Beans');

        $bean = $this->getMockBuilder('NotificationCenterSubscription')
            ->disableOriginalConstructor()
            ->setMethods(array('fetchFromQuery'))
            ->getMock();
        $bean->expects($this->once())->method('fetchFromQuery')->willReturn($beansExp)->with(
            $this->equalTo($query),
            $this->equalTo($fieldsExp)
        );

        $registry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getNewBean'));
        $registry->expects($this->atLeastOnce())->method('getNewBean')->willReturn($bean);

        $beansRes = \SugarTestReflection::callProtectedMethod($registry, 'getBeans', array($query, $fields));

        $this->assertEquals($beansExp, $beansRes);
    }

    public function existingVariants()
    {
        return array(
            array(true),
            array(false)
        );
    }

    /**
     * @dataProvider existingVariants
     * @covers ::checkExisting
     */
    public function testCheckExisting($exists)
    {
        $beanForCheck = $this->getMockBuilder('\NotificationCenterSubscription')
            ->disableOriginalConstructor()
            ->setMethods(array('fetchFromQuery', 'retrieve'))
            ->getMock();
        $beanForCheck->beanForCheck = 'beanForCheck';

        $beanForFound = $this->getMockBuilder('\NotificationCenterSubscription')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $beanForFound->id = 'id-' . microtime();
        $beanForFound->beanForFound = 'beanForFound';

        $where = $this->getMock('SugarQuery_Builder_Andwhere', array('equals'), array(), '', false);

        $query = $this->getMock('SugarQuery', array('where'), array(), '', false);

        $registry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getSugarQuery'));
        $fields = array(
            'type',
            'emitter_module_name',
            'event_name',
            'relation_name',
            'carrier_name',
        );

        $whereConsecutive = array();
        foreach ($fields as $fld) {
            $beanForCheck->$fld = "Fld-$fld-" . microtime();
            $whereConsecutive[] = array($this->equalTo($fld), $this->equalTo($beanForCheck->$fld));
        }

        $beanForFound->type = $beanForCheck->type;
        $whereExp = $where->expects($this->exactly(count($fields)))->method('equals');
        call_user_func_array(array($whereExp, 'withConsecutive'), $whereConsecutive);

        $query->expects($this->exactly(count($fields)))->method('where')->willReturn($where);

        $beanForCheck->expects($this->once())->method('fetchFromQuery')
            ->willReturn($exists ? array($beanForFound->id => $beanForFound) : array())
            ->with($this->equalTo($query));

        $beanForCheck->expects($exists ? $this->once() : $this->never())->method('retrieve')->with(
            $this->equalTo($beanForFound->id)
        );

        $registry->expects($this->once())->method('getSugarQuery')->willReturn($query);

        \SugarTestReflection::callProtectedMethod($registry, 'checkExisting', array($beanForCheck));

        if (!$exists) {
            $this->assertTrue($beanForCheck->new_with_id);
            $this->assertNotEmpty($beanForCheck->id);
        }
    }

    /**
     * @covers ::setGlobalConfiguration
     * @group ft1
     */
    public function testSetGlobalConfiguration()
    {
        $beans = array();
        $ids = array();
        $list = $this->list;
        $listSize = count($list);

        array_walk($list, function (&$arr) {
            ksort($arr);
        });
        $list = array_map('json_encode', $list);

        $registry = $this->getMock(
            self::NS_SUBSCRIPTIONS_REGISTRY,
            array('decodeEmitter', 'getNewBean', 'deleteOther', 'checkExisting')
        );

        $fields = array(
            'type',
            'emitter_module_name',
            'event_name',
            'relation_name',
            'carrier_name',
            'carrier_option'
        );

        for ($i = 0; $i < count($this->list); $i++) {
            $beans[$i] = $this->getMockBuilder('\NotificationCenterSubscription')
                ->disableOriginalConstructor()
                ->setMethods(array('save'))
                ->getMock();
            $beans[$i]->expects($this->once())->method('save');
            $ids[] = $beans[$i]->id = 'id-' . microtime();
        }

        $registry->expects($this->exactly($listSize))->method('checkExisting')->with(
            $this->callback(function ($bean) use (&$list, $fields) {
                $arr = array();
                foreach ($fields as $field) {
                    $arr[$field] = $bean->$field;
                }
                ksort($arr);

                $key = array_search(json_encode($arr), $list);
                if (false === $key && count($list) > 0) {
                    return false;
                } else {
                    unset($list[$key]);
                    return true;
                }
            })
        );

        $registry->expects($this->exactly($listSize))->method('getNewBean')
            ->will(call_user_func_array(array($this, 'onConsecutiveCalls'), $beans));

        $registry->expects($this->once())->method('deleteOther')
            ->with($this->equalTo($ids));

        $map = array(
            array('ApplicationEmitter', array('type' => 'application', 'emitter_module_name' => null)),
            array('BeanEmitter', array('type' => 'bean', 'emitter_module_name' => null)),
            array('Accounts', array('type' => 'module', 'emitter_module_name' => 'Accounts')),
        );
        $registry->expects($this->exactly(count($this->list)))->method('decodeEmitter')
            ->will($this->returnValueMap($map));

        $registry->setGlobalConfiguration($this->tree);

        $this->assertEmpty($list);
    }
}
