<?php

class PMSEFlowRouteExceptionTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Sets up the test data, for example, 
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        
    }

    /**
     * Removes the initial test configurations for each test, for example:
     *     close a network connection. 
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }
    
    public function testToString()
    {
        $message = 'Exception message';
        $code = 1;
        $exception = new PMSEFlowRouteException($message, $code);
        $result = $exception->__toString();
        $this->assertEquals("PMSEFlowRouteException: [1]: Exception message\n", $result);
    }
    //put your tests code here
}
