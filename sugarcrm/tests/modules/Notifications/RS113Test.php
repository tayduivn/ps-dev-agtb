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


/**
 * RS-113: Prepare Notifications Module.
 */
class RS113Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    protected function tearDown()
    {
        SugarTestNotificationUtilities::removeAllCreatedNotifications();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testUnreadForDateCount()
    {
        global $current_user;
        $time = TimeDate::getInstance();
        $de = $time->getNow()->sub(new DateInterval('P1D'));
        $now = $time->getNow();

        $current_count = BeanFactory::getBean('Notifications')->retrieveUnreadCountFromDateEnteredFilter($time->asDb($de));
        $bean = SugarTestNotificationUtilities::createNotification();
        $bean->is_read = 0;
        $bean->date_entered = $time->asDb($now);
        $bean->update_date_entered = true;
        $bean->assigned_user_id = $current_user->id;
        $bean->save();
        $count = BeanFactory::getBean('Notifications')->retrieveUnreadCountFromDateEnteredFilter($time->asDb($de));
        $this->assertEquals($current_count + 1, $count);
    }

    public function testUnreadForUserCount()
    {
        global $current_user;

        $current_count = BeanFactory::getBean('Notifications')->getUnreadNotificationCountForUser();
        $bean = SugarTestNotificationUtilities::createNotification();
        $bean->is_read = 0;
        $bean->assigned_user_id = $current_user->id;
        $bean->save();
        $count = BeanFactory::getBean('Notifications')->getUnreadNotificationCountForUser();
        $this->assertEquals($current_count + 1, $count);
        $count = BeanFactory::getBean('Notifications')->getUnreadNotificationCountForUser($current_user);
        $this->assertEquals($current_count + 1, $count);
    }
}