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

class RestTestTheme extends RestTestBase
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
        parent::tearDown();
    }

    public function testGenerateBootstrapCss()
    {
        // Test fetch bootstrap.css
        $restReply = $this->_restCall('bootstrap.css');

        $this->assertNotEmpty($restReply);
    }

    public function testGetCustomThemeVars()
    {
        // Test fetch bootstrap.css
        $restReply = $this->_restCall('theme?platform=' . $this->platformTest);

        $this->assertEquals($restReply['reply']['hex'], array(
            0 => array('name' => 'primary', 'value' => '#E61718'),
            1 => array('name' => 'secondary', 'value' => '#000000'),
            2 => array('name' => 'primaryBtn', 'value' => '#177EE5'),
        ));
    }

    public function testUpdateCustomTheme()
    {
        $args = array(
            'platform' => $this->platformTest,
            'themeName' => $this->themeTest,
            'primary' => '#75c1d1',
            'secondary' => '#192c47',
            'primaryBtn' => '#f5b30a',
        );

        // Fake the user is an admin
        $this->_user->is_admin = 1;
        $this->_user->save();

        // Make a POST request to the ThemeApi
        $restReply = $this->_restCall('theme', json_encode($args));

        $this->_user->is_admin = 0;
        $this->_user->save();

        // check the boostrap.css file has been created
        $this->assertEquals(file_exists('custom/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/bootstrap.css'), true);

        // check the boostrap.css file is not empty
        $this->assertNotEmpty(file_get_contents('custom/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/bootstrap.css'));

        // check if a config var has been added in the DB
        $query = $GLOBALS['db']->query("SELECT value FROM config WHERE category = '" . $args['platform'] . "' AND name = 'css'");
        $row = $GLOBALS['db']->fetchByAssoc($query);

        $this->assertEquals(html_entity_decode($row['value']),
            '"' . $GLOBALS['sugar_config']['site_url'] . '/custom/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/bootstrap.css"');
    }

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

        // Make a POST request to the ThemeApi
        $restUpdateReply = $this->_restCall('theme', json_encode($args));

        $this->_user->is_admin = 0;
        $this->_user->save();

        // check the boostrap.css file has been created
        $this->assertEquals(file_exists('custom/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/bootstrap.css'), true);

        // check the boostrap.css file is not empty
        $this->assertNotEmpty(file_get_contents('custom/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/bootstrap.css'));

        // url of the default variables.less falling back to base platform if no default theme for this platform exists
        if (file_exists('themes/clients/' . $args['platform'] . '/default/variables.less')) {
            $defaultTheme = 'themes/clients/' . $args['platform'] . '/default/variables.less';
        } else {
            $defaultTheme = 'themes/clients/base/default/variables.less';
        }

        $themeApi = new ThemeApi();
        // check that the variables.less generated in the custom folder is the same as the default theme
        $this->assertEquals(
            $themeApi->get_less_vars('custom/themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/variables.less'),
            $themeApi->get_less_vars($defaultTheme)
        );
    }
}
