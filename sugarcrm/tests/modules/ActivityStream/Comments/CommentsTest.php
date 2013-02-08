<?php

use SugarTestActivityUtilities as ActivityHelper;
use SugarTestCommentUtilities as CommentHelper;
use SugarTestUserUtilities as UserHelper;

class CommentsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->activity = ActivityHelper::createActivity();
    }

    public function tearDown()
    {
        ActivityHelper::removeAllCreatedActivities();
        CommentHelper::removeAllCreatedComments();
    }

    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = UserHelper::createAnonymousUser();
    }

    public static function tearDownAfterClass()
    {
        UserHelper::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * Tests that the magic method __toString() on a Comment bean is valid.
     * @covers Comment::toJson
     * @group ActivityStream
     */
    public function testToString()
    {
        $comment = CommentHelper::createComment($this->activity);
        $json = $comment->toJson();
        $this->assertInternalType('string', $json);
        $this->assertNotEquals(false, json_decode($json, true));
    }

    /**
     * Tests that saving a comment that the post has already counted does not
     * increment the cached count again.
     * @covers Comment::save
     * @group ActivityStream
     */
    public function testDoubleSaveDoesntUpdateCommentCount()
    {
        $comment = CommentHelper::createComment($this->activity);
        $this->assertEquals(1, $this->activity->comment_count);
        $comment->save();
        $this->assertEquals(1, $this->activity->comment_count);
    }

    /**
     * Tests that saving a comment without a parent post returns false.
     * @covers Comment::save
     * @group ActivityStream
     */
    public function testSave()
    {
        $comment = BeanFactory::getBean('Comments');
        $id = $comment->save();
        $this->assertFalse($id);
    }
}
