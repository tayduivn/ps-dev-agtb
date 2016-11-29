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
class PMSEConvergingGatewayTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PMSEElement
     */
    protected $convergingGateway;

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
     * 
     */
    public function testRetrievePreviousFlowsALL()
    {
        $this->convergingGateway = $this->getMockBuilder('PMSEConvergingGateway')
            ->setMethods(array('retrieveSugarQueryObject'))
            ->disableOriginalConstructor()
            ->getMock();

        $caseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('retrieveBean'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $sugarBeanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();
        
        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->setMethods(array('select', 'from', 'where', 'joinRaw', 'queryAnd', 'addRaw', 'execute', 'fieldRaw'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $caseFlowHandler->expects($this->exactly(1))
            ->method('retrieveBean')
            ->will($this->returnValue($sugarBeanMock));

        $sugarQuery->expects($this->atLeastOnce())
            ->method('select')
            ->will($this->returnValue($sugarQuery));

        $this->convergingGateway->expects($this->exactly(1))
            ->method('retrieveSugarQueryObject')
            ->will($this->returnValue($sugarQuery));
        
        $sugarQuery->expects($this->exactly(1))
            ->method('where')
            ->will($this->returnValue($sugarQuery));
        
        $sugarQuery->expects($this->exactly(1))
            ->method('queryAnd')
            ->will($this->returnValue($sugarQuery));
        
        $sugarQuery->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue(array(array('id'=>'abc123'))));
        
        $this->convergingGateway->setCaseFlowHandler($caseFlowHandler);
        
        $type = 'PASSED';
        $elementId = '29018301923132';
        
        $this->convergingGateway->retrievePreviousFlows($type, $elementId);
        
    }
    
    /**
     * 
     */
    public function testRetrievePreviousFlowsPASSED()
    {
        $this->convergingGateway = $this->getMockBuilder('PMSEConvergingGateway')
            ->setMethods(array('retrieveSugarQueryObject'))
            ->disableOriginalConstructor()
            ->getMock();

        $caseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(array('retrieveBean'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $sugarBeanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(NULL)
            ->getMock();
        
        $sugarQuery = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('select', 'from', 'where', 'joinRaw', 'queryAnd', 'addRaw', 'execute', 'fieldRaw'))
            ->getMock();
        
        $this->convergingGateway->expects($this->exactly(1))
            ->method('retrieveSugarQueryObject')
            ->will($this->returnValue($sugarQuery));
        
        $caseFlowHandler->expects($this->exactly(1))
            ->method('retrieveBean')
            ->will($this->returnValue($sugarBeanMock));

        $sugarQuery->expects($this->atLeastOnce())
            ->method('select')
            ->will($this->returnValue($sugarQuery));
        
        $sugarQuery->expects($this->exactly(1))
            ->method('where')
            ->will($this->returnValue($sugarQuery));
        
        $sugarQuery->expects($this->exactly(1))
            ->method('queryAnd')
            ->will($this->returnValue($sugarQuery));
        
        $sugarQuery->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue(array(array('id'=>'abc123'))));
        
        $this->convergingGateway->setCaseFlowHandler($caseFlowHandler);
        
        $type = 'ALL';
        $elementId = '29018301923132';
        
        $this->convergingGateway->retrievePreviousFlows($type, $elementId);
    }
}
