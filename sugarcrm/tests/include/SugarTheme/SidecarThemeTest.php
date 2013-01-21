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

require_once 'include/SugarTheme/SidecarTheme.php';

class SidecarThemeTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $platformTest = 'platform_TEST_123456789E_1234';
    private $themeTest = 'theme_TEST_123456789E_1234';

    public function testGetCSSURL()
    {
        // Create a stub for compileBootstrapCss().
        $stub = $this->getMock('SidecarTheme', array('compileBootstrapCss'));
        $stub->expects($this->any())
             ->method('compileBootstrapCss')
             ->will($this->returnValue('.foo {}'));

        // If our theme doesn't have a variables.less file, it should cache the
        // default file.
        $theme = new $stub($this->platformTest, $this->themeTest);
        $defaultTheme = new $stub($this->platformTest, 'default');
        $themePaths = $theme->getPaths();
        $defaultPaths = $defaultTheme->getPaths();

        // Make sure variables.less doesn't exist in the file map.
        SugarAutoLoader::delFromMap($themePaths['custom'] . 'variables.less');
        SugarAutoLoader::delFromMap($themePaths['base'] . 'variables.less');
        SugarAutoLoader::delFromMap($defaultPaths['custom'] . 'variables.less');
        SugarAutoLoader::delFromMap($defaultPaths['base'] . 'variables.less');

        // Make sure our environment is clean. The FileNotExists assertion works
        // on directories as well.
        $this->assertFileNotExists($themePaths['cache']);
        $this->assertFileNotExists($defaultPaths['cache']);
        $this->assertNull(sugar_cache_retrieve($themePaths['hashKey']));
        $this->assertNull(sugar_cache_retrieve($defaultPaths['hashKey']));

        sugar_mkdir($themePaths['custom'], null, true);
        $url = $theme->getCSSURL();
        $this->assertFileExists($url);

        // The fake theme doesn't have a variables.less, so it should only set a
        // cache key for the default theme.
        $this->assertNull(sugar_cache_retrieve($themePaths['hashKey']));
        $this->assertInternalType('string', sugar_cache_retrieve($defaultPaths['hashKey']));

        if (is_dir('custom/themes/clients/' . $this->platformTest . '/' . $this->themeTest)) {
            rmdir_recursive("custom/themes/clients/" . $this->platformTest);
        }

        rmdir_recursive($themePaths['cache']);
        rmdir_recursive($defaultPaths['cache']);
        sugar_cache_clear($themePaths['hashKey']);
        sugar_cache_clear($defaultPaths['hashKey']);
    }

    public function testParseFile()
    {
        //Initiate out test theme
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $paths = $theme->getPaths();

        //Write a sample variables.less to temporary put in /custom/
        $contentsVariablesLess = '
@testHex:                 #E61718;
@testRgba:                 rgba(100, 101, 102);
@testbgPath:                 "zzzz.ww";
@testRel:                 @otherColor;
@textMixin:     mixinChoice;';

        //Save the file
        sugar_mkdir($paths['custom'], null, true);
        sugar_file_put_contents($paths['custom'] . 'variables.less', $contentsVariablesLess);

        // TEST = Parse the created file and verify the parser is correct.
        $variables = $theme->parseFile($paths['custom'], true);

        // Should result this array
        $expectedArray = array(
            'mixins' => array(
                array(
                    'name' => 'textMixin',
                    'value' => 'mixinChoice'
                ),
            ),
            'hex' => array(
                array(
                    'name' => 'testHex',
                    'value' => '#E61718'
                ),
            ),
            'rgba' => array(
                array(
                    'name' => 'testRgba',
                    'value' => 'rgba(100, 101, 102)'
                ),
            ),
            'rel' => array(
                array(
                    'name' => 'testRel',
                    'value' => '@otherColor'
                ),
            ),
            'bg' => array(
                array(
                    'name' => 'testbgPath',
                    'value' => 'zzzz.ww'
                ),
            ),
        );

        // TEST Result
        $this->assertEquals($expectedArray, $variables, 'SidecarTheme retrieves all variables');

        // Remove our temporary file
        if (is_dir('custom/themes/clients/' . $this->platformTest . '/' . $this->themeTest)) {
            rmdir_recursive("custom/themes/clients/" . $this->platformTest);
        }
    }

}
