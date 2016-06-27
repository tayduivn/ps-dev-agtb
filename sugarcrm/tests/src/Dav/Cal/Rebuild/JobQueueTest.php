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


namespace Sugarcrm\SugarcrmTests\Dav\Cal\Rebuild;

use Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\JobQueue;

/**
 * Class JobQueueTest
 *
 * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\JobQueue
 */
class JobQueueTest extends \Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var \SugarTestDatabaseMock
     */
    protected $db = null;

    /**
     * @var \Sugarcrm\Sugarcrm\Util\Runner\Quiet|\PHPUnit_Framework_MockObject_MockObject;
     */
    protected $runner;

    /**
     * @var JobQueue
     */
    protected $jobQueue;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->runner = $this->getMock('Sugarcrm\Sugarcrm\Util\Runner\Quiet', array(), array(), '', false);
        $this->jobQueue = new JobQueue($this->runner);
        $this->db = \SugarTestHelper::setUp('mock_db');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Testing running runner.
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\JobQueue::run
     */
    public function testRun()
    {
        $clearTableQueries = array(
            'clear_events' => '/' . preg_quote($this->db->truncateTableSQL('caldav_events')) . '/',
            'clear_calendars' => '/' . preg_quote($this->db->truncateTableSQL('caldav_calendars')) . '/',
            'clear_changes' => '/' . preg_quote($this->db->truncateTableSQL('caldav_changes')) . '/',
            'clear_scheduling' => '/' . preg_quote($this->db->truncateTableSQL('caldav_scheduling')) . '/',
            'clear_synchronization' =>
                '/' . preg_quote($this->db->truncateTableSQL('caldav_synchronization')) . '/',
            'clear_queue' => '/' . preg_quote($this->db->truncateTableSQL('caldav_queue')) . '/',
        );
        foreach ($clearTableQueries as $key => $query) {
            $this->db->addQuerySpy($key, "{$query}");
        }

        $this->runner->expects($this->once())->method('run');

        $this->assertEquals(\SchedulersJob::JOB_SUCCESS, $this->jobQueue->run());

        foreach (array_keys($clearTableQueries) as $key) {
            $this->assertEquals(1, $this->db->getQuerySpyRunCount($key), "clear table $key");
        }
    }
}
