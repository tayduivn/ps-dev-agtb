<?php

use SugarTestAccountUtilities as AccountHelper;
use SugarTestCommentUtilities as CommentHelper;
use SugarTestActivityUtilities as ActivityHelper;
use SugarTestUserUtilities as UserHelper;

class SubscriptionsTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $user;
    private $record;

    public function setUp()
    {
        $this->user = UserHelper::createAnonymousUser();
        // TODO: Hack to avoid ACLController::checkAccessInternal errors. See
        // https://plus.google.com/101248048527720727791/posts/BNzpE6vwncT?cfem=1.
        $GLOBALS['current_user'] = $this->user;

        $this->record = self::getUnsavedRecord();
    }

    public function tearDown()
    {
        unset($GLOBALS['current_user']);
        UserHelper::removeAllCreatedAnonymousUsers();
    }

    /**
     * @covers Subscription::getSubscribedUsers
     * @group ActivityStream
     */
    public function testGetSubscribedUsers()
    {
        $kls = BeanFactory::getBeanName('Subscriptions');
        $return = $kls::getSubscribedUsers($this->record);
        $this->assertInternalType('array', $return);
        // TODO: Change this assertion to use assertCount after upgrading to
        // PHPUnit 3.6 or above.
        $this->assertEquals(0, count($return));

        $kls::subscribeUserToRecord($this->user, $this->record);
        $return = $kls::getSubscribedUsers($this->record);
        $this->assertInternalType('array', $return);
        // TODO: Change this assertion to use assertCount after upgrading to
        // PHPUnit 3.6 or above.
        $this->assertEquals(1, count($return));
        $this->assertEquals($return[0]['created_by'], $this->user->id);
    }

    /**
     * @covers Subscription::getSubscribedRecords
     * @group ActivityStream
     */
    public function testGetSubscribedRecords()
    {
        $kls = BeanFactory::getBeanName('Subscriptions');
        $return = $kls::getSubscribedRecords($this->user);
        $this->assertInternalType('array', $return);
        // TODO: Change this assertion to use assertCount after upgrading to
        // PHPUnit 3.6 or above.
        $this->assertEquals(0, count($return));

        $kls::subscribeUserToRecord($this->user, $this->record);
        $return = $kls::getSubscribedRecords($this->user);
        $this->assertInternalType('array', $return);
        // TODO: Change this assertion to use assertCount after upgrading to
        // PHPUnit 3.6 or above.
        $this->assertEquals(1, count($return));
        $this->assertEquals($return[0]['parent_id'], $this->record->id);
    }

    /**
     * @covers Subscription::checkSubscription
     * @group ActivityStream
     */
    public function testCheckSubscription()
    {
        $kls = BeanFactory::getBeanName('Subscriptions');
        $return = $kls::checkSubscription($this->user, $this->record);
        $this->assertNull($return, "A subscription shouldn't exist for a new record.");

        $guid = $kls::subscribeUserToRecord($this->user, $this->record);
        $return = $kls::checkSubscription($this->user, $this->record);
        $this->assertEquals($guid, $return);
    }

    /**
     * @covers Subscription::subscribeUserToRecord
     * @group ActivityStream
     */
    public function testSubscribeUserToRecord()
    {
        $kls = BeanFactory::getBeanName('Subscriptions');
        $return = $kls::subscribeUserToRecord($this->user, $this->record);
        // Expect a Subscription bean GUID if we're creating the subscription.
        $this->assertInternalType('string', $return);

        $return = $kls::subscribeUserToRecord($this->user, $this->record);
        // Expect false if we cannot add another subscription for the user.
        $this->assertFalse($return);
    }

    private static function getUnsavedRecord()
    {
        // SugarTestAccountUtilities::createAccount saves the bean, which
        // triggers the OOB subscription logic. For that reason, we create our
        // own record and give it an ID.
        $record = new Account();
        $record->id = "SubscriptionsTest".mt_rand();
        return $record;
    }
}
