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

require_once('tests/rest/RestTestBase.php');
require_once('include/api/SugarApi.php');
require_once('clients/base/api/ThemeApi.php');

class RestThemeTest extends RestTestBase
{

    private $platformTest = 'platform_TEST_123456789';
    private $themeTest = 'theme_TEST_123456789';

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM config WHERE category = '" . $this->platformTest . "' AND name = 'css'");

        if (is_dir('custom/themes/clients/' . $this->platformTest . '/' . $this->themeTest)) {
            //exec("rm -rf custom/themes/clients/" . $this->platformTest);
            rmdir_recursive("custom/themes/clients/" . $this->platformTest);
        }
        if (is_dir('cache/themes/clients/' . $this->platformTest . '/' . $this->themeTest)) {
            //exec("rm -rf custom/themes/clients/" . $this->platformTest);
            rmdir_recursive("cache/themes/clients/" . $this->platformTest);
        }
        parent::tearDown();
    }

    /**
     * @group rest
     * @group Theming
     */
    public function testPreviewCSS()
    {
        $args1 = array(
            'platform' => $this->platformTest,
            'themeName' => $this->themeTest,
            'BorderColor' => '#75c1d1',
            'NavigationBar' => '#192c47',
            'PrimaryButton' => '#f5b30a',
        );

        $args2 = array(
            'platform' => $this->platformTest,
            'themeName' => $this->themeTest,
            'BorderColor' => '#aaaaaa',
            'NavigationBar' => '#aaaaaa',
            'PrimaryButton' => '#aaaaaa',
        );

        // TEST= GET bootstrap.css with a set of arguments
        $restReply1 = $this->_restCall('css/preview' . $this->rawurlencode($args1));

        // TEST if the the response is not empty
        $this->assertNotEmpty($restReply1['replyRaw']);

        // TEST= GET bootstrap.css with another set of arguments
        $restReply2 = $this->_restCall('css/preview' . $this->rawurlencode($args2));

        // TEST the two generated css are different
        $this->assertInternalType('string', $restReply1['replyRaw']);
        $this->assertNotEquals($restReply1['replyRaw'], $restReply2['replyRaw']);
    }

    /**
     * @group rest
     * @group Theming
     */
    public function testGetCustomThemeVars()
    {
        // TEST= GET theme
        $restReply = $this->_restCall('theme?platform=' . $this->platformTest);

        // TEST we get a hash of variables
        $this->assertEquals(array('name' => 'BorderColor', 'value' => '#E61718'), $restReply['reply']['hex'][0]);
        $this->assertEquals(array('name' => 'NavigationBar', 'value' => '#000000'), $restReply['reply']['hex'][1]);
        $this->assertEquals(array('name' => 'PrimaryButton', 'value' => '#177EE5'), $restReply['reply']['hex'][2]);

        /*
        $this->assertEquals($restReply['reply']['hex'], array(
            0 => array('name' => 'BorderColor', 'value' => '#E61718'),
            1 => array('name' => 'NavigationBar', 'value' => '#000000'),
            2 => array('name' => 'PrimaryButton', 'value' => '#177EE5'),
        ));
        */
    }

    /**
     * @group rest
     * @group Theming
     */
    public function testUpdateCustomTheme()
    {
        $args = array(
            'platform' => $this->platformTest,
            'themeName' => $this->themeTest,
            'BorderColor' => '#75c1d1',
            'NavigationBar' => '#192c47',
            'PrimaryButton' => '#f5b30a',
        );

        // Fake the user is an admin
        $this->_user->is_admin = 1;
        $this->_user->save();
        $GLOBALS['db']->commit();
        // TEST= POST theme
        $restReply = $this->_restCall('theme', json_encode($args));

        $this->_user->is_admin = 0;
        $this->_user->save();
        $GLOBALS['db']->commit();

        // TEST the css files have been created
        $this->assertArrayHasKey('bootstrap', $restReply['reply']);
        $this->assertArrayHasKey('sugar', $restReply['reply']);
        $bootstrapFileName = end(explode('/', $restReply['reply']['bootstrap']));
        $sugarFileName = end(explode('/', $restReply['reply']['sugar']));
        $bootstrapFile = sugar_cached(
            'themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/' . $bootstrapFileName
        );
        $sugarFile = sugar_cached(
            'themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/' . $sugarFileName
        );
        $this->assertFileExists($bootstrapFile, "Created file (" . $bootstrapFileName . ") does not exist");
        $this->assertFileExists($sugarFile, "Created file (" . $sugarFileName . ") does not exist");

        // TEST the css files are not empty
        $this->assertTrue(filesize($bootstrapFile) > 0, "Created file (" . $bootstrapFileName . ") has no contents");
        $this->assertTrue(filesize($sugarFile) > 0, "Created file (" . $sugarFileName . ") has no contents");

        $thisTheme = new SidecarTheme($args['platform'], $args['themeName']);

        // TEST we have updated the variables in variables.less
        $variables = $thisTheme->getThemeVariables();
        $this->assertEquals($args['BorderColor'], $variables['BorderColor']);
        $this->assertEquals($args['NavigationBar'], $variables['NavigationBar']);
        $this->assertEquals($args['PrimaryButton'], $variables['PrimaryButton']);

        // TEST if a config var has been added in the DB
        $query = $GLOBALS['db']->query(
            "SELECT value FROM config WHERE category = '" . $args['platform'] . "' AND name = 'css'"
        );
        $row = $GLOBALS['db']->fetchByAssoc($query);

        // TEST the config var contains the bootstrap.css url
        $this->assertEquals(
        // Some databases (*cough* ORACLE *cough*) are backslash escaping this value
            stripslashes(html_entity_decode($row['value'])),
            stripslashes($restReply['replyRaw']),
            "$row[value] does not match the expected value"
        );
    }

    /**
     * @group rest
     * @group Theming
     */
    public function testResetDefaultTheme()
    {

        $args = array(
            'platform' => $this->platformTest,
            'themeName' => $this->themeTest,
            'reset' => 'true',
        );

        // Fake the user is an admin
        $this->_user->is_admin = 1;
        $this->_user->save();
        $GLOBALS['db']->commit();
        // TEST= POST theme with reset=true
        $restReply = $this->_restCall('theme', json_encode($args));

        $this->_user->is_admin = 0;
        $this->_user->save();
        $GLOBALS['db']->commit();

        // TEST the css files have been created
        $this->assertArrayHasKey('bootstrap', $restReply['reply']);
        $this->assertArrayHasKey('sugar', $restReply['reply']);
        $bootstrapFileName = end(explode('/', $restReply['reply']['bootstrap']));
        $sugarFileName = end(explode('/', $restReply['reply']['sugar']));
        $bootstrapFile = sugar_cached(
            'themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/' . $bootstrapFileName
        );
        $sugarFile = sugar_cached(
            'themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/' . $sugarFileName
        );
        $this->assertFileExists($bootstrapFile, "Created file (" . $bootstrapFileName . ") does not exist");
        $this->assertFileExists($sugarFile, "Created file (" . $sugarFileName . ") does not exist");

        // TEST the css files are not empty
        $this->assertTrue(filesize($bootstrapFile) > 0, "Created file (" . $bootstrapFileName . ") has no contents");
        $this->assertTrue(filesize($sugarFile) > 0, "Created file (" . $sugarFileName . ") has no contents");

        // TEST variables.less file is not empty
        $this->assertNotEmpty(
            file_get_contents(
                'custom/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/variables.less'
            ),
            "Variables.less is not empty"
        );

        // TEST variables.less generated in the custom folder is the same as the default theme
        $defaultTheme = new SidecarTheme($args['platform'], 'default');
        $thisTheme = new SidecarTheme($args['platform'], $args['themeName']);

        // TEST they contain the same variables
        $this->assertEquals(
            $defaultTheme->getThemeVariables(),
            $thisTheme->getThemeVariables()
        );
    }

    /**
     * @group rest
     * @group Theming
     */
    //Bug58031: baseUrl needs to be different for the Theme Editor preview.
    public function testBug58031BaseUrlVariable()
    {

        // TEST 1:  for preview, baseUrl is "../../styleguide/assets"
        $args = array(
            'platform' => $this->platformTest,
            'themeName' => $this->themeTest,
            'BorderColor' => '#75c1d1',
            'NavigationBar' => '#192c47',
            'PrimaryButton' => '#f5b30a',
        );
        $restReply = $this->_restCall('css/preview' . $this->rawurlencode($args));

        // TEST= the CSS contains the expected baseUrl
        $this->assertContains("../../styleguide/assets", $restReply['replyRaw']);
        $this->assertNotContains("../../../../../styleguide/assets", $restReply['replyRaw']);

        // TEST 2:  for deployment, baseUrl is "../../../../../styleguide/assets"
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $variables = $theme->getThemeVariables();
        $css = $theme->compileCss($variables);

        $css = implode(' ', $css);
        // TEST= the CSS contains the expected baseUrl
        $this->assertContains("../../../../../styleguide/assets", $css);
    }

    private function rawurlencode($args)
    {
        $getString = '?';
        foreach ($args as $k => $v) {
            $getString .= $k . '=' . rawurlencode($v) . '&';
        }
        return $getString;
    }
}
