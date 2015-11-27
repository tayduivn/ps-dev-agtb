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

namespace Sugarcrm\SugarcrmTestsUnit\Notification\SubscriptionFilter;

use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event as ReminderEvent;
use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as AppEvent;
use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event as BeanEvent;
use Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder
 */
class ReminderTest extends \PHPUnit_Framework_TestCase
{
    public function events()
    {
        return array(
            array(new ReminderEvent(), true),
            array(new AppEvent('AppName'), false),
            array(new BeanEvent('BeanEvent'), false)
        );
    }
    /**
     * @covers ::supports
     * @dataProvider events
     */
    public function testSupports($event, $expects)
    {
        $filter = new Reminder();

        $isSupports = $filter->supports($event);

        $this->assertEquals($expects, $isSupports);
    }
}
