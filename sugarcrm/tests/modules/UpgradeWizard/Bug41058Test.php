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

require_once 'modules/UpgradeWizard/uw_utils.php';

class Bug41058Test extends Sugar_PHPUnit_Framework_OutputTestCase {

    var $user;
    var $backupConfig;
    var $backupSystemLocaleNameFormat;
    var $loc;

    public function setUp() {
        global $sugar_config;

        $this->backupConfig = $sugar_config;

        $this->user = SugarTestUserUtilities::createAnonymousUser(true);

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '1';
	    $GLOBALS['current_user']->save();

        $this->loc = new Localization();
        if ($this->loc->invalidLocaleNameFormatUpgrade()) {
            rename($this->loc->invalidNameFormatUpgradeFilename, $this->loc->invalidNameFormatUpgradeFilename.'.backup');
        }
    }

    public function tearDown() {
        global $sugar_config, $sugar_version, $mod_strings;

        if (file_exists($this->loc->invalidNameFormatUpgradeFilename)) {
            unlink($this->loc->invalidNameFormatUpgradeFilename);
        }
        if (file_exists($this->loc->invalidNameFormatUpgradeFilename.'.backup')) {
            rename($this->loc->invalidNameFormatUpgradeFilename.'.backup', $this->loc->invalidNameFormatUpgradeFilename);
        }
        unset($this->loc);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($GLOBALS['current_user']);

        $sugar_config = $this->backupConfig;
        if(!rebuildConfigFile($sugar_config, $sugar_version)) {
            logThis('*** ERROR: could not write config.php!');
            $errors[] = $mod_strings['ERR_UW_CONFIG_WRITE'];
        }

        unset($this->backupSystemLocaleNameFormat);
        unset($sugar_config);
        unset($sugar_version);
        unset($mod_strings);
        unset($app_strings);
        unset($app_list_strings);
        unset($locale);
        unset($_REQUEST);
    }


    /**
     * Tests that Localization::isAllowedNameFormat returns true for valid name formats
     * @param $name_format valid name format from dataProvider
     * @dataProvider goodLocaleNameFormatProvider
     */
    public function testCheckReturnsTrueForValidNameFormats($name_format) {
        $this->assertTrue(Localization::isAllowedNameFormat($name_format));
    }

    /**
     * Tests that Localization::isAllowedNameFormat returns false for invalid name formats
     * @param $name_format invalid name format from dataProvider
     * @dataProvider badLocaleNameFormatProvider
     */
    public function testCheckReturnsFalseForInvalidNameFormats($name_format) {
        $this->assertFalse(Localization::isAllowedNameFormat($name_format));
    }

    /**
     * Tests that good Locale Name Format strings from user preferences get added to the config
     * @param $name_format valid name format from data provider
     * @dataProvider goodLocaleNameFormatProvider
     * @depends testCheckReturnsTrueForValidNameFormats
     */
    public function testUserPreferenceForLocaleNameFormatUpgrade($name_format) {
        global $sugar_config;

        $this->user->setPreference('default_locale_name_format', $name_format);
        $this->user->savePreferencesToDB();
        $this->user->save();

        $ulnf = $this->user->getPreference('default_locale_name_format');
        $this->assertSame($name_format, $ulnf);

        $this->assertArrayNotHasKey($name_format, $sugar_config['name_formats']);
        upgradeUserPreferences();
        $this->assertArrayHasKey($name_format, $sugar_config['name_formats']);
    }

    /**
     * Tests that bad Locale Name Format strings from user preferences do not get added to the config
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends testCheckReturnsFalseForInvalidNameFormats
     */
    public function testBadUserPreferenceForLocaleNameFormatUpgrade($name_format) {
        global $sugar_config;

        $this->user->setPreference('default_locale_name_format', $name_format);
        $this->user->savePreferencesToDB();
        $this->user->save();

        $ulnf = $this->user->getPreference('default_locale_name_format');
        $this->assertSame($name_format, $ulnf);

        $this->assertArrayNotHasKey($name_format, $sugar_config['name_formats']);
        upgradeUserPreferences();
        $this->assertArrayNotHasKey($name_format, $sugar_config['name_formats']);
        $coreDefaults = $this->loc->getLocaleConfigDefaults();
        $this->assertSame($coreDefaults['default_locale_name_format'], $this->user->getPreference('default_locale_name_format'));
    }


    /**
     * Tests that good Locale Name Format strings from global preference get added to the config
     * @param $name_format valid name format from data provider
     * @dataProvider goodLocaleNameFormatProvider
     * @depends testCheckReturnsTrueForValidNameFormats
     */
    public function testGlobalPreferenceForLocaleNameFormatUpgrade($name_format) {
        global $sugar_config;

        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        $this->assertNotSame($name_format, $sugar_config['default_locale_name_format']);
        $sugar_config['default_locale_name_format'] = $name_format;
        upgradeUserPreferences();
        require ('config.php');
        $this->assertSame($name_format, $sugar_config['default_locale_name_format']);
        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
    }

    /**
     * Tests that bad Locale Name Format strings from global preference do not get added to the config
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends testCheckReturnsFalseForInvalidNameFormats
     */
    public function testInvalidGlobalPreferenceForLocaleNameFormatUpgrade($name_format) {
        global $sugar_config;

        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        $this->assertNotSame($name_format, $sugar_config['default_locale_name_format']);
        $sugar_config['default_locale_name_format'] = $name_format;
        upgradeUserPreferences();
        $this->assertNotSame($name_format, $sugar_config['default_locale_name_format']);
        require ('config.php');
        $coreDefaults = $this->loc->getLocaleConfigDefaults();
        $this->assertSame($coreDefaults['default_locale_name_format'], $sugar_config['default_locale_name_format']);
        $this->assertFileExists($this->loc->invalidNameFormatUpgradeFilename);
    }

    /**
     * Tests that UI presents a message on the locale settings page when there was an invalid name format during an upgrade
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends testCheckReturnsFalseForInvalidNameFormats
     */
    public function testMessageIsShownWhenInvalidLocaleNameFormatIsFoundInUpgrade($name_format) {
        global $sugar_config, $locale, $app_strings, $app_list_strings;

        require('modules/Administration/language/en_us.lang.php');

        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        $sugar_config['default_locale_name_format'] = $name_format;
        upgradeUserPreferences();
        $this->assertFileExists($this->loc->invalidNameFormatUpgradeFilename);

        $this->expectOutputRegex('/'.$mod_strings['ERR_INVALID_LOCALE_NAME_FORMAT_UPGRADE'].'/');
        require('modules/Administration/Locale.php');
    }

    /**
     * Tests that UI does not present a message on the locale settings page when there wasn't an invalid name format during an upgrade
     * @param $name_format valid name format from data provider
     * @dataProvider goodLocaleNameFormatProvider
     * @depends testCheckReturnsTrueForValidNameFormats
     */
    public function testMessageIsNotShownWhenNoInvalidLocaleNameFormatIsFoundInUpgrade($name_format) {
        global $sugar_config, $locale, $app_strings, $app_list_strings;

        require('modules/Administration/language/en_us.lang.php');

        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        $sugar_config['default_locale_name_format'] = $name_format;
        upgradeUserPreferences();
        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);

        $this->expectOutputNotRegex('/'.$mod_strings['ERR_INVALID_LOCALE_NAME_FORMAT_UPGRADE'].'/');
        require('modules/Administration/Locale.php');
        
    }

    /**
     * Test that file gets removed after a save from Locale page
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends testCheckReturnsFalseForInvalidNameFormats
     */
    public function testFileGetsRemovedAfterLocaleSave($name_format) {
        global $sugar_config, $locale, $app_strings, $app_list_strings;
        require('modules/Administration/language/en_us.lang.php');

        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        $sugar_config['default_locale_name_format'] = $name_format;
        upgradeUserPreferences();
        $this->assertFileExists($this->loc->invalidNameFormatUpgradeFilename);

        try {
            $_REQUEST['process'] = 'true';
            require('modules/Administration/Locale.php');
        } catch (Exception $e) {
//            $this->assertContains('Cannot modify header information - headers already sent', $e->getMessage());
            $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        }
    }

    /**
     * Data provider of allowed name formats
     * @return array of allowed name format strings
     */
    public function goodLocaleNameFormatProvider() {
        $goodFormatsArray = array(
            array('`l` `f` `s`'),
            array('l_f_s'),
            array('*-s-f-l-*'),
            array('{[`~!@#$%^&*()_-+=;:\'"/?\\|.>s, f, l    <]}'),
        );

        return $goodFormatsArray;
    }

    /**
     * Data provider of disallowed name formats
     * @return array of disallowed name format strings
     */
    public function badLocaleNameFormatProvider() {
        $badFormatsArray = array(
            array('`l` `f` `s`: `t`'),
            array('alpha-bits'),
            array('*-s-f-l-*-bad_name_format'),
            array('bad{[`~!@#$%^&*()_-+=;:\'"/?\\|.>s, f, l    <]}'),
        );

        return $badFormatsArray;
    }

}
?>
