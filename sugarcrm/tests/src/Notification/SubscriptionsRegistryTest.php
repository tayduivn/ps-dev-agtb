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

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry
 */
class SubscriptionsRegistryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    const NS_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\SubscriptionsRegistry';

    private $ids = array();

    /**
     * @covers ::getSugarQuery
     */
    public function testGetSugarQuery()
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
