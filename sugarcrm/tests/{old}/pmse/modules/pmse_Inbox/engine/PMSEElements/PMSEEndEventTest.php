<?php
//FILE SUGARCRM flav=ent ONLY
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

use PHPUnit\Framework\TestCase;

class PMSEEndEventTest extends TestCase
{
    /**
     * @var PMSEElement
     */
    protected $endEvent;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * Test the end event run method if there are no open threads so the action
     * should be to close (complete) the process.
     */
    public function testRunCloseCase()
    {
        $this->endEvent = $this->getMockBuilder('PMSEEndEvent')
            ->setMethods(array('countNumberOpenThreads'))
            ->disableOriginalConstructor()
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('closeThreadByCaseIndex', 'closeCase'))
            ->getMock();

        $count = 1;
       
        $this->endEvent->expects($this->once())
            ->method('countNumberOpenThreads')
            ->will($this->returnValue($count));
        
        $caseFlowHandlerMock->expects($this->once())
            ->method('closeCase');
        
        $bean = new stdClass();

        $this->endEvent->setCaseFlowHandler($caseFlowHandlerMock);

        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_previous' => 1
        );

        $this->endEvent->run($flowData, $bean, '');
    }
    
    /**
     * Test the end event run method if there is at least one open thread so 
     * the action should be to close the current flow and thread.
     */
    public function testRunCloseThread()
    {
        $this->endEvent = $this->getMockBuilder('PMSEEndEvent')
            ->setMethods(array('countNumberOpenThreads'))
            ->disableOriginalConstructor()
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('closeThreadByCaseIndex', 'closeCase'))
            ->getMock();

        $count = 2;
       
        $this->endEvent->expects($this->once())
            ->method('countNumberOpenThreads')
            ->will($this->returnValue($count));
        
        $caseFlowHandlerMock->expects($this->once())
            ->method('closeThreadByCaseIndex');
        
        $bean = new stdClass();

        $this->endEvent->setCaseFlowHandler($caseFlowHandlerMock);

        $flowData = array(
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_previous' => 1
        );

        $this->endEvent->run($flowData, $bean, '');
    }
}
