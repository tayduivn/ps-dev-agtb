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

class Bug48571Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $globalDefaultTheme;
    var $unavailableThemes;
    var $customThemeDef;

    public function setUp()
    {
        if(isset($GLOBALS['sugar_config']['default_theme']))
        {
            $this->globalDefaultTheme = $GLOBALS['sugar_config']['default_theme'];
            unset($GLOBALS['sugar_config']['default_theme']);
        }

        if(isset($GLOBALS['sugar_config']['disabled_themes']))
        {
            $this->unavailableThemes = $GLOBALS['sugar_config']['disabled_themes'];
            unset($GLOBALS['sugar_config']['disabled_themes']);
        }

        if(file_exists('custom/themes/default/themedef.php'))
        {
            $this->customThemeDef = file_get_contents('custom/themes/default/themedef.php');
            unlink('custom/themes/default/themedef.php');
        }

        //Blowout all existing cache/themes that may not have been cleaned up
        if(file_exists('cache/themes'))
        {
            rmdir_recursive('cache/themes');
        }

    }

    public function tearDown()
    {
        if(!empty($this->globalDefaultTheme))
        {
            $GLOBALS['sugar_config']['default_theme'] = $this->globalDefaultTheme;
            unset($this->globalDefaultTheme);
        }

        if(!empty($this->unavailableThemes))
        {
            $GLOBALS['sugar_config']['disabled_themes'] = $this->unavailableThemes;
            unset($this->unavailableThemes);
        }

        if(!empty($this->customThemeDef))
        {
            file_put_contents('custom/themes/default/themedef.php', $this->customThemeDef);
        }
    }

    public function testBuildRegistry()
    {
        //BEGIN SUGARCRM flav=com ONLY
        $this->markTestSkipped('Skip for community edition builds for now as this was to test a ce->pro upgrade');
        //END SUGARCRM flav=com ONLY
        
        SugarThemeRegistry::buildRegistry();
        $themeObject = SugarThemeRegistry::current();
        //BEGIN SUGARCRM flav=pro ONLY
        $this->assertRegExp('/RacerX /i', $themeObject->__get('name'), 'Assert that buildRegistry defaults to the RacerX theme');
        //END SUGARCRM flav=pro ONLY

    }

}

?>