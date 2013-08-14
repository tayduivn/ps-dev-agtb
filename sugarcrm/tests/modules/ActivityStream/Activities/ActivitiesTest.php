<?php
/**
 * @group ActivityStream
 */
class ActivitiesTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $activity;

    public function setUp()
    {
        $this->markTestIncomplete('Needs to be fixed by ABE team.');
        parent::setUp();
        $this->activity = SugarTestActivityUtilities::createActivity();
    }

    public function tearDown()
    {
        SugarTestActivityUtilities::removeAllCreatedActivities();
        SugarTestCommentUtilities::removeAllCreatedComments();
        parent::tearDown();
    }

    /**
     * Tests that modifying a post does not modify the last comment associated
     * with the post.
     * @covers Activity
     */
    public function testThatTouchingAnActivityDoesNotModifyLastComment()
    {
        SugarTestCommentUtilities::createComment($this->activity);

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
     */
    public function testAddComment()
    {
        $comment = SugarTestCommentUtilities::createComment($this->activity);
        $this->assertInternalType('string', $comment->id);
        $this->assertEquals($comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(1, $this->activity->comment_count);
        $this->assertEquals((string)$comment, $this->activity->last_comment);
    }

    /**
     * For an unsaved activity, adding a comment should return false.
     * @covers Activity::addComment
     */
    public function testAddComment2()
    {
        $record = SugarTestActivityUtilities::createUnsavedActivity();
        $comment = SugarTestCommentUtilities::createComment($record);
        $this->assertFalse($record->addComment($comment));
    }

    /**
     * addComment should only work for comments which have a parent of the
     * current activity.
     * @covers Activity::addComment
     */
    public function testAddComment3()
    {
        $record = SugarTestActivityUtilities::createActivity();
        $record2 = SugarTestActivityUtilities::createActivity();
        $comment = SugarTestCommentUtilities::createComment($record2);
        $this->assertFalse($record->addComment($comment));
    }

    /**
     * For a saved activity and comment, deleting the comment should delete it,
     * and decrements the counter.
     * @covers Activity::deleteComment
     */
    public function testDeleteComment()
    {
        $comment = SugarTestCommentUtilities::createComment($this->activity);
        $this->activity->deleteComment($comment->id);

        $this->assertNotEquals($comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(0, $this->activity->comment_count);
    }

    /**
     * On a saved post with no comments, deleting an arbitrary comment should do
     * nothing.
     * @covers Activity::deleteComment
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
     */
    public function testDeleteComment3()
    {
        $record = SugarTestActivityUtilities::createUnsavedActivity();
        $orig_last_comment = $record->last_comment_bean;
        $record->deleteComment('foo');

        $this->assertEquals($orig_last_comment, $record->last_comment_bean);
        $this->assertEquals(0, $record->comment_count);
    }

    /**
     * For a saved activity with multiple comment, deleting the last comment
     * should delete it, and decrements the counter.
     * @covers Activity::deleteComment
     */
    public function testDeleteComment4()
    {
        $first_comment = SugarTestCommentUtilities::createComment($this->activity);
        $second_comment = SugarTestCommentUtilities::createComment($this->activity);
        $this->activity->deleteComment($second_comment->id);

        $this->assertEquals($first_comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(1, $this->activity->comment_count);
    }

    /**
     * For a saved activity with multiple comment, deleting a non-last comment
     * should delete it, and decrements the counter, but not change the last
     * comment.
     * @covers Activity::deleteComment
     */
    public function testDeleteComment5()
    {
        $first_comment = SugarTestCommentUtilities::createComment($this->activity);
        $second_comment = SugarTestCommentUtilities::createComment($this->activity);
        $this->activity->deleteComment($first_comment->id);

        $this->assertEquals($second_comment->id, $this->activity->last_comment_bean->id);
        $this->assertEquals(1, $this->activity->comment_count);
    }

    /**
     * For an activity without an ID, adding a comment should return false.
     * @covers Activity::deleteComment
     */
    public function testDeleteComment6()
    {
        $comment = SugarTestCommentUtilities::createComment($this->activity);
    }

    /**
     * Test that data and last_comment are valid JSON when getting them from the
     * bean.
     * @covers Activity
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

        $comment = SugarTestCommentUtilities::createComment($this->activity);
        $this->assertInternalType('string', $this->activity->last_comment);
        $this->assertNotEquals(false, json_decode($this->activity->last_comment, true));
    }

    /**
     * @covers Activity::processPostSubscription
     */
    public function testProcessPostSubscription()
    {
        $relationshipStub = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->getMock();
        $relationshipStub->expects($this->once())
            ->method('add');

        $stub = $this->getMock(BeanFactory::getObjectName('Activities'));
        $stub->expects($this->once())
            ->method('load_relationship')
            ->with('activities_teams')
            ->will($this->returnValue(true));
        $stub->activities_teams = $relationshipStub;

        SugarTestReflection::callProtectedMethod($stub, 'processPostSubscription', array());
    }
}
