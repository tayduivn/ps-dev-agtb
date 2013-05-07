<?php

use SugarTestAccountUtilities as AccountHelper;
use SugarTestCommentUtilities as CommentHelper;
use SugarTestActivityUtilities as ActivityHelper;
use SugarTestUserUtilities as UserHelper;

class ActivitiesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->markTestIncomplete('Needs to be fixed by ABE team.');
        $this->activity = ActivityHelper::createActivity();
        $this->activityClass = get_class($this->activity);
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
        AccountHelper::removeAllCreatedAccounts();
        UserHelper::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * Tests that modifying a post does not modify the last comment associated
     * with the post.
     * @covers Activity
     * @group ActivityStream
     */
    public function testThatTouchingAnActivityDoesNotModifyLastComment()
    {
        CommentHelper::createComment($this->activity);

        $count = $this->activity->comment_count;
        $last = $this->activity->last_comment;
        $bean = $this->activity->last_comment_bean;
        $this->activity->save();

        $this->assertEquals($count, $this->activity->comment_count);
        $this->assertEquals($last, $this->activity->last_comment);
        $this->assertEquals($bean, $this->activity->last_comment_bean);
    }

    /**
     * For a saved activity, adding a comment should return the comment object.
     * @covers Activity::addComment
     * @group ActivityStream
     */
    public function testAddComment()
    {
        $comment = CommentHelper::createComment($this->activity);
        $this->assertInternalType('string', $comment->id);
        $this->assertEquals($comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(1, $this->activity->comment_count);
        $this->assertEquals((string)$comment, $this->activity->last_comment);
    }

    /**
     * For an unsaved activity, adding a comment should return false.
     * @covers Activity::addComment
     * @group ActivityStream
     */
    public function testAddComment2()
    {
        $record = ActivityHelper::createUnsavedActivity();
        $comment = CommentHelper::createComment($record);
        $this->assertFalse($record->addComment($comment));
    }

    /**
     * addComment should only work for comments which have a parent of the
     * current activity.
     * @covers Activity::addComment
     * @group ActivityStream
     */
    public function testAddComment3()
    {
        $record = ActivityHelper::createActivity();
        $record2 = ActivityHelper::createActivity();
        $comment = CommentHelper::createComment($record2);
        $this->assertFalse($record->addComment($comment));
    }

    /**
     * For a saved activity and comment, deleting the comment should delete it,
     * and decrements the counter.
     * @covers Activity::deleteComment
     * @group ActivityStream
     */
    public function testDeleteComment()
    {
        $comment = CommentHelper::createComment($this->activity);
        $this->activity->deleteComment($comment->id);

        $this->assertNotEquals($comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(0, $this->activity->comment_count);
    }

    /**
     * On a saved post with no comments, deleting an arbitrary comment should do
     * nothing.
     * @covers Activity::deleteComment
     * @group ActivityStream
     */
    public function testDeleteComment2()
    {
        $orig_last_comment = $this->activity->last_comment_bean;
        $this->activity->deleteComment('foo');

        $this->assertEquals($orig_last_comment, $this->activity->last_comment_bean);
        $this->assertEquals(0, $this->activity->comment_count);
    }

    /**
     * On an unsaved post, deleting a comment should do nothing.
     * @covers Activity::deleteComment
     * @group ActivityStream
     */
    public function testDeleteComment3()
    {
        $record = ActivityHelper::createUnsavedActivity();
        $orig_last_comment = $record->last_comment_bean;
        $record->deleteComment('foo');

        $this->assertEquals($orig_last_comment, $record->last_comment_bean);
        $this->assertEquals(0, $record->comment_count);
    }

    /**
     * For a saved activity with multiple comment, deleting the last comment
     * should delete it, and decrements the counter.
     * @covers Activity::deleteComment
     * @group ActivityStream
     */
    public function testDeleteComment4()
    {
        $first_comment = CommentHelper::createComment($this->activity);
        $second_comment = CommentHelper::createComment($this->activity);
        $this->activity->deleteComment($second_comment->id);

        $this->assertEquals($first_comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(1, $this->activity->comment_count);
    }

    /**
     * For a saved activity with multiple comment, deleting a non-last comment
     * should delete it, and decrements the counter, but not change the last
     * comment.
     * @covers Activity::deleteComment
     * @group ActivityStream
     */
    public function testDeleteComment5()
    {
        $first_comment = CommentHelper::createComment($this->activity);
        $second_comment = CommentHelper::createComment($this->activity);
        $this->activity->deleteComment($first_comment->id);

        $this->assertEquals($second_comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(1, $this->activity->comment_count);
    }

    /**
     * For an activity without an ID, adding a comment should return false.
     * @covers Activity::deleteComment
     * @group ActivityStream
     */
    public function testDeleteComment6()
    {
        $comment = CommentHelper::createComment($this->activity);
    }

    /**
     * Test that data and last_comment are valid JSON when getting them from the
     * bean.
     * @covers Activity
     * @group ActivityStream
     */
    public function testValidJson()
    {
        $this->assertInternalType('string', $this->activity->data);
        $this->assertNotEquals(false, json_decode($this->activity->data, true));

        $this->activity->retrieve($this->activity->id);
        $this->assertInternalType('string', $this->activity->data);
        $this->assertNotEquals(false, json_decode($this->activity->data, true));
        $this->assertInternalType('string', $this->activity->last_comment);
        $this->assertNotEquals(false, json_decode($this->activity->last_comment, true));

        $comment = CommentHelper::createComment($this->activity);
        $this->assertInternalType('string', $this->activity->last_comment);
        $this->assertNotEquals(false, json_decode($this->activity->last_comment, true));
    }
}
