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
require_once 'tests/SugarTestReflection.php';

class SidecarThemeTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $platformTest = 'platform_TEST_123456789E_1234';
    private $themeTest = 'theme_TEST_123456789E_1234';

    public function tearDown()
    {
        // Clear out the test folders
        $customDir = 'custom/themes/clients/' . $this->platformTest;
        if (is_dir($customDir)) {
            rmdir_recursive($customDir);
        }
        $cacheDir = 'cache/themes/clients/' . $this->platformTest;
        if (is_dir($cacheDir)) {
            rmdir_recursive($cacheDir);
        }
        parent::tearDown();
    }

    /**
     * @group Theming
     */
    public function testGetCSSURL()
    {
        // If our theme doesn't have a variables.less file, it should cache the
        // default file.
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $defaultTheme = new SidecarTheme($this->platformTest, 'default');
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

        $urls = $theme->getCSSURL();
        $this->assertArrayHasKey('bootstrap', $urls);
        $this->assertArrayHasKey('sugar', $urls);
        foreach ($urls as $url) {
            $this->assertFileExists($url, 'The CSS (' . $url . ') file should be found');
        }

        // The fake theme doesn't have a variables.less, so it should only set a
        // cache key for the default theme.
        $this->assertNull(sugar_cache_retrieve($themePaths['hashKey']));
        $this->assertInternalType('array', sugar_cache_retrieve($defaultPaths['hashKey']));

        if (is_dir($defaultPaths['cache'])) {
            rmdir_recursive($defaultPaths['cache']);
        }
        sugar_cache_clear($themePaths['hashKey']);
        sugar_cache_clear($defaultPaths['hashKey']);
    }

    /**
     * @group Theming
     */
    public function testCompileTheme() {
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $themePaths = $theme->getPaths();

        $this->assertFileNotExists($themePaths['cache']);
        $files = $theme->compileTheme();

        $this->assertArrayHasKey('bootstrap', $files);
        $this->assertArrayHasKey('sugar', $files);

        foreach ($files as $lessFile => $hash) {
            $this->assertFileExists($themePaths['cache'] . $lessFile . '_' . $hash . '.css', 'The CSS file should be found');
        }
    }

    /**
     * @group Theming
     */
    public function testPreviewCss() {
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $themePaths = $theme->getPaths();

        $this->assertFileNotExists($themePaths['cache']);
        $css = $theme->previewCss();
        $this->assertInternalType('string', $css);
        $this->assertFileNotExists($themePaths['cache']);
    }

    /**
     * @group Theming
     */
    public function testCompileFile() {
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $themePaths = $theme->getPaths();

        $files = glob($themePaths['cache'] . '*.css');
        $this->assertEquals(sizeof($files), 0, 'There should be 0 css file');

        $this->assertFileNotExists($themePaths['cache'] . 'boostrap');
        $hash = $theme->compileFile('bootstrap');

        $this->assertFileNotExists($themePaths['cache'] . 'boostrap_' . $hash .'.css', 'The css file should have been created.');
    }

    /**
     * @group Theming
     */
    public function testGetUserPreferredTheme()
    {
        $oldPreferredTheme = null;
        $preferredTheme = 'MyTestPreferredTheme';

        // Save preferred theme stored in session
        if (isset($_SESSION['authenticated_user_theme'])) {
            $oldPreferredTheme = $_SESSION['authenticated_user_theme'];
        }
        $_SESSION['authenticated_user_theme'] = $preferredTheme;


        // Create a theme without defining a themeName
        $theme = new SidecarTheme($this->platformTest, null);
        $paths = $theme->getPaths();

        $userTheme = SugarTestReflection::callProtectedMethod($theme, 'getUserTheme');
        $this->assertEquals($preferredTheme, $userTheme, 'It should pick the theme name stored in session');

        $this->assertEquals(
            $paths['base'],
            'styleguide/themes/clients/' . $this->platformTest . '/' . $preferredTheme . '/',
            'It should have retrieve the theme name stored in session'
        );

        // Reset session var
        unset($_SESSION['authenticated_user_theme']);
        if ($oldPreferredTheme) {
            $_SESSION['authenticated_user_theme'] = $oldPreferredTheme;
        }
    }

    /**
     * @group Theming
     */
    public function testLoadVariables()
    {
        // Create a stub for getThemeVariables().
        $mockThemeVariables = array(
            'type1' => array(
                'Variable11' => 'Value12',
                'Variable12' => 'Value12',
            ),
            'type2' => array(
                'Variable21' => 'Value21',
                'Variable22' => 'Value22',
            ),
        );
        // Create a stub for the SomeClass class.
        $theme = $this->getMock('SidecarTheme', array('getThemeVariables'), array($this->platformTest, $this->themeTest));
        $theme->expects($this->any())
            ->method('getThemeVariables')
            ->will($this->returnValue($mockThemeVariables));

        $expected = array(
            'Variable11' => 'Value12',
            'Variable12' => 'Value12',
            'Variable21' => 'Value21',
            'Variable22' => 'Value22',
        );
        $actual = $theme->loadVariables();
        $this->assertEquals($expected, $actual, 'It should have set variables correctly');
    }

    /**
     * @group Theming
     */
    public function testIsDefined()
    {
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $themePaths = $theme->getPaths();
        $customPaths = $themePaths['custom'];
        $this->assertFalse($theme->isDefined(), 'Should say this theme does not exist');

        sugar_mkdir($customPaths, null, true);
        sugar_file_put_contents($customPaths . 'variables.less', '');

        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $this->assertTrue($theme->isDefined(), 'Should say this theme exists');
        rmdir_recursive($customPaths);
    }

    /**
     * @group Theming
     */
    public function testGetThemeVariables() {
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
        $variables = $theme->getThemeVariables();

        // Should result this array
        $expectedArray = array(
            'mixins' => array(
                'textMixin' => 'mixinChoice'
            ),
            'hex' => array(
                'testHex' => '#E61718',
                'BorderColor' => '#E61718',
                'NavigationBar' => '#000000',
                'PrimaryButton' => '#177EE5',
            ),
            'rgba' => array(
                'testRgba' => 'rgba(100, 101, 102)'
            ),
            'rel' => array(
                'testRel' => '@otherColor'
            ),
            'bg' => array(
                'testbgPath' => 'zzzz.ww'
            ),
        );

        // TEST Result
        $this->assertEquals($expectedArray, $variables, 'It should retrieve all variables');
    }

    /**
     * @group Theming
     */
    public function testSaveThemeVariables() {
        //Initiate out test theme
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $paths = $theme->getPaths();

        $this->assertFileNotExists($paths['custom'] . 'variables.less');

        //Write a sample variables.less to temporary put in /custom/
        $this->testLoadVariables();
        $theme->setVariable('BorderColor', '#FFFFFF');

        $theme->saveThemeVariables();

        $this->assertFileExists($paths['custom'] . 'variables.less', 'metadata file should have been created');

        $variables = $theme->getThemeVariables();

        // Should result this array
        $expectedArray = array(
            'mixins' => array(
            ),
            'hex' => array(
                'BorderColor' => '#FFFFFF',
                'NavigationBar' => '#000000',
                'PrimaryButton' => '#177EE5',
            ),
            'rgba' => array(
            ),
            'rel' => array(
            ),
            'bg' => array(
            ),
        );

        // TEST Result
        $this->assertEquals($expectedArray, $variables, 'It should have updated the variable');

        //Reset default theme
        $theme->saveThemeVariables(true);
        $variables = $theme->getThemeVariables();

        // TEST variables.less has been removed
        $this->assertFileNotExists($paths['custom'] . 'variables.less', 'Variables.less has not been removed');

        // TEST Result
        $this->assertNotEquals($expectedArray, $variables, 'It should reset base default theme variables');
    }

    /**
     * @group Theming
     */
    public function testRetrieveCssFilesInCache()
    {
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $themePaths = $theme->getPaths();

        // Clear out the path
        sugar_mkdir($themePaths['cache'], null, true);

        $files = SugarTestReflection::callProtectedMethod($theme, 'retrieveCssFilesInCache');
        $this->assertEmpty($files, 'Should have found 0 file');

        $testCacheFile = $themePaths['cache'] . 'bootstrap_unittest.css';
        file_put_contents($testCacheFile, 'This is a unit test CSS file.');

        $files = SugarTestReflection::callProtectedMethod($theme, 'retrieveCssFilesInCache');
        $this->assertArrayHasKey('bootstrap', $files, 'Should have found 1 file');
        $this->assertEquals($files['bootstrap'], 'unittest', 'Should retrieve the hash');

        $testCacheFile = $themePaths['cache'] . 'sugar_unittest2.css';
        file_put_contents($testCacheFile, 'This is a unit test CSS file.');

        $files = SugarTestReflection::callProtectedMethod($theme, 'retrieveCssFilesInCache');
        $this->assertArrayHasKey('bootstrap', $files, 'Should have found 2 files');
        $this->assertArrayHasKey('sugar', $files, 'Should have found 2 files');
        $this->assertEquals($files['sugar'], 'unittest2', 'Should retrieve the hash');
    }

    /**
     * @group Theming
     */
    public function testGetLessFileLocation()
    {
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $themePaths = $theme->getPaths();

        $url = SugarTestReflection::callProtectedMethod($theme,'getLessFileLocation',array('bootstrap'));
        $this->assertEquals($url, 'styleguide/less/clients/base/bootstrap.less');

        //Save the file
        $path = 'styleguide/less/clients/' . $this->platformTest . '/';
        sugar_mkdir($path, null, true);
        sugar_file_put_contents($path . 'bootstrap.less', '');

        //Make sure
        $url = SugarTestReflection::callProtectedMethod($theme,'getLessFileLocation',array('bootstrap'));
        $this->assertEquals($url, 'styleguide/less/clients/' . $this->platformTest . '/bootstrap.less');

        // Remove our temporary
        if (is_dir($path)) {
            rmdir_recursive($path);
        }
    }
}
