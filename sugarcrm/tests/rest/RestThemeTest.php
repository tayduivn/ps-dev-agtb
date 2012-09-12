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
require_once('include/api/SugarApi/SugarApi.php');
require_once('include/api/ThemeApi.php');

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
     */
    public function testPreviewCSS()
    {
        $args1 = array(
            'platform' => $this->platformTest,
            'themeName' => $this->themeTest,
            'primary' => '#75c1d1',
            'secondary' => '#192c47',
            'primaryBtn' => '#f5b30a',
        );

        $args2 = array(
            'platform' => $this->platformTest,
            'themeName' => $this->themeTest,
            'primary' => '#aaaaaa',
            'secondary' => '#aaaaaa',
            'primaryBtn' => '#aaaaaa',
        );

        // TEST= GET bootstrap.css with a set of arguments
        $restReply1 = $this->_restCall('css', json_encode($args1));

        // TEST if the the response is not empty
        $this->assertNotEmpty($restReply1);

        // TEST= GET bootstrap.css with another set of arguments
        $restReply2 = $this->_restCall('css', json_encode($args2));

        // TEST the two generated css are different
        $this->assertNotEquals($restReply1, $restReply2);
    }

    /**
     * @group rest
     */
    public function testGetCustomThemeVars()
    {
        // TEST= GET theme
        $restReply = $this->_restCall('theme?platform=' . $this->platformTest);

        // TEST we get a hash of variables
        $this->assertEquals($restReply['reply']['hex'], array(
            0 => array('name' => 'BorderColor', 'value' => '#E61718'),
            1 => array('name' => 'NavigationBar', 'value' => '#000000'),
            2 => array('name' => 'PrimaryButton', 'value' => '#177EE5'),
        ));
    }

    /**
     * @group rest
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
        // TEST the boostrap.css file has been created
        $this->assertTrue(file_exists('cache/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/bootstrap.css'), "Created bootstrap file does not exist");

        // TEST the boostrap.css file is not empty
        $this->assertTrue(filesize('cache/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/bootstrap.css') > 0, "Created file has no contents");
        $thisTheme = new SidecarTheme($args['platform'], $args['themeName']);

        // TEST we have updated the variables in variables.less
        $variables = $thisTheme->getThemeVariables();
        $this->assertEquals($args['BorderColor'], $variables['BorderColor']);
        $this->assertEquals($args['NavigationBar'], $variables['NavigationBar']);
        $this->assertEquals($args['PrimaryButton'], $variables['PrimaryButton']);

        // TEST if a config var has been added in the DB
        $query = $GLOBALS['db']->query("SELECT value FROM config WHERE category = '" . $args['platform'] . "' AND name = 'css'");
        $row = $GLOBALS['db']->fetchByAssoc($query);

        // TEST the config var contains the bootstrap.css url
        $this->assertEquals(html_entity_decode($row['value']),
            '"' . $GLOBALS['sugar_config']['site_url'] . '/cache/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/bootstrap.css"');
    }

    /**
     * @group rest
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
        $this->_restCall('theme', json_encode($args));

        $this->_user->is_admin = 0;
        $this->_user->save();
        $GLOBALS['db']->commit();
        // TEST boostrap.css file has been created
        $this->assertEquals(file_exists('cache/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/bootstrap.css'), true);

        // TEST boostrap.css file is not empty
        $this->assertNotEmpty(file_get_contents('cache/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/bootstrap.css'));

        // TEST variables.less file is not empty
        $this->assertNotEmpty(file_get_contents('custom/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/variables.less'));

        // TEST variables.less generated in the custom folder is the same as the default theme
        $defaultTheme = new SidecarTheme($args['platform'], 'default');
        $thisTheme = new SidecarTheme($args['platform'], $args['themeName']);

        // TEST they contain the same variables
        $this->assertEquals(
            $defaultTheme->getThemeVariables(),
            $thisTheme->getThemeVariables()
        );
    }
}
