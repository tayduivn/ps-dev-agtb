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

namespace Sugarcrm\SugarcrmTestsUnit\JobQueue\Runner;

use Psr\Log\NullLogger;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\JobQueue\Worker\WorkerInterface;
use Sugarcrm\Sugarcrm\JobQueue\LockStrategy\Stub;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\JobQueue\Runner\AbstractRunner
 */
class RunnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::startWorker
     */
    public function testNoJobHandling()
    {
        /**
         * @var Object runner
         * @var Object worker
         */
        \extract($this->getRunnerParts());

        $runner->expects($this->any())
            ->method('isWorkProcessActual')
            ->will($this->onConsecutiveCalls(true, false));

        $worker->expects($this->exactly(2))->method('work')->will($this->returnValue(false));
        $worker->expects($this->any())->method('returnCode')->will(
            $this->returnValue(WorkerInterface::RETURN_CODE_NO_JOBS)
        );
        $runner->expects($this->once())->method('noJobsHandler');
        $worker->expects($this->once())->method('wait')->will($this->returnValue(true));

        $runner->startWorker();
    }


    /**
     * @covers ::startWorker
     */
    public function testJobSuccess()
    {
        /**
         * @var Object runner
         * @var Object worker
         */
        \extract($this->getRunnerParts());

        $runner->expects($this->any())
            ->method('isWorkProcessActual')
            ->will($this->onConsecutiveCalls(true, false));

        $worker->expects($this->exactly(2))->method('work')->will($this->returnValue(true));

        $worker->expects($this->any())->method('returnCode')->will(
            $this->returnValue(WorkerInterface::RETURN_CODE_SUCCESS)
        );
        $worker->expects($this->never())->method('wait');
        $runner->expects($this->never())->method('noJobsHandler');

        $runner->startWorker();
    }

    /**
     * Get an instance of Runner and its parts.
     * @return array ['runner', 'worker', 'lock']
     */
    public function getRunnerParts()
    {
        $worker = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Worker\WorkerInterface',
            array('returnCode', 'registerHandler', 'unregisterHandler', 'work', 'wait')
        );
        $lock = new Stub();

        $runner = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Runner\AbstractRunner',
            array('run', 'shutdownHandler', 'noJobsHandler', 'isWorkProcessActual', 'registerTicks'),
            array(array(), $worker, $lock, new NullLogger())
        );

        TestReflection::setProtectedValue($runner, 'logger', new NullLogger());

        // To prevent infinite loop.
        $worker->expects($this->any())->method('wait')->will($this->returnValue(true));

        return array('runner' => $runner, 'worker' => $worker, 'lock' => $lock);
    }
}
