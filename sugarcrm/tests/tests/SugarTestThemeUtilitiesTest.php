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
 
class SugarTestThemeUtilitiesTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_before_snapshot = array();
    
    public function tearDown() 
    {
        SugarTestThemeUtilities::removeAllCreatedAnonymousThemes();
    }

    public function testCanCreateAnAnonymousTheme() 
    {
        $themename = SugarTestThemeUtilities::createAnonymousTheme();

        $this->assertTrue(is_dir("themes/$themename"));
        $this->assertTrue(is_file("themes/$themename/themedef.php"));
    }

    public function testCanCreateAnAnonymousCustomTheme() 
    {
        $themename = SugarTestThemeUtilities::createAnonymousCustomTheme();

        $this->assertTrue(is_dir("custom/themes/$themename"));
        $this->assertTrue(is_file("custom/themes/$themename/themedef.php"));
        
        $themename = 'MyCustomTestTheme'.date("YmdHis");
        SugarTestThemeUtilities::createAnonymousCustomTheme($themename);
        
        $this->assertTrue(is_dir("custom/themes/$themename"));
        $this->assertTrue(is_file("custom/themes/$themename/themedef.php"));
    }
    
    public function testCanCreateAnAnonymousChildTheme() 
    {
        $themename = SugarTestThemeUtilities::createAnonymousTheme();
        $childtheme = SugarTestThemeUtilities::createAnonymousChildTheme($themename);

        $this->assertTrue(is_dir("themes/$childtheme"));
        $this->assertTrue(is_file("themes/$childtheme/themedef.php"));
        
        $themedef = array();
        include("themes/$childtheme/themedef.php");
        
        $this->assertEquals($themedef['parentTheme'],$themename);
    }
    
    public function testCanCreateAnAnonymousRTLTheme() 
    {
        $themename = SugarTestThemeUtilities::createAnonymousRTLTheme();

        $this->assertTrue(is_dir("themes/$themename"));
        $this->assertTrue(is_file("themes/$themename/themedef.php"));
        
        $themedef = array();
        include("themes/$themename/themedef.php");
        
        $this->assertEquals($themedef['directionality'],'rtl');
    }

    public function testCanTearDownAllCreatedAnonymousThemes() 
    {
        $themesCreated = array();
        
        for ($i = 0; $i < 5; $i++) 
            $themesCreated[] = SugarTestThemeUtilities::createAnonymousTheme();

        SugarTestThemeUtilities::removeAllCreatedAnonymousThemes();
        
        foreach ( $themesCreated as $themename )
            $this->assertFalse(is_dir("themes/$themename"));
    }
}

