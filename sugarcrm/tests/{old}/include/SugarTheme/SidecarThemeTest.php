<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

class SidecarThemeTest extends TestCase
{
    private $platformTest = 'platform_TEST_123456789E_1234';
    private $themeTest = 'theme_TEST_123456789E_1234';

    protected function tearDown() : void
    {
        SugarCache::instance()->flush();
        // Clear out the test folders
        $customDir = 'custom/themes/clients/' . $this->platformTest;
        if (is_dir($customDir)) {
            rmdir_recursive($customDir);
        }
        $cacheDir = 'cache/themes/clients/' . $this->platformTest;
        if (is_dir($cacheDir)) {
            rmdir_recursive($cacheDir);
        }
        $baseDir = 'styleguide/themes/clients/' . $this->platformTest;
        if (is_dir($baseDir)) {
            rmdir_recursive($baseDir);
        }
    }

    /**
     * @group Theming
     */
    public function testGetCSSURL()
    {
        // If our theme doesn't have a variables.php file, it should cache the
        // default file.
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $defaultTheme = new SidecarTheme($this->platformTest, 'default');
        $themePaths = $theme->getPaths();
        $defaultPaths = $defaultTheme->getPaths();

        // Make sure our environment is clean. The FileNotExists assertion works
        // on directories as well.
        $this->assertFileDoesNotExist($themePaths['cache']);
        $this->assertFileDoesNotExist($defaultPaths['cache']);
        $this->assertNull(sugar_cache_retrieve($themePaths['hashKey']));
        $this->assertNull(sugar_cache_retrieve($defaultPaths['hashKey']));

        $urls = $theme->getCSSURL();
        $this->assertArrayHasKey('sugar', $urls);
        foreach ($urls as $url) {
            $this->assertFileExists($url, 'The CSS (' . $url . ') file should be found');
        }

        // The fake theme doesn't have a variables.php, so it should only set a
        // cache key for the default theme.
        $this->assertNull(sugar_cache_retrieve($themePaths['hashKey']));
        $this->assertIsArray(sugar_cache_retrieve($defaultPaths['hashKey']));

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

        $this->assertFileDoesNotExist($themePaths['cache']);
        $files = $theme->compileTheme();

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

        $this->assertFileDoesNotExist($themePaths['cache']);
        $css = $theme->previewCss();
        $this->assertIsString($css);
        $this->assertFileDoesNotExist($themePaths['cache']);
    }

    /**
     * @group Theming
     */
    public function testCompileFile() {
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);
        $themePaths = $theme->getPaths();

        $files = glob($themePaths['cache'] . '*.css');
        $this->assertEquals(sizeof($files), 0, 'There should be 0 css file');

        $this->assertFileDoesNotExist($themePaths['cache'] . 'sugar');
        $hash = $theme->compileFile('sugar');

        $this->assertFileExists($themePaths['cache'] . 'sugar_' . $hash .'.css', 'The css file should have been created.');
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
        $this->assertEquals(
            'default',
            $userTheme,
            'Multiple themes are no longer supported. It should return default'
        );

        $this->assertEquals(
            'styleguide/themes/clients/' . $this->platformTest . '/default/',
            $paths['base'],
            'Multiple themes are no longer supported. It should always load default theme'
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
        $theme = $this->getMockBuilder('SidecarTheme')
            ->setMethods(array('getThemeVariables'))
            ->setConstructorArgs(array($this->platformTest, $this->themeTest))
            ->getMock();
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
        sugar_file_put_contents($customPaths . 'variables.php', '');

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

        $platformTestDefault = new SidecarTheme($this->platformTest, 'default');
        $platformTestDefaultPaths = $platformTestDefault->getPaths();

        //Write a sample variables.php to temporary put in /custom/
        $platformTestDefaultVariablesLess = '<?php
        $lessdefs = array(
            "colors" => array(
                "BorderColor" => "#aaaaaa",
                "NavigationBar" => "#bbbbbb",
                "testColor" => "#cccccc",
                "testRgba" => "rgba(100, 101, 102)",
            ),
            "bgPath" => array(
                "testbgPath" => "zzzz.ww",
            ),
            "rel" => array(
                "testRel" => "@otherColor",
            ),
            "mixins" => array(
                "textMixin" => "mixinChoice",
            ),
        );';

        $platformTestCustomVariablesLess = '<?php
        $lessdefs = array(
            "colors" => array(
                "BorderColor" => "#000000",
                "NavigationBar" => "#111111",
                "non_customizable_var" => "#222222",
            ),
            "bgPath" => array(
                "testbgPath" => "other_background.png",
            ),
        );';

        //Save the file
        sugar_mkdir($platformTestDefaultPaths['base'], null, true);
        sugar_file_put_contents($platformTestDefaultPaths['base'] . 'variables.php', $platformTestDefaultVariablesLess);

        //Save the file
        sugar_mkdir($paths['custom'], null, true);
        sugar_file_put_contents($paths['custom'] . 'variables.php', $platformTestCustomVariablesLess);

        // TEST = Parse the created file and verify the parser is correct.
        $variables = $theme->getThemeVariables();


        // Should result this array
        $expectedArray = array(
            'mixins' => array(
                'textMixin' => 'mixinChoice'
            ),
            'colors' => array(
                'BorderColor' => '#000000',
                'NavigationBar' => '#111111',
                'PrimaryButton' => '#0679c8', //base theme var
                "testColor" => "#cccccc",
                'testRgba' => 'rgba(100, 101, 102)'
            ),
            'rel' => array(
                'testRel' => '@otherColor'
            ),
            'bgPath' => array(
                'testbgPath' => 'other_background.png'
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

        $this->assertFileDoesNotExist($paths['custom'] . 'variables.php');

        //Write a sample variables.php to temporary put in /custom/
        $this->testLoadVariables();
        $theme->setVariable('BorderColor', '#FFFFFF');

        $theme->saveThemeVariables();

        $this->assertFileExists($paths['custom'] . 'variables.php', 'metadata file should have been created');

        $variables = $theme->getThemeVariables();

        // Should result this array
        $expectedArray = array(
            'colors' => array(
                'BorderColor' => '#FFFFFF',
                'NavigationBar' => '#fff',
                'PrimaryButton' => '#0679c8',
            ),
        );

        // TEST Result
        $this->assertEquals($expectedArray, $variables, 'It should have updated the variable');

        //Reset default theme
        $theme->saveThemeVariables(true);
        $variables = $theme->getThemeVariables();

        // TEST variables.php has been removed
        $this->assertFileDoesNotExist($paths['custom'] . 'variables.php', 'Variables.less has not been removed');

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

        $testCacheFile = $themePaths['cache'] . 'sugar_unittest2.css';
        file_put_contents($testCacheFile, 'This is a unit test CSS file.');

        $files = SugarTestReflection::callProtectedMethod($theme, 'retrieveCssFilesInCache');
        $this->assertArrayHasKey('sugar', $files, 'Should have found 1 file');
        $this->assertEquals($files['sugar'], 'unittest2', 'Should retrieve the hash');
    }

    /**
     * @group Theming
     */
    public function testGetLessFileLocation()
    {
        $theme = new SidecarTheme($this->platformTest, $this->themeTest);

        $url = SugarTestReflection::callProtectedMethod($theme,'getLessFileLocation',array('sugar'));
        $this->assertEquals($url, 'styleguide/less/clients/base/sugar.less');

        //Save the file
        $path = 'styleguide/less/clients/' . $this->platformTest . '/';
        sugar_mkdir($path, null, true);
        sugar_file_put_contents($path . 'sugar.less', '');

        //Make sure
        $url = SugarTestReflection::callProtectedMethod($theme,'getLessFileLocation',array('sugar'));
        $this->assertEquals($url, 'styleguide/less/clients/' . $this->platformTest . '/sugar.less');

        // Remove our temporary
        if (is_dir($path)) {
            rmdir_recursive($path);
        }
    }
}
