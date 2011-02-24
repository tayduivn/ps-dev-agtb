<?php

class Bug41106Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }
    
    public function testCallClose()
    {
        $call = SugarTestCallUtilities::createCall();
        $this->assertEquals(substr($call->date_start, 0, 4), substr($call->date_end, 0, 4), "The end date that was calculated does not match the start date.");
        SugarTestCallUtilities::removeAllCreatedCalls();
    }
    
}