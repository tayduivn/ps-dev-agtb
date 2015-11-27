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

namespace Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter;

use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event as ReminderEvent;
use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as AppEvent;
use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event as BeanEvent;
use Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder
 */
class ReminderTest extends \Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @covers ::filterQuery
     */
    public function testFilterQuery()
    {
        $user1 = \SugarTestUserUtilities::createAnonymousUser();
        $user2 = \SugarTestUserUtilities::createAnonymousUser();
        $call = \SugarTestCallUtilities::createCall();
        \SugarTestCallUtilities::addCallUserRelation($call->id, $user1->id);
        \SugarTestCallUtilities::addCallUserRelation($call->id, $user2->id);

        $event = new ReminderEvent();
        $event->setBean($call)->setUser($user1);
        $query = new \SugarQuery();

        $filter = new Reminder();
        $userAlias = $filter->filterQuery($event, $query);
        $query->select(array("{$userAlias}.id"));

        $list = $query->execute();

        $this->assertContains(array('id' => $user1->id), $list);
        $this->assertCount(1, $list);
        $this->assertNotContains(array('id' => $user2->id), $list);
    }
}
