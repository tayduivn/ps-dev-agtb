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

require_once 'include/MVC/Controller/SugarController.php';

class SugarControllerTest extends Sugar_PHPUnit_Framework_OutputTestCase
{
    protected $module_name;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->module_name = null;
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        if($this->module_name) {
            rmdir_recursive("modules/{$this->module_name}");
            SugarAutoLoader::delFromMap("modules/{$this->module_name}", false);
        }
    }

    public function testSetup()
    {
        $controller = new SugarControllerMock;
        $controller->setup();

        $this->assertEquals('Home',$controller->module);
        $this->assertNull($controller->target_module);
    }

    public function testSetupSpecifyModule()
    {
        $controller = new SugarControllerMock;
        $controller->setup('foo');

        $this->assertEquals('foo',$controller->module);
        $this->assertNull($controller->target_module);
    }

    public function testSetupUseRequestVars()
    {
        $_REQUEST = array(
            'module' => 'dog33434',
            'target_module' => 'dog121255',
            'action' => 'dog3232',
            'record' => 'dog5656',
            'view' => 'dog4343',
            'return_module' => 'dog1312',
            'return_action' => 'dog1212',
            'return_id' => '11212',
            );
        $controller = new SugarControllerMock;
        $controller->setup();

        $this->assertEquals($_REQUEST['module'],$controller->module);
        $this->assertEquals($_REQUEST['target_module'],$controller->target_module);
        $this->assertEquals($_REQUEST['action'],$controller->action);
        $this->assertEquals($_REQUEST['record'],$controller->record);
        $this->assertEquals($_REQUEST['view'],$controller->view);
        $this->assertEquals($_REQUEST['return_module'],$controller->return_module);
        $this->assertEquals($_REQUEST['return_action'],$controller->return_action);
        $this->assertEquals($_REQUEST['return_id'],$controller->return_id);
    }


    protected function touch($filename)
    {
        sugar_touch($filename);
        SugarAutoLoader::addToMap($filename, false);
    }

    protected function mkdir($filename)
    {
        $this->rmdir[] = $filename;
        sugar_mkdir($filename,null,true);
    }

    public function testSetModule()
    {
        $controller = new SugarControllerMock;
        $controller->setModule('cat');

        $this->assertEquals('cat',$controller->module);
    }

    public function testCallLegacyCodeIfLegacyDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule'.mt_rand();
        sugar_mkdir("modules/$module_name/", null, true);
        $this->touch("modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock;
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';
        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('classic',$controller->view);

    }

    public function testCallLegacyCodeIfNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule'.mt_rand();
        sugar_mkdir("modules/$module_name/views", null, true);
        $this->touch("modules/$module_name/views/view.detail.php");

        $controller = new SugarControllerMock;
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';

        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('list',$controller->view);
    }


    public function testCallLegacyCodeIfLegacyDetailViewAndNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule'.mt_rand();
        sugar_mkdir("modules/$module_name/views",null,true);
        $this->touch("modules/$module_name/views/view.detail.php");
        $this->touch("modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock;
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';

        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('list',$controller->view);
    }

    public function testCallLegacyCodeIfCustomLegacyDetailViewAndNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule'.mt_rand();
        sugar_mkdir("modules/$module_name/views",null,true);
        $this->touch("modules/$module_name/views/view.detail.php");
        sugar_mkdir("custom/modules/$module_name",null,true);
        $this->touch("custom/modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock;
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';

        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('classic',$controller->view);
    }

    public function testCallLegacyCodeIfLegacyDetailViewAndCustomNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule'.mt_rand();
        sugar_mkdir("custom/modules/$module_name/views",null,true);
        $this->touch("custom/modules/$module_name/views/view.detail.php");
        sugar_mkdir("modules/$module_name",null,true);
        $this->touch("modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock;
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';
        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('classic',$controller->view);
    }

    public function testCallLegacyCodeIfLegacyDetailViewAndNewDetailViewFoundAndCustomLegacyDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule'.mt_rand();
        sugar_mkdir("modules/$module_name/views",null,true);
        $this->touch("modules/$module_name/views/view.detail.php");
        $this->touch("modules/$module_name/DetailView.php");
        sugar_mkdir("custom/modules/$module_name",null,true);
        $this->touch("custom/modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock;
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';

        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('classic',$controller->view);
    }

    public function testCallLegacyCodeIfLegacyDetailViewAndNewDetailViewFoundAndCustomNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule'.mt_rand();
        sugar_mkdir("custom/modules/$module_name/views",null,true);
        $this->touch("custom/modules/$module_name/views/view.detail.php");
        sugar_mkdir("modules/$module_name/views",null,true);
        $this->touch("modules/$module_name/views/view.detail.php");
        $this->touch("modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock;
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';
        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('list',$controller->view);
    }

    public function testCallLegacyCodeIfLegacyDetailViewAndNewDetailViewFoundAndCustomLegacyDetailViewFoundAndCustomNewDetailViewFound()
    {
        $this->module_name = $module_name = 'TestModule'.mt_rand();
        sugar_mkdir("custom/modules/$module_name/views",null,true);
        $this->touch("custom/modules/$module_name/views/view.detail.php");
        $this->touch("custom/modules/$module_name/DetailView.php");
        sugar_mkdir("modules/$module_name/views",null,true);
        $this->touch("modules/$module_name/views/view.detail.php");
        $this->touch("modules/$module_name/DetailView.php");

        $controller = new SugarControllerMock;
        $controller->setup($module_name);
        $controller->do_action = 'DetailView';

        $controller->view = 'list';
        $controller->callLegacyCode();

        $this->assertEquals('list',$controller->view);
    }

    public function testPostDelete()
    {
        $_REQUEST['return_module'] = 'foo';
        $_REQUEST['return_action'] = 'bar';
        $_REQUEST['return_id'] = '123';

        $controller = new SugarControllerMock;
        $controller->post_delete();

        unset($_REQUEST['return_module']);
        unset($_REQUEST['return_action']);
        unset($_REQUEST['return_id']);

        $this->assertEquals("index.php?module=foo&action=bar&record=123",$controller->redirect_url);
    }

    /**
     * @ticket 23816
     */
    public function testPostDeleteWithOffset()
    {
        $_REQUEST['return_module'] = 'foo';
        $_REQUEST['return_action'] = 'bar';
        $_REQUEST['return_id'] = '123';
        $_REQUEST['offset'] = '2';

        $controller = new SugarControllerMock;
        $controller->post_delete();

        unset($_REQUEST['return_module']);
        unset($_REQUEST['return_action']);
        unset($_REQUEST['return_id']);
        unset($_REQUEST['offset']);

        $this->assertEquals("index.php?module=foo&action=bar&record=123&offset=2",$controller->redirect_url);
    }

    /**
     * @ticket 23816
     */
    public function testPostDeleteWithOffsetAndDuplicateSave()
    {
        $_REQUEST['return_module'] = 'foo';
        $_REQUEST['return_action'] = 'bar';
        $_REQUEST['return_id'] = '123';
        $_REQUEST['offset'] = '2';
        $_REQUEST['duplicateSave'] = true;

        $controller = new SugarControllerMock;
        $controller->post_delete();

        unset($_REQUEST['return_module']);
        unset($_REQUEST['return_action']);
        unset($_REQUEST['return_id']);
        unset($_REQUEST['offset']);
        unset($_REQUEST['duplicateSave']);

        $this->assertEquals("index.php?module=foo&action=bar&record=123",$controller->redirect_url);
    }

    public function testPostDeleteWithDefaultValues()
    {
        $backupDefaultModule = $GLOBALS['sugar_config']['default_module'];
        $backupDefaultAction = $GLOBALS['sugar_config']['default_action'];

        $GLOBALS['sugar_config']['default_module'] = 'yuck';
        $GLOBALS['sugar_config']['default_action'] = 'yuckyuck';

        $controller = new SugarControllerMock;
        $controller->post_delete();

        $GLOBALS['sugar_config']['default_module'] = $backupDefaultModule;
        $GLOBALS['sugar_config']['default_action'] = $backupDefaultAction;

        $this->assertEquals("index.php?module=yuck&action=yuckyuck&record=",$controller->redirect_url);
    }

    public function testExecuteException()
    {
        $controller = $this->getMock('SugarController', array('process', 'handleException'));
        $controller->expects($this->once())
            ->method('process')
            ->will($this->throwException(new Exception('test')));
        $controller->expects($this->once())
            ->method('handleException');
        $controller->execute();
    }

    public function testExecuteNoException()
    {
        $controller = $this->getMockBuilder('SugarController')
            ->setMethods(array('execute'))
            ->getMock();
        $controller->expects($this->never())
            ->method('handleException');
        $controller->execute();
        // this is just to suppress output... remove when this is a proper test
        $this->expectOutputRegex('/<html/');
    }

}

class SugarControllerMock extends SugarController
{
    public $do_action;

    public function callLegacyCode()
    {
        return parent::callLegacyCode();
    }

    public function post_delete()
    {
        parent::post_delete();
    }
}
