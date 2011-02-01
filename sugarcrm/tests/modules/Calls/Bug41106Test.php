<?php

class Bug41106Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function testCallClose()
    {
        $call = SugarTestCallUtilities::createCall();
        $this->assertEquals(substr($call->date_start, 0, 4), substr($call->date_end, 0, 4), "The end date that was calculated does not match the start date.");
        SugarTestCallUtilities::removeAllCreatedCalls();
    }
    
}