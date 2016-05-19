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

namespace Sugarcrm\SugarcrmTests\Trigger\Repair\Runner;

use Sugarcrm\Sugarcrm\Trigger\Repair\Runner\Quiet as RunnerQuiet;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * Testing is all found beans will be forwarded to rebuild.
 *
 * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\Runner\Quiet
 */
class QuietTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var \SugarBean[] */
    protected $listOfBeans = array();

    /** @var RunnerQuiet|\PHPUnit_Framework_MockObject_MockObject */
    protected $quietRunner = null;

    /** @var \Sugarcrm\Sugarcrm\Trigger\Repair\Repair|\PHPUnit_Framework_MockObject_MockObject */
    protected $repair = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->repair = $this->getMock('Sugarcrm\Sugarcrm\Trigger\Repair\Repair');

        $this->quietRunner = new RunnerQuiet($this->repair);

        for ($i = 0; $i < 3; $i ++) {
            $bean = $this->getMockBuilder('SugarBean')
                ->disableOriginalConstructor()
                ->getMock();

            $bean->id = Uuid::uuid1();
            $this->listOfBeans[] = $bean;
        }
    }

    /**
     * Testing is all found beans will be forwarded to rebuild.
     *
     * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\Runner\Quiet::run
     */
    public function testRun()
    {
        $this->repair->expects($this->once())
            ->method('getBeans')
            ->willReturn($this->listOfBeans);

        $this->repair->expects($this->exactly(count($this->listOfBeans)))
            ->method('rebuild')
            ->withConsecutive(
                array($this->listOfBeans[0]),
                array($this->listOfBeans[1]),
                array($this->listOfBeans[2])
            );

        $this->quietRunner->run();
    }
}
