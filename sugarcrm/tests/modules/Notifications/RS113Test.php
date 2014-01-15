<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
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