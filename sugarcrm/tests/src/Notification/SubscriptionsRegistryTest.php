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

namespace Sugarcrm\SugarcrmTests\Notification;

use Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry;

require_once 'modules/Accounts/Emitter.php';

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry
 */
class SubscriptionsRegistryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    const NS_SUBSCRIPTIONS_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\SubscriptionsRegistry';

    const NS_APPLICATION_EMITTER = 'Sugarcrm\\Sugarcrm\\Notification\\ApplicationEmitter\\Emitter';

    const NS_APPLICATION_EVENT = 'Sugarcrm\\Sugarcrm\\Notification\\ApplicationEmitter\\Event';

    const NS_BEAN_EVENT = 'Sugarcrm\\Sugarcrm\\Notification\\BeanEmitter\\Event';

    const NS_SF_ASSIGNED_TO_ME = 'Sugarcrm\\Sugarcrm\\Notification\\SubscriptionFilter\\AssignedToMe';

    const NS_SF_APPLICATION = 'Sugarcrm\\Sugarcrm\\Notification\\SubscriptionFilter\\Application';

    private $ids = array();

    public function testGetTree()
    {
        $appEmitter = $this->getMock(self::NS_APPLICATION_EMITTER, array(
            'getEventStrings', 'getEventPrototypeByString'));
        $appEmitterEvent = array(
            'application_event1' => $this->getMock(self::NS_APPLICATION_EVENT, array(), array('application_event1'))
        );
        $appEmitter->expects($this->once())->method('getEventStrings')->willReturn(array_keys($appEmitterEvent));

        $appEmitter->expects($this->exactly(1))->method('getEventPrototypeByString')
            ->will($this->returnValueMap(array(array('application_event1', $appEmitterEvent['application_event1']))));

        $accountEmitter = $this->getMock('\\AccountEmitter', array(
            'getEventStrings', 'getEventPrototypeByString'), array(), '', false);

        $accountEvent = $this->getMock(self::NS_BEAN_EVENT, array('setBean'), array('account-event1'));
        $accountEvent->expects($this->once())->method('setBean')->with($this->isInstanceOf('\\Account'));

        $accountEmitterEvent = array(
            'account-event1' => $accountEvent
        );
        $accountEmitter->expects($this->once())->method('getEventStrings')
            ->willReturn(array_keys($accountEmitterEvent));
        $accountEmitter->expects($this->exactly(1))->method('getEventPrototypeByString')
            ->will($this->returnValueMap(array(array('account-event1', $accountEvent))));

        $emitters = array($appEmitter, 'Accounts' => $accountEmitter);

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array(
            'getEmitters', 'getSubscriptionFilters'));
        $subscriptionsRegistry->expects($this->once())->method('getEmitters')->willReturn($emitters);


        $sfAssignedToMe = $this->getMock(self::NS_SF_ASSIGNED_TO_ME, array('supports', '__toString'));
        $assignedToMeMap = array(
            array($accountEvent, true),
            array($appEmitterEvent['application_event1'], false),
        );
        $sfAssignedToMe->expects($this->atLeastOnce())->method('supports')
            ->will($this->returnValueMap($assignedToMeMap));
        $sfAssignedToMe->expects($this->atLeastOnce())->method('__toString')
            ->willReturn('AssignedToMe');

        $sfApp = $this->getMock(self::NS_SF_APPLICATION, array('supports', '__toString'));
        $sfAppMap = array(
            array($accountEvent, false),
            array($appEmitterEvent['application_event1'], true),
        );
        $sfApp->expects($this->atLeastOnce())->method('supports')
            ->will($this->returnValueMap($sfAppMap));
        $sfApp->expects($this->atLeastOnce())->method('__toString')
            ->willReturn('Application');

        $subscriptionsRegistry->expects($this->once())->method('getSubscriptionFilters')
            ->willReturn(array($sfAssignedToMe, $sfApp));


        $resTree = \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'getTree');

        $expectedTree = array(
            'ApplicationEmitter' => array('application_event1' => array('Application' => array())),
            'Accounts' => array('account-event1' => array('AssignedToMe' => array()))
        );

        $this->assertEquals($expectedTree, $resTree);
    }

    public function diffVariants()
    {
        return array(
            array(
                array(
                    array(
                        'carrier_name' => 'carrierForDelName1',
                        'carrier_option' => 'carrierForDelOption1'
                    ),
                    array(
                        'carrier_name' => 'carrierForDelName2',
                        'carrier_option' => 'carrierForDelOption2'
                    ),
                    array(
                        'carrier_name' => 'carrierForDelName3',
                        'carrier_option' => 'carrierForDelOption3'
                    )
                ),
                array(
                    array('carrierForInsertName1', 'carrierForInsertOption1'),
                    array('carrierForInsertName2', 'carrierForInsertOption2'),
                    array('carrierForInsertName3', 'carrierForInsertOption3'),
                    array('carrierForInsertName4', 'carrierForInsertOption4')
                )
            ),
            array(
                array(
                    array(
                        'carrier_name' => 'carrierForDelName1',
                        'carrier_option' => 'carrierForDelOption1'
                    ),
                    array(
                        'carrier_name' => 'carrierForDelName2',
                        'carrier_option' => 'carrierForDelOption2'
                    ),
                    array(
                        'carrier_name' => 'carrierForDelName3',
                        'carrier_option' => 'carrierForDelOption3'
                    ),
                    array(
                        'carrier_name' => 'carrierForDelName4',
                        'carrier_option' => 'carrierForDelOption4'
                    )
                ),
                array(
                    array('carrierForInsertName1', 'carrierForInsertOption1'),
                    array('carrierForInsertName2', 'carrierForInsertOption2'),
                )
            ),
        );
    }

    /**
     * @dataProvider diffVariants
     * @param $beansForDeleteArr
     * @param $carriersForInsert
     */
    public function testDiffBranch($beansForDeleteArr, $carriersForInsert)
    {
        $user = \SugarTestUserUtilities::createAnonymousUser();

        $path = array(
            'type' => 'appication',
            'emitter_module_name' => null,
            'event_name' => 'event1',
            'filter_name' => 'sf1',
            'user_id' => $user->id,
        );

        $beansForDelete = array();
        foreach ($beansForDeleteArr as $key => $beanArr) {
            $beanArr['user_id'] = $user->id;
            $bean = \BeanFactory::newBean('NotificationCenterSubscriptions');
            $bean->fromArray($beanArr);
            $bean->save();
            $beansForDelete[$key] = $bean;
            $this->ids[] = $bean->id;
        }

        $subscriptionsRegistry = new SubscriptionsRegistry();

        \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'mergeDiff', array(
            $path, $beansForDelete, $carriersForInsert));

        $checkInsertedBean = \BeanFactory::newBean('NotificationCenterSubscriptions');
        $checkInsertedQuery = new \SugarQuery();
        $checkInsertedQuery->select('id');
        $checkInsertedQuery->from($checkInsertedBean);

        $conditionOr = $checkInsertedQuery->where()->queryAnd()
            ->in('id', $this->ids)
            ->queryOr();

        foreach ($beansForDeleteArr as $key => $beanArr) {
            $conditionOr->queryAnd()
                ->equals('carrier_name', $beanArr['carrier_name'])
                ->equals('carrier_option', $beanArr['carrier_option']);
        }

        $this->assertCount(0, $checkInsertedQuery->execute(), 'Expected all old beans will be updated or deleted');

        $checkInsertedBean = \BeanFactory::newBean('NotificationCenterSubscriptions');
        $checkInsertedQuery = new \SugarQuery();
        $checkInsertedQuery->from($checkInsertedBean);
        $checkInsertedQuery->where()->equals('user_id', $user->id);
        $insertedCarriers = $checkInsertedQuery->execute();

        $this->assertCount(count($carriersForInsert), $insertedCarriers, 'Check is same count inserted Carriers');
        foreach ($insertedCarriers as $insertedCarrier) {
            $baseArr = array($insertedCarrier['carrier_name'], $insertedCarrier['carrier_option']);
            $this->assertContains($baseArr, $carriersForInsert);
            $this->ids[] = $insertedCarrier['id'];
        }
    }

    public function testGetNewBaseBean()
    {
        $subscriptionsRegistry = new SubscriptionsRegistry();
        $bean = \SugarTestReflection::callProtectedMethod($subscriptionsRegistry, 'getNewBaseBean');

        $this->assertInstanceOf('NotificationCenterSubscription', $bean);
        $this->assertTrue($bean->new_with_id);
        $this->assertNotEmpty($bean->id);
        $this->assertTrue(is_string($bean->id));
    }

    public function testGetSugarQueryGlobal()
    {
        $beanUser = \BeanFactory::newBean('NotificationCenterSubscriptions');
        $beanUser->user_id = 'some-user-id';
        $beanUser->save();
        $this->ids[] = $beanUser->id;

        $beanGlobal = \BeanFactory::newBean('NotificationCenterSubscriptions');
        $beanGlobal->save();
        $this->ids[] = $beanGlobal->id;

        $registry = new SubscriptionsRegistry();
        $query = \SugarTestReflection::callProtectedMethod($registry, 'getSugarQuery');
        $query->where()->in('id', array($beanGlobal->id, $beanUser->id));

        $res = $query->execute();
        $this->assertCount(1, $res);
        $this->assertEquals($beanGlobal->id, $res[0]['id']);
    }

    public function testGetSugarQueryUser()
    {
        $beanUser = \BeanFactory::newBean('NotificationCenterSubscriptions');
        $beanUser->user_id = 'some-user-id';
        $beanUser->save();
        $this->ids[] = $beanUser->id;

        $beanGlobal = \BeanFactory::newBean('NotificationCenterSubscriptions');
        $beanGlobal->save();
        $this->ids[] = $beanGlobal->id;

        $registry = new SubscriptionsRegistry();
        $query = \SugarTestReflection::callProtectedMethod($registry, 'getSugarQuery', array($beanUser->user_id));
        $query->where()->in('id', array($beanGlobal->id, $beanUser->id));

        $res = $query->execute();
        $this->assertCount(1, $res);
        $this->assertEquals($beanUser->id, $res[0]['id']);
    }

    public function testisNullableUserId()
    {
        $bean = \BeanFactory::newBean('NotificationCenterSubscriptions');

        $varDef = $bean->getFieldDefinition('user_id');

        $isNullable = \SugarTestReflection::callProtectedMethod($bean->db, 'isNullable', array($varDef));
        $this->assertTrue($isNullable);
    }

    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('current_user');
        \SugarTestHelper::setUp('beanList');
        \SugarTestHelper::setUp('beanFiles');
        \SugarTestHelper::setUp('moduleList');
        $this->ids = array();
    }

    protected function tearDown()
    {
        $this->clearCreated();
        \SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    private function clearCreated()
    {
        if ($this->ids) {
            $table = \BeanFactory::newBean('NotificationCenterSubscriptions')->table_name;
            $qr = "DELETE FROM {$table} WHERE id in('" . implode("', '", $this->ids) . "')";
            $GLOBALS['db']->query($qr);
        }
    }
}
