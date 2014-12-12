<?php

class PMSEUserTaskTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PMSEElement
     */
    protected $userTask;

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
    public function testRunAssignment()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(array('prepareResponse'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('taskAssignment'))
            ->getMock();
        
        $bean = new stdClass();
        $externalAction = '';
        $flowData = array(
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5
        );
        
        $expectedFlowData = array(
            'cas_user_id' => 2,
            'cas_index' => 1,
            'id' => 5,
            'cas_flow_status' => 'FORM',
            'assigned_user_id' => 2,
        );
        
        $expectedResult = array(
            'route_action' => 'WAIT',
            'flow_action' => 'CREATE',
            'flow_data' => array ( 
                'cas_user_id' => 2,                
                'cas_index' => 1,
                'id' => 5,
                'cas_flow_status' => 'FORM'
            ),
            'flow_id' => $flowData['id']
        );
        
        $this->userTask->expects($this->exactly(1))
            ->method('prepareResponse')
            ->with($expectedFlowData, 'WAIT', 'CREATE')
            ->will($this->returnValue($expectedResult));

        $userAssignment->expects($this->exactly(1))
            ->method('taskAssignment')
            ->with($flowData)
            ->will($this->returnValue(2));
        
        $this->userTask->setUserAssignmentHandler($userAssignment);
        
        $result = $this->userTask->run($flowData, $bean, $externalAction);
        $this->assertEquals($expectedResult, $result);
    }

    public function testRunRoundTrip()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(array('prepareResponse', 'processAction'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('roundTripReassign'))
            ->getMock();
        
        $bean = new stdClass();
        $externalAction = 'ROUND_TRIP';
        $flowData = array(
            'cas_user_id' => 1,
            'cas_index' => 2,
            'id' => 5
        );
        
        $expectedResult = array(
            'route_action' => 'WAIT', 
            'flow_action' => 'CLOSE', 
            'flow_data' => array ('cas_flow_status' => 'FORM'), 
            'flow_id' => $flowData['id']
        );
        
        $expectedFlowData = array(
            'cas_user_id' => 1,            
            'cas_index' => 1,
            'id' => 5,
            'cas_flow_status' => 'FORM',
            'assigned_user_id' => 1
        );
        
        $this->userTask->expects($this->exactly(1))
            ->method('prepareResponse')
            ->with($expectedFlowData, 'WAIT', 'CLOSE')
            ->will($this->returnValue($expectedResult));
        
        $this->userTask->expects($this->exactly(1))
            ->method('processAction')
            ->with($flowData)
            ->will($this->returnValue('ROUND_TRIP'));

        $rtFlowData = $flowData;
        $rtFlowData['cas_index']--;
        $userAssignment->expects($this->exactly(1))
            ->method('roundTripReassign')
            ->with($rtFlowData)
            ->will($this->returnValue(2));
        
        $this->userTask->setUserAssignmentHandler($userAssignment);
        
        $result = $this->userTask->run($flowData, $bean, $externalAction);
        $this->assertEquals($expectedResult, $result);
    }
    
    public function testRunOneWay()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(array('prepareResponse', 'processAction'))
            ->disableOriginalConstructor()
            ->getMock();
        
        
        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('oneWayReassign'))
            ->getMock();
        
        $bean = new stdClass();
        $externalAction = 'ONE_WAY';
        $flowData = array(
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5
        );
        
        $expectedResult = array(
            'route_action' => 'WAIT', 
            'flow_action' => 'CLOSE', 
            'flow_data' => array ('cas_flow_status' => 'FORM'), 
            'flow_id' => $flowData['id']
        );
        
        $expectedFlowData = array(
            'cas_user_id' => 1,            
            'cas_index' => 0,
            'id' => 5,
            'cas_flow_status' => 'FORM',
            'assigned_user_id' => 1
        );
        
        $this->userTask->expects($this->exactly(1))
            ->method('prepareResponse')
            ->with($expectedFlowData, 'WAIT', 'CLOSE')
            ->will($this->returnValue($expectedResult));
        
        $this->userTask->expects($this->exactly(1))
            ->method('processAction')
            ->with($flowData)
            ->will($this->returnValue('ONE_WAY'));

        $owFlowData = $flowData;
        $owFlowData['cas_index']--;
        $userAssignment->expects($this->exactly(1))
            ->method('oneWayReassign')
            ->with($owFlowData)
            ->will($this->returnValue(2));
        
        $this->userTask->setUserAssignmentHandler($userAssignment);
        
        $result = $this->userTask->run($flowData, $bean, $externalAction);
        $this->assertEquals($expectedResult, $result);
    }
    
    public function testRunRouteWithArguments()
    {                
        
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(array('lockFlowRoute', 'saveBeanData', 'prepareResponse', 'processAction'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $bean = new stdClass();
        $externalAction = 'SOME_ACTION';
        $flowData = array(
            'cas_user_id' => 1,
            'cas_index' => 2,
            'id' => 5
        );
        
        $expectedResult = array(
            'route_action' => 'ROUTE', 
            'flow_action' => 'UPDATE', 
            'flow_data' => array (
                'cas_user_id' => 1,
                'cas_index' => 2,
                'id' => 5,
                'cas_flow_status' => 'FORM',
                'assigned_user_id' => 1
            ), 
            'flow_id' => $flowData['id'],
            'flow_filters' => array()
        );
        
        $expectedFlowData = array(
            'cas_user_id' => 1,
            'cas_index' => 2,
            'id' => 5,
            'cas_flow_status' => 'FORM',
            'assigned_user_id' => 1
        );
        
        $this->userTask->expects($this->exactly(1))
            ->method('prepareResponse')
            ->with($expectedFlowData, 'ROUTE', 'UPDATE')
            ->will($this->returnValue($expectedResult));
        
        $this->userTask->expects($this->exactly(1))
            ->method('lockFlowRoute');
        
        $this->userTask->expects($this->exactly(1))
            ->method('saveBeanData');
        
        $this->userTask->expects($this->exactly(1))
            ->method('processAction')
            ->with($flowData)
            ->will($this->returnValue('ROUTE'));

        $arguments = array('idFlow'=>'abc123');
        $result = $this->userTask->run($flowData, $bean, $externalAction, $arguments);
        $this->assertEquals($expectedResult, $result);
    } 
    
    
    public function testProcessUserActionRT()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        
        $flowData = array(
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5
        );
        
        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('isRoundTrip'))
            ->getMock();
        
        $paramFlowData = array(
            'cas_user_id' => 1,
            'cas_index' => 0,
            'id' => 5
        );
        
        $userAssignment->expects($this->once())
            ->method('isRoundTrip')
            ->with($paramFlowData)
            ->will($this->returnValue(true));
                
        $expectedAction = 'ROUND_TRIP';
        
        $this->userTask->setUserAssignmentHandler($userAssignment);
        $action = $this->userTask->processUserAction($flowData);
        
        $this->assertEquals($expectedAction, $action);
    }
    
    public function testProcessUserActionOW()
    {
        $flowData = array(
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5
        );
        
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        
        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('isRoundTrip', 'isOneWay'))
            ->getMock();
        
        $paramFlowData = array(
            'cas_user_id' => 1,
            'cas_index' => 0,
            'id' => 5
        );
        
        $userAssignment->expects($this->exactly(1))
            ->method('isRoundTrip')
            ->with($paramFlowData)
            ->will($this->returnValue(false));
        
        $userAssignment->expects($this->exactly(1))
            ->method('isOneWay')
            ->with($paramFlowData)
            ->will($this->returnValue(true));
                
        $expectedAction = 'ONE_WAY';
        $this->userTask->setUserAssignmentHandler($userAssignment);
        
        $action = $this->userTask->processUserAction($flowData);
        
        $this->assertEquals($expectedAction, $action);
    }
    
    public function testProcessUserActionRoute()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        
        $flowData = array(
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5
        );
        
        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('isRoundTrip', 'isOneWay'))
            ->getMock();
        
        $paramFlowData = array(
            'cas_user_id' => 1,
            'cas_index' => 0,
            'id' => 5
        );
        
        $userAssignment->expects($this->exactly(1))
            ->method('isRoundTrip')
            ->with($paramFlowData)
            ->will($this->returnValue(false));
        
        $userAssignment->expects($this->exactly(1))
            ->method('isOneWay')
            ->with($paramFlowData)
            ->will($this->returnValue(false));
                
        $expectedAction = 'ROUTE';
        $this->userTask->setUserAssignmentHandler($userAssignment);
        $action = $this->userTask->processUserAction($flowData);
        
        $this->assertEquals($expectedAction, $action);
    }
    
    public function testLockFlowRouteIfRegistered()
    {
        $this->userTask = $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $_SESSION['locked_flows'] = array('abc123');
        $this->userTask->lockFlowRoute('zte890');
        $this->assertContains('zte890', $_SESSION['locked_flows']);
    }
    
    public function testLockFlowRouteIfNew()
    {
        $this->userTask = $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userTask->lockFlowRoute('zte890');
        $this->assertContains('zte890', $_SESSION['locked_flows']);
    }
}
