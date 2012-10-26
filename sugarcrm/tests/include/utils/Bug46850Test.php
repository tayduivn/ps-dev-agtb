<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once('include/utils/LogicHook.php');

class Bug46850Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $renames = array();
    protected $deletes = array();

    protected $hook = array(
        'test_logic_hook' => array(array(1, 'test_logic_hook', __FILE__, 'LogicHookTest', 'testLogicHook')),
    );

    public function setUp()
    {
        LogicHookTest::$called = false;
        unset($GLOBALS['logic_hook']);
        $GLOBALS['logic_hook'] = LogicHook::initialize();
        LogicHook::refreshHooks();
    }

    public function tearDown()
    {
        foreach($this->renames as $file) {
            rename($file.".bak", $file);
        }
        foreach($this->deletes as $file) {
            unlink($file);
        }
        unset($GLOBALS['logic_hook']);
        LogicHook::refreshHooks();
    }

    protected function saveHook($file)
    {
        if(file_exists($file)) {
            rename($file, $file.".bak");
            $this->renames[] = $file;
        } else {
            $this->deletes[] = $file;
        }
    }

    public function getModules()
    {
        return array(
            array(''),
            array('Contacts'),
            array('Accounts'),
        );
    }

    /**
     * @dataProvider getModules
     */
    public function testHooksDirect($module)
    {
        $dir = rtrim("custom/modules/$module", "/");
        $file = "$dir/logic_hooks.php";
        $this->saveHook($file);
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        write_array_to_file('hook_array', $this->hook, $file);
        $GLOBALS['logic_hook']->getHooks($module, true); // manually refresh
        $GLOBALS['logic_hook']->call_custom_logic($module, 'test_logic_hook');
        $this->assertTrue(LogicHookTest::$called);
    }

    /**
     * @dataProvider getModules
     */
    public function testHooksExtDirect($module)
    {
        if(empty($module)) {
            $dir = "custom/application/Ext/LogicHooks";
        } else {
            $dir = "custom/modules/$module/Ext/LogicHooks";
        }
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file = "$dir/logichooks.ext.php";
        $this->saveHook($file);
        write_array_to_file('hook_array', $this->hook, $file);
        $GLOBALS['logic_hook']->getHooks($module, true); // manually refresh
        $GLOBALS['logic_hook']->call_custom_logic($module, 'test_logic_hook');
        $this->assertTrue(LogicHookTest::$called);
    }

    /**
     * @dataProvider getModules
     */
    public function testHooksUtils($module)
    {
        $dir = rtrim("custom/modules/$module", "/");
        $file = "$dir/logic_hooks.php";
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->saveHook($file);
        check_logic_hook_file($module, 'test_logic_hook', $this->hook['test_logic_hook'][0]);
        $GLOBALS['logic_hook']->getHooks($module, true); // manually refresh
        $GLOBALS['logic_hook']->call_custom_logic($module, 'test_logic_hook');
        $this->assertTrue(LogicHookTest::$called);
    }


    /**
     * @dataProvider getModules
     */
    public function testGeHookArray($module)
    {
        $dir = rtrim("custom/modules/$module", "/");
        $file = "$dir/logic_hooks.php";
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->saveHook($file);
        check_logic_hook_file($module, 'test_logic_hook', $this->hook['test_logic_hook'][0]);
        $array = get_hook_array($module);
        $this->assertEquals($this->hook, $array);
    }
}

class LogicHookTest {
    public static $called = false;
    function testLogicHook() {
        self::$called = true;
    }
}
