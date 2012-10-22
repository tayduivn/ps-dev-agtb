<?php

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class Bug52133Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $bean;
    protected $hook;

    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public static function tearDownAfterClass()
    {
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function setUp()
    {
        $this->bean = new Account();
        Bug52113TestHook::$count = 0;
        LogicHook::refreshHooks();
	}

	public function tearDown()
	{
	    if(!empty($this->hook)) {
	        call_user_func_array('remove_logic_hook', $this->hook);
	    }
	}

    /**
     * @ticket 52113
     */
    public function testCallLogicHookLoop()
    {
        $this->hook = array('Accounts', 'test_event', Array(1, 'Test hook', __FILE__, 'Bug52113TestHook', 'count'));
        call_user_func_array('check_logic_hook_file', $this->hook);
        for($i=0;$i<50;$i++) {
            $this->bean->call_custom_logic("test_event");
        }
        $this->assertEquals(50, Bug52113TestHook::$count);
    }

	/**
     * @ticket 52113
     */
    public function testCallLogicHookRecursive()
    {
        $this->hook = array('Accounts', 'test_event', Array(1, 'Test hook', __FILE__, 'Bug52113TestHook', 'recurse'));
        call_user_func_array('check_logic_hook_file', $this->hook);
        for($i=0;$i<20;$i++) {
            $this->bean->call_custom_logic("test_event");
        }
        $this->assertEquals(220, Bug52113TestHook::$count);
    }
}

class Bug52113TestHook
{
    static public $count = 0;
    public function count()
    {
        self::$count++;
    }

    public function recurse($bean, $event)
    {
        $this->count();
        $bean->call_custom_logic($event);
    }
}
