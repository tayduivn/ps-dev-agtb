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
 * Test if we respect user preference for email notifications
 */
class GetNotificationRecipientsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if receive_notifications flag is respected for users
     *
     * @param $receiveNotification - Receive notification user preference
     * @param $expectedNumberOfRecords - Number of records returned
     * @dataProvider dataProvider
     */
    public function testGetNotificationRecipients($receiveNotification, $expectedNumberOfRecords)
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->receive_notifications = $receiveNotification;
        $user->save();

        $account = SugarTestAccountUtilities::createAccount();
        $account->assigned_user_id = $user->id;
        $account->save();

        $userList = $account->get_notification_recipients();
        $this->assertEquals($expectedNumberOfRecords, count($userList));
    }

    public static function dataProvider()
    {
        return array(
            array(
                false,
                0
            ),
            array(
                true,
                1
            ),
        );
    }
}
