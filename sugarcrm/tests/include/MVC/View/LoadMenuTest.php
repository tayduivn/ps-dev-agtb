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
 
require_once('include/MVC/View/SugarView.php');

class LoadMenuTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_moduleName;

    public function setUp()
	{
		global $mod_strings, $app_strings;
		$mod_strings = return_module_language($GLOBALS['current_language'], 'Accounts');
		$app_strings = return_application_language($GLOBALS['current_language']);

		$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

		// create a dummy module directory
		$this->_moduleName = 'TestModule'.mt_rand();

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        sugar_mkdir("modules/{$this->_moduleName}",null,true);
	}

	public function tearDown()
	{
		unset($GLOBALS['mod_strings']);
		unset($GLOBALS['app_strings']);

		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		unset($GLOBALS['current_user']);
        if(!empty($this->_moduleName)) {
    		if ( is_dir("modules/{$this->_moduleName}") )
    		    rmdir_recursive("modules/{$this->_moduleName}");
    		if ( is_dir("custom/modules/{$this->_moduleName}") )
    		    rmdir_recursive("custom/modules/{$this->_moduleName}");
        }
		unset($GLOBALS['current_user']);
	}

	public function testMenuDoesNotExists()
	{
        $view = new SugarView;
        $module_menu = $view->getMenu($this->_moduleName);
        $this->assertTrue(empty($module_menu),'Assert the module menu array is empty');
	}
	
	/**
	 * @ticket 43497
	 */
	public function testMenuExistsCanFindModuleMenu()
	{
	    // Create module menu
        if( $fh = @fopen("modules/{$this->_moduleName}/Menu.php", 'w+') ) {
	        $string = <<<EOQ
<?php
\$module_menu[]=Array("index.php?module=Import&action=bar&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs( $fh, $string);
            fclose( $fh );
        }

        $view = new SugarView;
        $module_menu = $view->getMenu($this->_moduleName);
        $found_menu = false;
        $found_menu_twice = false;
        foreach ($module_menu as $menu_entry) {
        	foreach ($menu_entry as $menu_item) {
        		if (preg_match('/action=bar/', $menu_item)) {
        		    if ( $found_menu ) {
        		        $found_menu_twice = true;
        		    }
        		    $found_menu = true;
        		}
        	}
        }
        
        $this->assertTrue($found_menu, "Assert that menu was detected");
        $this->assertFalse($found_menu_twice, "Assert that menu item wasn't listed twice");
	}

    /**
     * @ticket 29114
     * @ticket 43497
     */
    public function testMenuExistsCanFindModuleExtMenu()
    {
        // Create module ext menu
        sugar_mkdir("custom/modules/{$this->_moduleName}/Ext/Menus/",null,true);
        if( $fh = @fopen("custom/modules/{$this->_moduleName}/Ext/Menus/menu.ext.php", 'w+') ) {
	        $string = <<<EOQ
<?php
\$module_menu[]=Array("index.php?module=Import&action=foo&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs( $fh, $string);
            fclose( $fh );
        }

        $view = new SugarView;
        $module_menu = $view->getMenu($this->_moduleName);
        $found_custom_menu = false;
        $found_custom_menu_twice = false;
        foreach ($module_menu as $key => $menu_entry) {
        	foreach ($menu_entry as $id => $menu_item) {
        		if (preg_match('/action=foo/', $menu_item)) {
        		    if ( $found_custom_menu ) {
        		        $found_custom_menu_twice = true;
        		    }
        		    $found_custom_menu = true;
        		}
        	}
        }
        $this->assertTrue($found_custom_menu, "Assert that custom menu was detected");
        $this->assertFalse($found_custom_menu_twice, "Assert that custom menu item wasn't listed twice");
    }

    /**
     * @ticket 38935
     * @ticket 43497
     */
    public function testMenuExistsCanFindModuleExtMenuWhenModuleMenuDefinedGlobal()
    {
        // Create module ext menu
        sugar_mkdir("custom/modules/{$this->_moduleName}/Ext/Menus/",null,true);
        if( $fh = @fopen("custom/modules/{$this->_moduleName}/Ext/Menus/menu.ext.php", 'w+') ) {
	        $string = <<<EOQ
<?php
global \$module_menu;
\$module_menu[]=Array("index.php?module=Import&action=foo&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs( $fh, $string);
            fclose( $fh );
        }

        $view = new SugarView;
        $module_menu = $view->getMenu($this->_moduleName);
        $found_custom_menu = false;
        $found_custom_menu_twice = false;
        foreach ($module_menu as $key => $menu_entry) {
        	foreach ($menu_entry as $id => $menu_item) {
        		if (preg_match('/action=foo/', $menu_item)) {
        		    if ( $found_custom_menu ) {
        		        $found_custom_menu_twice = true;
        		    }
        		    $found_custom_menu = true;
        		}
        	}
        }
        
        $this->assertTrue($found_custom_menu, "Assert that custom menu was detected");
        $this->assertFalse($found_custom_menu_twice, "Assert that custom menu item wasn't listed twice");
    }    
    
    /**
     * @ticket 43497
     */
    public function testMenuExistsCanFindApplicationExtMenu()
	{
	    // Create module ext menu
	    $backupCustomMenu = false;
	    if ( !is_dir("custom/application/Ext/Menus/") )
	        sugar_mkdir("custom/application/Ext/Menus/",null,true);
        if (file_exists('custom/application/Ext/Menus/menu.ext.php')) {
	        copy('custom/application/Ext/Menus/menu.ext.php', 'custom/application/Ext/Menus/menu.ext.php.backup');
	        $backupCustomMenu = true;
	    }

        if ( $fh = @fopen("custom/application/Ext/Menus/menu.ext.php", 'w+') ) {
	        $string = <<<EOQ
<?php
\$module_menu[]=Array("index.php?module=Import&action=foobar&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs( $fh, $string);
            fclose( $fh );
        }

        $view = new SugarView;
        $module_menu = $view->getMenu($this->_moduleName);
        $found_application_custom_menu = false;
        $found_application_custom_menu_twice = false;
        foreach ($module_menu as $key => $menu_entry) {
        	foreach ($menu_entry as $id => $menu_item) {
        		if (preg_match('/action=foobar/', $menu_item)) {
        		    if ( $found_application_custom_menu ) {
        		        $found_application_custom_menu_twice = true;
        		    }
        		    $found_application_custom_menu = true;
        		}
        	}
        }
        
        $this->assertTrue($found_application_custom_menu, "Assert that application custom menu was detected");
        $this->assertFalse($found_application_custom_menu_twice, "Assert that application custom menu item wasn't duplicated");
        
        if($backupCustomMenu) {
            copy('custom/application/Ext/Menus/menu.ext.php.backup', 'custom/application/Ext/Menus/menu.ext.php');
            unlink('custom/application/Ext/Menus/menu.ext.php.backup');
        }
        else
            unlink('custom/application/Ext/Menus/menu.ext.php');
	}

	/**
	 * @ticket 43497
	 */
	public function testMenuExistsCanFindModuleMenuAndModuleExtMenu()
	{
	    // Create module menu
        if( $fh = @fopen("modules/{$this->_moduleName}/Menu.php", 'w+') ) {
	        $string = <<<EOQ
<?php
\$module_menu[]=Array("index.php?module=Import&action=foo&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs( $fh, $string);
            fclose( $fh );
        }

        // Create module ext menu
        sugar_mkdir("custom/modules/{$this->_moduleName}/Ext/Menus/",null,true);
        if( $fh = @fopen("custom/modules/{$this->_moduleName}/Ext/Menus/menu.ext.php", 'w+') ) {
	        $string = <<<EOQ
<?php
\$module_menu[]=Array("index.php?module=Import&action=bar&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs( $fh, $string);
            fclose( $fh );
        }

        $view = new SugarView;
        $module_menu = $view->getMenu($this->_moduleName);
        $found_custom_menu = false;
        $found_custom_menu_twice = false;
        $found_menu = false;
        $found_menu_twice = false;
        foreach ($module_menu as $key => $menu_entry) {
        	foreach ($menu_entry as $id => $menu_item) {
        		if (preg_match('/action=foo/', $menu_item)) {
        		    if ( $found_menu ) {
        		        $found_menu_twice = true;
        		    }
        		    $found_menu = true;
        		}
        		if (preg_match('/action=bar/', $menu_item)) {
        		    if ( $found_custom_menu ) {
        		        $found_custom_menu_twice = true;
        		    }
        		    $found_custom_menu = true;
        		}
        	}
        }
        $this->assertTrue($found_menu, "Assert that menu was detected");
        $this->assertFalse($found_menu_twice, "Assert that menu item wasn't duplicated");
        $this->assertTrue($found_custom_menu, "Assert that custom menu was detected");
        $this->assertFalse($found_custom_menu_twice, "Assert that custom menu item wasn't duplicated");
	}
}

class ViewLoadMenuTest extends SugarView
{
    public function menuExists(
        $module
        )
    {
        return $this->_menuExists($module);
    }
}