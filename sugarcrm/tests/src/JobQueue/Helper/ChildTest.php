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

namespace Sugarcrm\SugarcrmTests\JobQueue\Helper;

use Sugarcrm\Sugarcrm\JobQueue\Helper\Child;

class ChildTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \SchedulersJob
     */
    protected $parentJob;

    /**
     * @var \SchedulersJob
     */
    protected $childJob;

    /**
     * @var Child
     */
    protected $helper;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->parentJob = \SugarTestSchedulersJobUtilities::createJob();
        $this->childJob = \SugarTestSchedulersJobUtilities::createJob();
        $this->childJob->job_group = $this->parentJob->id;
        $this->childJob->save();

        $this->helper = new Child($this->childJob);
    }

    public function tearDown()
    {
        \SugarTestSchedulersJobUtilities::removeAllCreatedJobs();
        \SugarTestHelper::tearDown();
    }

    public function testGetParentJob()
    {
        $parentJob = $this->helper->getParentJob();
        $this->assertEquals($this->parentJob->id, $parentJob->id);
    }

    public function testParentJobActuality()
    {
        $this->parentJob->status = null;
        $this->parentJob->save();

        $this->assertTrue($this->helper->isParentActual());

        $this->parentJob->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->parentJob->save();

        $this->assertFalse($this->helper->isParentActual());
    }

    /**
     * @expectedException \Exception
     */
    public function testParentJobInChildHandler()
    {
        new Child($this->parentJob);
    }
}
