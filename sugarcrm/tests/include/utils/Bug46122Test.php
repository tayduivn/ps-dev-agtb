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
require_once('include/MVC/View/SugarView.php');

class Bu46122Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $hasCustomModulesLogicHookFile = false;
    var $hasCustomContactLogicHookFile = false;
    var $modulesHookFile = 'custom/modules/logic_hooks.php';
    var $contactsHookFile = 'custom/modules/Contacts/logic_hooks.php';

    public function setUp()
    {
        //Setup mock logic hook files
        if(file_exists($this->modulesHookFile))
        {
            $this->hasCustomModulesLogicHookFile = true;
            copy($this->modulesHookFile, $this->modulesHookFile.'.bak');
        } else {
            write_array_to_file("test", array(), $this->modulesHookFile);
        }

        if(file_exists($this->contactsHookFile))
        {
            $this->hasCustomContactLogicHookFile = true;
            copy($this->contactsHookFile, $this->contactsHookFile.'.bak');
        } else {
            write_array_to_file("test", array(), $this->contactsHookFile);
        }

        $this->useOutputBuffering = false;
        LogicHook::refreshHooks();
    }

    public function tearDown()
    {
        //Remove the custom logic hook files
        if($this->hasCustomModulesLogicHookFile && file_exists($this->modulesHookFile.'.bak'))
        {
            copy($this->modulesHookFile.'.bak', $this->modulesHookFile);
            unlink($this->modulesHookFile.'.bak');
        } else if(file_exists($this->modulesHookFile)) {
            unlink($this->modulesHookFile);
        }

        if($this->hasCustomContactLogicHookFile && file_exists($this->contactsHookFile.'.bak'))
        {
            copy($this->contactsHookFile.'.bak', $this->contactsHookFile);
            unlink($this->contactsHookFile.'.bak');
        } else if(file_exists($this->contactsHookFile)) {
            unlink($this->contactsHookFile);
        }
        unset($GLOBALS['logic_hook']);
    }

    public function testSugarViewProcessLogicHookWithModule()
    {
        $GLOBALS['logic_hook'] = new LogicHookMock();
        $hooks = $GLOBALS['logic_hook']->getHooks('Contacts');
        $sugarViewMock = new SugarViewMock();
        $sugarViewMock->module = 'Contacts';
        $sugarViewMock->process();
        $expectedHookCount = isset($hooks['after_ui_frame']) ? count($hooks['after_ui_frame']) : 0;
        $this->assertEquals($expectedHookCount, $GLOBALS['logic_hook']->hookRunCount, 'Assert that two logic hook files were run');
    }


    public function testSugarViewProcessLogicHookWithoutModule()
    {
        $GLOBALS['logic_hook'] = new LogicHookMock();
        $hooks = $GLOBALS['logic_hook']->getHooks('');
        $sugarViewMock = new SugarViewMock();
        $sugarViewMock->module = '';
        $sugarViewMock->process();
        $expectedHookCount = isset($hooks['after_ui_frame']) ? count($hooks['after_ui_frame']) : 0;
        $this->assertEquals($expectedHookCount, $GLOBALS['logic_hook']->hookRunCount, 'Assert that one logic hook file was run');
    }
}

class SugarViewMock extends SugarView
{
    var $options = array();
    //no-opt methods we override
    function _trackView() {}
    function renderJavascript() {}
    function _buildModuleList() {}
    function preDisplay() {}
    function displayErrors() {}
    function display() {}
}

class LogicHookMock extends LogicHook
{
    var $hookRunCount = 0;

    function process_hooks($hook_array, $event, $arguments)
    {
        if(!empty($hook_array[$event])){
            if($event == 'after_ui_frame')
            {
                $this->hookRunCount++;
            }
        }
    }
}

?>