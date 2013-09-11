<?php
/**
 * @group ActivityStream
 */
class ActivitiesTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $activity;

    public function setUp()
    {
        parent::setUp();
        $this->activity = SugarTestActivityUtilities::createActivity();
    }

    public function tearDown()
    {
        //restore the BeanFactory name
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        SugarTestReflection::setProtectedValue($activity, 'beanFactoryClass', 'BeanFactory');

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
        $this->assertEquals($comment->toJson(), $this->activity->last_comment);
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
        $relationshipStub = $this->getMockRelationship();;
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

    public static function dataProvider_TestGetData()
    {
        return array(array('String'), array('Array'));
    }

    /**
     * @covers Activity::getDataString
     * @covers Activity::getDataArray
     * @dataProvider dataProvider_TestGetData
     */
    public function testGetData($format)
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $testData = array(
            'String' => '{"test":123}',
            'Array' => array('test' => 123),
        );

        foreach ($testData as $data) {
            $activity->data = $data;
            $result = SugarTestReflection::callProtectedMethod($activity, 'getData'.$format);
            $this->assertEquals($result, $testData[$format]);
        }
    }

    /**
     * @covers Activity::getParentBean
     */
    public function testGetParentBean_NullParentType_ReturnsNull()
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->parent_type = null;
        $result = SugarTestReflection::callProtectedMethod($activity, 'getParentBean');
        $this->assertEquals(null, $result, "Should return null if parent type is null");
    }

    /**
     * @covers Activity::getParentBean
     */
    public function testGetParentBean_NullParentId_RetrievesEmptyBean()
    {
        $parentType = 'FooBean';
        $mockBean = array('module' => $parentType);

        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->parent_type = $parentType;
        $activity->parent_id = null;

        $bf = $this->mockBeanFactoryOnActivity($activity, array('getBean'));
        $bf::staticExpects($this->once())
            ->method('getBean')
            ->will($this->returnValue($mockBean));

        SugarTestReflection::setProtectedValue($activity, 'beanFactoryClass', get_class($bf));
        $result = SugarTestReflection::callProtectedMethod($activity, 'getParentBean');
        $this->assertEquals($mockBean, $result, "Should return empty bean");
    }

    /**
     * @covers Activity::getParentBean
     */
    public function testGetParentBean_DeleteActivity_RetrievesBeanDisablingRowSecurity()
    {
        $parentType = 'FooBean';
        $parentId = '123';
        $mockBean = array('module' => $parentType, 'id' => $parentId, 'deleted' => 1);

        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->parent_type = $parentType;
        $activity->parent_id = $parentId;
        $activity->activity_type = 'delete';

        $bf = $this->mockBeanFactoryOnActivity($activity, array('retrieveBean'));
        $bf::staticExpects($this->once())
            ->method('retrieveBean')
            ->with($this->equalTo($parentType),
                $this->equalTo($parentId),
                $this->equalTo(array('disable_row_level_security' => true)),
                $this->equalTo(false))
            ->will($this->returnValue($mockBean));

        $result = SugarTestReflection::callProtectedMethod($activity, 'getParentBean');
        $this->assertEquals($mockBean, $result, "Should return deleted bean");
    }

    /**
     * @covers Activity::getParentBean
     */
    public function testGetParentBean_NonDeleteActivity_RetrievesBean()
    {
        $parentType = 'FooBean';
        $parentId = '123';
        $mockBean = array('module' => $parentType, 'id' => $parentId);

        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->parent_type = $parentType;
        $activity->parent_id = $parentId;
        $activity->activity_type = 'update';

        $bf = $this->mockBeanFactoryOnActivity($activity, array('retrieveBean'));
        $bf::staticExpects($this->once())
            ->method('retrieveBean')
            ->with($this->equalTo($parentType),
                $this->equalTo($parentId),
                $this->equalTo(array()),
                $this->equalTo(true))
            ->will($this->returnValue($mockBean));

        $result = SugarTestReflection::callProtectedMethod($activity, 'getParentBean');
        $this->assertEquals($mockBean, $result, "Should return retrieved bean");
    }

    /**
     * @covers Activity::getChangedFieldsForUser
     */
    public function testGetChangedFieldsForUser_NonUpdateActivity_ReturnsEmptyArray()
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->activity_type = 'foo';
        $activity->data = '{changes: []}';

        $bean = $this->getMock('SugarBean');
        $bean->expects($this->never())->method('ACLFilterFieldList');

        $result = SugarTestReflection::callProtectedMethod(
            $activity,
            'getChangedFieldsForUser',
            array(new User(), $bean)
        );
        $this->assertEquals(array(), $result, "Should return empty array");
    }

    /**
     * @covers Activity::getChangedFieldsForUser
     */
    public function testGetChangedFieldsForUser_NoDataChanges_ReturnsEmptyArray()
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->activity_type = 'update';
        $activity->data = '{}';

        $bean = $this->getMock('SugarBean');
        $bean->expects($this->never())->method('ACLFilterFieldList');

        $result = SugarTestReflection::callProtectedMethod(
            $activity,
            'getChangedFieldsForUser',
            array(new User(), $bean)
        );
        $this->assertEquals(array(), $result, "Should return empty array");
    }

    /**
     * @covers Activity::getChangedFieldsForUser
     */
    public function testGetChangedFieldsForUser_DataChangesExist_ChecksACLAndReturnsFields()
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->activity_type = 'update';
        $activity->data = '{"changes": [{"field_name": "foo"},{"field_name": "bar"}]}';

        $bean = $this->getMock('SugarBean');
        $bean->expects($this->once())->method('ACLFilterFieldList');

        $result = SugarTestReflection::callProtectedMethod(
            $activity,
            'getChangedFieldsForUser',
            array(new User(), $bean)
        );
        $this->assertEquals(array('foo','bar'), $result, "Should return array with two fields");
    }

    /**
     * @covers Activity::processPostTags
     */
    public function testProcessPostTags_WithTags_CallsProcessTags()
    {
        $activity = $this->getMock('Activity', array('processTags'));
        $activity->expects($this->once())->method('processTags');

        $activity->data = '{"tags": ["tag1","tag2"]}';
        SugarTestReflection::callProtectedMethod($activity, 'processPostTags');
    }

    /**
     * @covers Activity::processPostTags
     */
    public function testProcessPostTags_WithNoTags_DoesNotCallProcessTags()
    {
        $activity = $this->getMock('Activity', array('processTags'));
        $activity->expects($this->never())->method('processTags');

        $activity->data = '{}';
        SugarTestReflection::callProtectedMethod($activity, 'processPostTags');
    }

    /**
     * @covers Activity::processTags
     */
    public function testProcessTags_WithNoTags_DoesNotProcessAnyRelationships()
    {
        $tags = array();
        $activity = $this->getMock('Activity', array('processUserRelationships', 'processRecord'));
        $activity->expects($this->never())->method('processUserRelationships');
        $activity->expects($this->never())->method('processRecord');
        $activity->processTags($tags);
    }

    /**
     * @covers Activity::processTags
     */
    public function testProcessTags_UserTagNonPostActivity_CallsProcessUserRelationships()
    {
        $tags = array(
            array('module'=>'Users', 'id'=>'123'),
        );
        $activity = $this->getMock('Activity', array('processUserRelationships', 'processRecord'));
        $activity->expects($this->once())
            ->method('processUserRelationships')
            ->with($this->equalTo(array('123')));
        $activity->expects($this->never())->method('processRecord');

        $activity->parent_id = '456';
        $activity->processTags($tags);
    }

    /**
     * @covers Activity::processTags
     */
    public function testProcessTags_UserTagPostToModuleWithUserAccess_CallsProcessRecord()
    {
        $tags = array(
            array('module'=>'Users', 'id'=>'123'),
        );
        $activity = $this->getMock('Activity', array(
            'processUserRelationships',
            'processRecord',
            'userHasViewAccessToParentModule',
        ));
        $activity->expects($this->once())
            ->method('userHasViewAccessToParentModule')
            ->will($this->returnValue(true));
        $activity->expects($this->once())->method('processRecord');
        $activity->expects($this->never())->method('processUserRelationships');

        $bf = $this->mockBeanFactoryOnActivity($activity, array('retrieveBean'));
        $bf::staticExpects($this->once())
            ->method('retrieveBean')
            ->with($this->equalTo('Users'), $this->equalTo('123'))
            ->will($this->returnValue(new SugarBean()));

        $activity->parent_type = 'Foo';
        $activity->processTags($tags);
    }

    /**
     * @covers Activity::processTags
     */
    public function testProcessTags_UserTagPostToModuleWithNoAccess_DoesNotProcessAnyRelationships()
    {
        $tags = array(
            array('module'=>'Users', 'id'=>'123'),
        );
        $activity = $this->getMock('Activity', array(
            'processUserRelationships',
            'processRecord',
            'userHasViewAccessToParentModule',
        ));
        $activity->expects($this->once())
            ->method('userHasViewAccessToParentModule')
            ->will($this->returnValue(false));
        $activity->expects($this->never())->method('processRecord');
        $activity->expects($this->never())->method('processUserRelationships');

        $activity->parent_type = 'Bar';
        $activity->processTags($tags);
    }

    /**
     * @covers Activity::processTags
     */
    public function testProcessTags_NonUserTag_CallsProcessRecord()
    {
        $tags = array(
            array('module'=>'Blah', 'id'=>'123'),
        );
        $activity = $this->getMock('Activity', array(
            'processUserRelationships',
            'processRecord',
        ));
        $activity->expects($this->once())->method('processRecord');
        $activity->expects($this->never())->method('processUserRelationships');

        $bf = $this->mockBeanFactoryOnActivity($activity, array('retrieveBean'));
        $bf::staticExpects($this->once())
            ->method('retrieveBean')
            ->with($this->equalTo('Blah'), $this->equalTo('123'))
            ->will($this->returnValue(new SugarBean()));

        $activity->processTags($tags);
    }

    /**
     * @covers Activity::userHasViewAccessToParentModule
     */
    public function testUserHasViewAccessToParentModule_NoParentType_ReturnsTrue()
    {
        $activity = SugarTestActivityUtilities::createUnsavedActivity();
        $activity->parent_type = null;
        $result = SugarTestReflection::callProtectedMethod(
            $activity,
            'userHasViewAccessToParentModule',
            array(array('123'))
        );
        $this->assertTrue($result);
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_NoRelationship_ReturnsFalse()
    {
        $activity = $this->getMock('Activity', array('load_relationship'));
        $activity->expects($this->once())
            ->method('load_relationship')
            ->will($this->returnValue(false));

        $result = $activity->processUserRelationships();
        $this->assertFalse($result);
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_NoUserIds_NoRelationshipAdded()
    {
        $relationship = $this->getMockRelationship();;
        $relationship->expects($this->never())->method('add');

        $activity = $this->getMock('Activity', array(
            'load_relationship',
            'getParentBean',
        ));
        $activity->expects($this->once())
            ->method('load_relationship')
            ->will($this->returnValue(true));
        $activity->activities_users = $relationship;

        $bf = $this->mockBeanFactoryOnActivity($activity, array('retrieveBean'));
        $bf::staticExpects($this->never())->method('retrieveBean');

        $activity->processUserRelationships(array());
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_ParentBeanNotRetrieved_NoRelationshipAdded()
    {
        $relationship = $this->getMockRelationship();;
        $relationship->expects($this->never())->method('add');

        $activity = $this->getMock('Activity', array(
            'load_relationship',
            'getParentBean',
        ));
        $this->mockActivitiesUserRelationship($activity, $relationship);

        $activity->expects($this->once())
            ->method('getParentBean')
            ->will($this->returnValue(null));

        $bf = $this->mockBeanFactoryOnActivity($activity, array('retrieveBean'));
        $bf::staticExpects($this->never())->method('retrieveBean');

        $activity->processUserRelationships(array());
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_UserHasAccessToParent_RelationshipAdded()
    {
        $relationship = $this->getMockRelationship();
        $relationship->expects($this->once())->method('add');

        $activity = $this->getMock('Activity', array(
            'load_relationship',
            'getParentBean',
            'getChangedFieldsForUser',
        ));
        $this->mockActivitiesUserRelationship($activity, $relationship);

        $parentBean = $this->getMock('SugarBean', array('checkUserAccess'));
        $parentBean->expects($this->once())
            ->method('checkUserAccess')
            ->will($this->returnValue(true));

        $activity->expects($this->once())
            ->method('getParentBean')
            ->will($this->returnValue($parentBean));

        $bf = $this->mockBeanFactoryOnActivity($activity, array('retrieveBean'));
        $bf::staticExpects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue(new User()));

        $activity->processUserRelationships(array('123'));
    }

    /**
     * @covers Activity::processUserRelationships
     */
    public function testProcessUserRelationships_UserNoAccessToParent_SubscriptionRemoved()
    {
        $relationship = $this->getMockRelationship();
        $relationship->expects($this->never())->method('add');

        $activity = $this->getMock('Activity', array(
            'load_relationship',
            'getParentBean',
            'getChangedFieldsForUser',
        ));
        $this->mockActivitiesUserRelationship($activity, $relationship);

        $parentBean = $this->getMock('SugarBean', array('checkUserAccess'));
        $parentBean->expects($this->once())
            ->method('checkUserAccess')
            ->will($this->returnValue(false));

        $activity->expects($this->once())
            ->method('getParentBean')
            ->will($this->returnValue($parentBean));

        $subscription = $this->getMock('Subscription', array('unsubscribeUserFromRecord'));
        $subscription::staticExpects($this->once())->method('unsubscribeUserFromRecord');

        $bf = $this->mockBeanFactoryOnActivity($activity, array('retrieveBean', 'getBeanName'));
        $bf::staticExpects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue(new User()));
        $bf::staticExpects($this->once())
            ->method('getBeanName')
            ->with($this->equalTo('Subscriptions'))
            ->will($this->returnValue(get_class($subscription)));

        $activity->processUserRelationships(array('123'));
    }

    /**
     * Helper to get a mock relationship
     * @return mixed
     */
    protected function getMockRelationship()
    {
        return $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Helper for mocking out the activities_users relationship on an activity
     * @param $activity
     * @param $relationship
     */
    protected function mockActivitiesUserRelationship($activity, $relationship)
    {
        $activity->expects($this->once())
            ->method('load_relationship')
            ->will($this->returnValue(true));
        $activity->activities_users = $relationship;
    }

    /**
     * Helper to create a mock BeanFactory and set it on the activity
     * @param $activity
     * @param $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockBeanFactoryOnActivity($activity, $methods)
    {
        $beanFactory = $this->getMock('BeanFactory', $methods);
        SugarTestReflection::setProtectedValue($activity, 'beanFactoryClass', get_class($beanFactory));
        return $beanFactory;
    }

}
