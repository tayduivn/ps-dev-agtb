<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once "tests/upgrade/UpgradeTestCase.php";
require_once 'upgrade/scripts/post/7_FixCustomLabelsForCoreModules.php';

/**
 * Class FixCustomModuleLabelsTest test for SugarUpgradeFixCustomModuleLabels upgrader script
 */
class FixCustomLabelsForCoreModulesTest extends UpgradeTestCase
{
    /** @var SugarUpgradeFixCustomLabelsForCoreModules */
    protected $script;

    public function setUp()
    {
        parent::setUp();

        /** @var SugarUpgradeFixCustomLabelsForCoreModules */
        $this->script = $this->upgrader->getScript('post', '7_FixCustomLabelsForCoreModules');

        $GLOBALS['sugar_config']['languages'] =
            array(
                'en_us'       => 'English (US)',
                'test_test'   => 'Test',
                'test2_test2' => 'Test 2',
            );
    }

    /**
     *
     * @dataProvider providerDataForUpgradeLabels
     * @param string $module
     * @param string $language
     * @param array $customLabels
     * @param array $labelsToChange
     * @param array $expected
     */
    public function testUpgradeModuleLabels($module, $language, $customLabels, $labelsToChange, $expected)
    {
        $this->script->upgradeLabels = array($module => $labelsToChange);

        // Prepare language files with customizations
        $path = 'custom/modules/' . $module . '/language/' . $language. '.lang.php';
        mkdir_recursive(dirname($path));
        SugarTestHelper::saveFile($path);
        write_array_to_file('mod_strings', $customLabels, $path);

        $this->script->upgradeModuleLabels($module, $language);
        $mod_strings = array();
        include $path;

        $this->assertEquals($expected, $mod_strings);
    }

    public function providerDataForUpgradeLabels()
    {
        return
            array(
                array(
                    'TestModule1',
                    'en_us',
                    array(
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                    ),
                    array(
                        'LBL_OLD_1' => 'LBL_NEW_1',
                        'LBL_OLD_2' => 'LBL_NEW_2',
                    ),
                    array(
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                        'LBL_NEW_1' => 'OLD_VALUE_1',
                        'LBL_NEW_2' => 'OLD_VALUE_2',
                    ),
                ), // Generic behavior
                array(
                    'TestModule1',
                    'test_test',
                    array(
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                    ),
                    array(
                        'LBL_OLD_1' => 'LBL_NEW_1',
                        'LBL_OLD_2' => 'LBL_NEW_2',
                    ),
                    array(
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                        'LBL_NEW_1' => 'OLD_VALUE_1',
                        'LBL_NEW_2' => 'OLD_VALUE_2',
                    )
                ), // Not default language
                array(
                    'TestModule1',
                    'test_test',
                    array(
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                    ),
                    array(
                        'LBL_OLD_ANOTHER_1' => 'LBL_NEW_1',
                        'LBL_OLD_ANOTHER_2' => 'LBL_NEW_2',
                    ),
                    array(
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                    )
                ), // No customizations were done for required labels, so file should not be modified
            );
    }

    public function testRun()
    {
        $this->script->upgradeLabels = array(
            'TestModule1' => array(
                'LBL_OLD_1' => 'LBL_NEW_1',
                'LBL_OLD_2' => 'LBL_NEW_2',
            ),
        );

        // Prepare language files with customizations
        $languages = array(
            'en_us'     => 'en_us',
            'test_test' => 'test_test',
        );
        $customLabels = array(
            'LBL_MODULE_NAME' => 'TestModule1',
            'LBL_OLD_1'       => 'OLD_VALUE_1',
            'LBL_OLD_2'       => 'OLD_VALUE_2',
        );

        foreach ($languages as $key => $value) {
            $path = 'custom/modules/TestModule1/language/' . $key. '.lang.php';
            mkdir_recursive(dirname($path));
            SugarTestHelper::saveFile($path);
            write_array_to_file('mod_strings', $customLabels, $path);
        }

        $moduleInstaller = $this->getMock('ModuleInstaller', array('rebuild_languages'));
        $moduleInstaller->expects($this->once())
            ->method('rebuild_languages')
            ->with($languages, array('TestModule1'));

        $this->script->mi = $moduleInstaller;
        $this->script->run();
    }

    /**
     * Test that no changes were made to label customization
     */
    public function testRunNoCustomization()
    {
        $this->script->upgradeLabels = array(
            'TestModule1' => array(
                'LBL_OLD_1' => 'LBL_NEW_1',
                'LBL_OLD_2' => 'LBL_NEW_2',
            ),
        );

        // Prepare language files with customizations
        $languages = array(
            'en_us'     => 'en_us',
            'test_test' => 'test_test',
        );
        $customLabels = array(
            'LBL_MODULE_NAME'   => 'TestModule1',
            'LBL_ANOTHER_KEY_1' => 'VALUE_1',
            'LBL_ANOTHER_KEY_2' => 'VALUE_2',
        );

        foreach ($languages as $key => $value) {
            $path = 'custom/modules/TestModule1/language/' . $key. '.lang.php';
            mkdir_recursive(dirname($path));
            SugarTestHelper::saveFile($path);
            write_array_to_file('mod_strings', $customLabels, $path);
        }

        // Rebuild should never be called cause there are no customizations for test labels in test module
        $moduleInstaller = $this->getMock('ModuleInstaller', array('rebuild_languages'));
        $moduleInstaller->expects($this->never())
            ->method('rebuild_languages');

        $this->script->mi = $moduleInstaller;
        $this->script->run();
    }
}
