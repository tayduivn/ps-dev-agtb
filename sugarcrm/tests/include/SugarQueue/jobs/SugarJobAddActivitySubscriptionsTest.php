<?php

use SugarTestActivityUtilities as ActivityHelper;
use SugarTestProductUtilities as ProductHelper;

class SugarJobAddActivitySubscriptionsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //set up test data
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->user = $GLOBALS['current_user'];
        $this->activity = ActivityHelper::createActivity();
        $this->bean = ProductHelper::createProduct();
        $this->bean->assigned_user_id = $this->user->id;
        $this->bean->save();

        //create test job
        $this->job = new SugarJobAddActivitySubscriptions();
        $this->job->setJob(new SchedulersJob());
    }

    public function tearDown()
    {
        //clean up test data
        ProductHelper::removeAllCreatedProducts();
        ActivityHelper::removeAllCreatedActivities();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @covers SugarJobAddActivitySubscriptionsTest::run
     * @group ActivityStream
     */
    public function testRun_AddSubscriptionsForActivityOfTypeDelete_RunsSuccessfully()
    {
        //simulate deleted bean and associated activity
        BeanFactory::deleteBean($this->bean->module_name, $this->bean->id);

        $this->activity->activity_type = 'deleted';
        $this->activity->save();

        $data = serialize(
            array(
                'act_id' => $this->activity->id,
                'bean_module' => $this->bean->module_name,
                'bean_id' => $this->bean->id,
                'user_partials' => array(
                    array(
                        'created_by' => $this->user->id,
                    ),
                ),
            )
        );
        $this->job->run($data);

        $this->activity->load_relationship("activities_users");
        $this->assertEquals(
            $this->activity->activities_users->get(),
            array($this->user->id),
            'should successfully add the user relationship to the activity'
        );
    }
}
