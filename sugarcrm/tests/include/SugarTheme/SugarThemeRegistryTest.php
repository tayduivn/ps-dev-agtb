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
 
require_once 'include/SugarTheme/SugarTheme.php';
require_once 'include/dir_inc.php';

class SugarThemeRegistryTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_themeName;
    private $_oldDefaultTheme;
    
    public function setup()
    {
        $this->_themeName = SugarTestThemeUtilities::createAnonymousTheme();
        if ( isset($GLOBALS['sugar_config']['default_theme']) ) {
            $this->_oldDefaultTheme = $GLOBALS['sugar_config']['default_theme'];
        }
        $GLOBALS['sugar_config']['default_theme'] = $this->_themeName;
        SugarThemeRegistry::buildRegistry();
    }
    
    public function tearDown()
    {
        SugarTestThemeUtilities::removeAllCreatedAnonymousThemes();
        if ( isset($this->_oldDefaultTheme) ) {
            $GLOBALS['sugar_config']['default_theme'] = $this->_oldDefaultTheme;
        }
    }
    
    public function testThemesRegistered()
    {
        $this->assertTrue(SugarThemeRegistry::exists($this->_themeName));
    }
    
    public function testGetThemeObject()
    {
        $object = SugarThemeRegistry::get($this->_themeName);
        
        $this->assertInstanceOf('SugarTheme',$object);
        $this->assertEquals($object->__toString(),$this->_themeName);
    }
    
    /**
     * @ticket 41635
     */
    public function testGetDefaultThemeObject()
    {
        $GLOBALS['sugar_config']['default_theme'] = $this->_themeName;
        
        $object = SugarThemeRegistry::getDefault();
        
        $this->assertInstanceOf('SugarTheme',$object);
        $this->assertEquals($object->__toString(),$this->_themeName);
    }
    
    /**
     * @ticket 41635
     */
    public function testGetDefaultThemeObjectWhenDefaultThemeIsNotSet()
    {
        unset($GLOBALS['sugar_config']['default_theme']);
        
        $themename = array_pop(array_keys(SugarThemeRegistry::availableThemes()));
        
        $object = SugarThemeRegistry::getDefault();
        
        $this->assertInstanceOf('SugarTheme',$object);
        $this->assertEquals($object->__toString(),$themename);
    }
    
    public function testSetCurrentTheme()
    {
        SugarThemeRegistry::set($this->_themeName);
        
        $this->assertInstanceOf('SugarTheme',SugarThemeRegistry::current());
        $this->assertEquals(SugarThemeRegistry::current()->__toString(),$this->_themeName);
    }
    
    public function testInListOfAvailableThemes()
    {
        if ( isset($GLOBALS['sugar_config']['disabled_themes']) ) {
            $disabled_themes = $GLOBALS['sugar_config']['disabled_themes'];
            unset($GLOBALS['sugar_config']['disabled_themes']);
        }
        
        $themes = SugarThemeRegistry::availableThemes();
        $this->assertTrue(isset($themes[$this->_themeName]));
        $themes = SugarThemeRegistry::unAvailableThemes();
        $this->assertTrue(!isset($themes[$this->_themeName]));
        $themes = SugarThemeRegistry::allThemes();
        $this->assertTrue(isset($themes[$this->_themeName]));
        
        if ( isset($disabled_themes) )
            $GLOBALS['sugar_config']['disabled_themes'] = $disabled_themes;
    }
    
    public function testDisabledThemeNotInListOfAvailableThemes()
    {
        if ( isset($GLOBALS['sugar_config']['disabled_themes']) ) {
            $disabled_themes = $GLOBALS['sugar_config']['disabled_themes'];
            unset($GLOBALS['sugar_config']['disabled_themes']);
        }
        
        $GLOBALS['sugar_config']['disabled_themes'] = $this->_themeName;
        
        $themes = SugarThemeRegistry::availableThemes();
        $this->assertTrue(!isset($themes[$this->_themeName]));
        $themes = SugarThemeRegistry::unAvailableThemes();
        $this->assertTrue(isset($themes[$this->_themeName]));
        $themes = SugarThemeRegistry::allThemes();
        $this->assertTrue(isset($themes[$this->_themeName]));
        
        if ( isset($disabled_themes) )
            $GLOBALS['sugar_config']['disabled_themes'] = $disabled_themes;
    }
    
    public function testCustomThemeLoaded()
    {
        $customTheme = SugarTestThemeUtilities::createAnonymousCustomTheme($this->_themeName);
        
        SugarThemeRegistry::buildRegistry();
        
        $this->assertEquals(
            SugarThemeRegistry::get($customTheme)->name,
            'custom ' . $customTheme
            );
    }
    
    public function testDefaultThemedefFileHandled()
    {
        create_custom_directory('themes/default/');
        sugar_file_put_contents('custom/themes/default/themedef.php','<?php $themedef = array("group_tabs" => false);');
        
        SugarThemeRegistry::buildRegistry();
        
        $this->assertEquals(
            SugarThemeRegistry::get($this->_themeName)->group_tabs,
            false
            );
        
        unlink('custom/themes/default/themedef.php');
    }
    
    public function testClearCacheAllThemes()
    {
        SugarThemeRegistry::get($this->_themeName)->getCSSURL('style.css');
        $this->assertTrue(isset(SugarThemeRegistry::get($this->_themeName)->_cssCache['style.css']),
                            'File style.css should exist in cache');
        
        SugarThemeRegistry::clearAllCaches();
        SugarThemeRegistry::buildRegistry();
        
        $this->assertFalse(isset(SugarThemeRegistry::get($this->_themeName)->_cssCache['style.css']),
                            'File style.css shouldn\'t exist in cache');
    }
    
    /**
     * @ticket 35307
     */
    public function testOldThemeIsNotRecognized()
    {
        $themename = SugarTestThemeUtilities::createAnonymousOldTheme();
        
        $this->assertNull(SugarThemeRegistry::get($themename));
    }
}
