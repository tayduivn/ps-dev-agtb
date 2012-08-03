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

require_once 'include/MVC/View/SugarView.php';

class SugarViewTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_backup = array();

    /**
     * @var SugarViewTestMock
     */
    private $_view;

    public function setUp()
    {
        global $moduleList, $beanList, $beanFiles;
        require('include/modules.php');
        $this->_view = new SugarViewTestMock();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Users');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->_backup['currentTheme'] = SugarThemeRegistry::current();
    }

    public function tearDown()
    {
    	unset($GLOBALS['mod_strings']);
    	unset($GLOBALS['app_strings']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarThemeRegistry::set($this->_backup['currentTheme']->dirName);
    }

    public function testGetModuleTab()
    {
        $_REQUEST['module_tab'] = 'ADMIN';
        $moduleTab = $this->_view->getModuleTab();
        $this->assertEquals('ADMIN', $moduleTab, 'Module Tab names are not equal from request');
    }

    public function testGetMetaDataFile()
    {
        // backup custom file if it already exists
        if(file_exists('custom/modules/Contacts/metadata/listviewdefs.php')){
            copy('custom/modules/Contacts/metadata/listviewdefs.php', 'custom/modules/Contacts/metadata/listviewdefs.php.bak');
            unlink('custom/modules/Contacts/metadata/listviewdefs.php');
        }
        $this->_view->module = 'Contacts';
        $this->_view->type = 'list';
        $metaDataFile = $this->_view->getMetaDataFile();
        $this->assertEquals('modules/Contacts/metadata/listviewdefs.php', $metaDataFile, 'Did not load the correct metadata file');

        //test custom file
        if(!file_exists('custom/modules/Contacts/metadata/')){
            sugar_mkdir('custom/modules/Contacts/metadata/', null, true);
        }
        $customFile = 'custom/modules/Contacts/metadata/listviewdefs.php';
        if(!file_exists($customFile))
        {
            sugar_file_put_contents($customFile, array());
            $customMetaDataFile = $this->_view->getMetaDataFile();
            $this->assertEquals($customFile, $customMetaDataFile, 'Did not load the correct custom metadata file');
            unlink($customFile);
        }
        // Restore custom file if we backed it up
        if(file_exists('custom/modules/Contacts/metadata/listviewdefs.php.bak')){
            rename('custom/modules/Contacts/metadata/listviewdefs.php.bak', 'custom/modules/Contacts/metadata/listviewdefs.php');
        }
    }

    public function testInit()
    {
        $bean = new SugarBean;
        $view_object_map = array('foo'=>'bar');
        $GLOBALS['action'] = 'barbar';
        $GLOBALS['module'] = 'foofoo';

        $this->_view->init($bean,$view_object_map);

        $this->assertInstanceOf('SugarBean',$this->_view->bean);
        $this->assertEquals($view_object_map,$this->_view->view_object_map);
        $this->assertEquals($GLOBALS['action'],$this->_view->action);
        $this->assertEquals($GLOBALS['module'],$this->_view->module);
        $this->assertInstanceOf('Sugar_Smarty',$this->_view->ss);
    }

    public function testInitNoParameters()
    {
        $GLOBALS['action'] = 'barbar';
        $GLOBALS['module'] = 'foofoo';

        $this->_view->init();

        $this->assertNull($this->_view->bean);
        $this->assertEquals(array(),$this->_view->view_object_map);
        $this->assertEquals($GLOBALS['action'],$this->_view->action);
        $this->assertEquals($GLOBALS['module'],$this->_view->module);
        $this->assertInstanceOf('Sugar_Smarty',$this->_view->ss);
    }

    public function testInitSmarty()
    {
        $this->_view->initSmarty();

        $this->assertInstanceOf('Sugar_Smarty',$this->_view->ss);
        $this->assertEquals($this->_view->ss->get_template_vars('MOD'),$GLOBALS['mod_strings']);
        $this->assertEquals($this->_view->ss->get_template_vars('APP'),$GLOBALS['app_strings']);
    }

    /**
     * @outputBuffering enabled
     */
    public function testDisplayErrors()
    {
        $this->_view->errors = array('error1','error2');
        $this->_view->suppressDisplayErrors = true;

        $this->assertEquals(
            '<span class="error">error1</span><br><span class="error">error2</span><br>',
            $this->_view->displayErrors()
            );
    }

    /**
     * @outputBuffering enabled
     */
    public function testDisplayErrorsDoNotSupressOutput()
    {
        $this->_view->errors = array('error1','error2');
        $this->_view->suppressDisplayErrors = false;

        $this->assertEmpty($this->_view->displayErrors());
    }

    public function testGetBrowserTitle()
    {
        $viewMock = $this->getMock('SugarViewTestMock',array('_getModuleTitleParams'));
        $viewMock->expects($this->any())
                 ->method('_getModuleTitleParams')
                 ->will($this->returnValue(array('foo','bar')));

        $this->assertEquals(
            "bar &raquo; foo &raquo; {$GLOBALS['app_strings']['LBL_BROWSER_TITLE']}",
            $viewMock->getBrowserTitle()
            );
    }

    public function testGetBrowserTitleUserLogin()
    {
        $this->_view->module = 'Users';
        $this->_view->action = 'Login';

        $this->assertEquals(
            "{$GLOBALS['app_strings']['LBL_BROWSER_TITLE']}",
            $this->_view->getBrowserTitle()
            );
    }

    public function testGetBreadCrumbSymbolForLTRTheme()
    {
        $theme = SugarTestThemeUtilities::createAnonymousTheme();
        SugarThemeRegistry::set($theme);

        $this->assertEquals(
            "<span class='pointer'>&raquo;</span>",
            $this->_view->getBreadCrumbSymbol()
            );
    }

    public function testGetBreadCrumbSymbolForRTLTheme()
    {
        $theme = SugarTestThemeUtilities::createAnonymousRTLTheme();
        SugarThemeRegistry::set($theme);

        $this->assertEquals(
            "<span class='pointer'>&laquo;</span>",
            $this->_view->getBreadCrumbSymbol()
            );
    }

    public function testGetSugarConfigJS()
    {
        global $sugar_config;

        $sugar_config['js_available'] = array('default_action');

        $js_array = $this->_view->getSugarConfigJS();

        // this should return 3 objects
        $this->assertEquals(3, count($js_array));

        $this->assertEquals('SUGAR.config.default_action = "index";', $js_array[2]);
    }
}

class SugarViewTestMock extends SugarView
{
    public function getModuleTab()
    {
        return parent::_getModuleTab();
    }

    public function initSmarty()
    {
        return parent::_initSmarty();
    }

    public function getSugarConfigJS()
    {
        return parent::getSugarConfigJS();
    }
}
