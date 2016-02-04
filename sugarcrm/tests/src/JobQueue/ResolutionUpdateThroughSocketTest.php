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

use Sugarcrm\Sugarcrm\JobQueue\Helper\Producer;
use Sugarcrm\Sugarcrm\JobQueue\Helper\Resolution;
use Sugarcrm\Sugarcrm\Socket\Client;

class ResolutionUpdateThroughSocketTest extends \Sugar_PHPUnit_Framework_TestCase
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
     * @var Producer
     */
    protected $producerHelper;

    /**
     * @var Resolution
     */
    protected $resolutionHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject of \Sugarcrm\Sugarcrm\Socket\Client
     */
    protected $socketClient;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        if (!\SugarConfig::getInstance()->get('websockets.server.url')
                || !class_exists('\Sugarcrm\Sugarcrm\Socket\Client')) {
            $this->markTestSkipped('Skipping, Socket Server is not configured.');
        }

        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->parentJob = \SugarTestSchedulersJobUtilities::createJob();
        $this->parentJob->resolution = \SchedulersJob::JOB_PENDING;
        $this->parentJob->save();

        $this->childJob = \SugarTestSchedulersJobUtilities::createJob();
        $this->childJob->job_group = $this->parentJob->id;
        $this->childJob->save();

        $this->producerHelper = new Producer($this->parentJob);
        $this->resolutionHelper = new Resolution();

        // mock socketClient property of Resolution Helper
        $this->socketClient = $this->getMock('\Sugarcrm\Sugarcrm\Socket\Client', array('send'));
        $refl = new \ReflectionClass($this->resolutionHelper);
        $prop = $refl->getProperty('socketClient');
        $prop->setAccessible(true);
        $prop->setValue($this->resolutionHelper, $this->socketClient);

        // set up prepared Resolution Helper of Producer Helper
        $refl = new \ReflectionClass($this->producerHelper);
        $prop = $refl->getProperty('resolutionHelper');
        $prop->setAccessible(true);
        $prop->setValue($this->producerHelper, $this->resolutionHelper);
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        \SugarTestSchedulersJobUtilities::removeAllCreatedJobs();
        \SugarTestHelper::tearDown();
    }

    /**
     * Send upgrade message when parent job changes its resolution.
     */
    public function testParentResolution()
    {
        $this->socketClient
            ->expects($this->once())
            ->method('send');

        $this->resolutionHelper->setResolution($this->parentJob, \SchedulersJob::JOB_RUNNING);
    }

    /**
     * Send upgrade message when children is done and parent::percent_complete is changed.
     */
    public function testChildResolution()
    {
        $this->socketClient
            ->expects($this->once())
            ->method('send');

        $this->resolutionHelper->setResolution($this->childJob, \SchedulersJob::JOB_SUCCESS);

        $this->producerHelper->resolve();
    }
}
