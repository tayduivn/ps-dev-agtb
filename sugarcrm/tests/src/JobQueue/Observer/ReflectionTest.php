<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTests\JobQueue\Observer;

use Psr\Log\NullLogger;
use Sugarcrm\Sugarcrm\JobQueue\Observer\Reflection;
use Sugarcrm\Sugarcrm\JobQueue\Workload\Workload;

class ReflectionTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Reflection
     */
    protected $observer;

    /**
     * @var Workload
     */
    protected $workload;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->workload = new Workload('testRoute', array());
        $this->observer = new Reflection(new NullLogger());
    }

    public function tearDown()
    {
        \SugarTestSchedulersJobUtilities::removeAllCreatedJobs();
        \SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        \SugarTestHelper::tearDown();
    }

    /**
     * Should create a record in DB and add 'dbId' attribute.
     */
    public function testOnAdd()
    {
        $this->observer->onAdd($this->workload);
        $job = \BeanFactory::getBean('SchedulersJobs', $this->workload->getAttribute('dbId'));
        $this->assertNotNull($job->id);

        \SugarTestSchedulersJobUtilities::setCreatedJob(array($job->id));
    }

    /**
     * Should resolve a job with passed resolution.
     */
    public function testOnResolve()
    {
        $job = \SugarTestSchedulersJobUtilities::createJob();
        $job->status = \SchedulersJob::JOB_STATUS_RUNNING;
        $job->resolution = \SchedulersJob::JOB_RUNNING;
        $job->save();

        $this->observer = new Reflection(new NullLogger());

        $this->workload->setAttribute('dbId', $job->id);
        $this->observer->onResolve($this->workload, \SchedulersJob::JOB_SUCCESS);

        $job->retrieve();

        $this->assertEquals(\SchedulersJob::JOB_STATUS_DONE, $job->status);
        $this->assertEquals(\SchedulersJob::JOB_SUCCESS, $job->resolution);
    }

    /**
     * Should use a job's user on run and restore it on resolve.
     */
    public function testChangeCurrentUser()
    {
        $admin = $GLOBALS['current_user'];
        $jobUser = \SugarTestUserUtilities::createAnonymousUser();

        // Add a job under a regular user.
        $GLOBALS['current_user'] = $jobUser;
        $this->observer->onAdd($this->workload);

        $job = \BeanFactory::getBean('SchedulersJobs', $this->workload->getAttribute('dbId'));
        \SugarTestSchedulersJobUtilities::setCreatedJob(array($job->id));

        $this->assertEquals($jobUser->id, $job->assigned_user_id);

        // Restore the user.
        $GLOBALS['current_user'] = $admin;
        $this->observer->onRun($this->workload);

        $this->assertEquals($jobUser->id, $GLOBALS['current_user']->id);

        $this->observer->onResolve($this->workload, \SchedulersJob::JOB_SUCCESS);

        $this->assertEquals($admin->id, $GLOBALS['current_user']->id);
    }
}
