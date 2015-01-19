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

require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/pre/LoadDropdowns.php';

/**
 * @covers SugarUpgradeLoadDropdowns
 */
class SugarUpgradeLoadDropdownsTest extends UpgradeTestCase
{
    protected $corePath = 'include/language/';
    protected $customPath = 'custom/include/language/';
    protected $script;

    public function setUp()
    {
        parent::setUp();

        $coreFiles = glob("{$this->corePath}*.lang.php");
        SugarTestLanguageFileUtilities::backup($coreFiles);
        SugarTestLanguageFileUtilities::remove($coreFiles);

        $customFiles = glob("{$this->customPath}*.lang.php");
        SugarTestLanguageFileUtilities::backup($customFiles);
        SugarTestLanguageFileUtilities::remove($customFiles);

        sugar_cache_clear("app_strings.{$GLOBALS['current_language']}");
        sugar_cache_clear("app_list_strings.{$GLOBALS['current_language']}");

        $this->script = $this->upgrader->getScript('pre', 'LoadDropdowns');
    }

    public function tearDown()
    {
        SugarTestLanguageFileUtilities::clearCache();

        parent::tearDown();
    }

    /**
     * @covers SugarUpgradeLoadDropdowns::run
     */
    public function testRun_IdentifiesCustomizedDropdowns()
    {
        $core = <<<EOF
\$app_list_strings = array(
    'sales_stage_default_key' => 'Prospecting',
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'Task',
        'Email' => 'Email',
        'Note' => 'Note',
    ),
    'meeting_status_dom' => array(
        'Planned' => 'Planned',
        'Held' => 'Held',
        'Not Held' => 'Not Held',
    ),
);

EOF;

        $custom = <<<EOF
\$app_list_strings = array(
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'To Do',
        'Email' => 'Email',
        'Note' => 'Note',
        'SMS' => 'Text Message',
    ),
);

EOF;

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $core);
        SugarTestLanguageFileUtilities::write($this->customPath, $GLOBALS['current_language'], $custom);

        $this->script->run();

        $this->assertArrayHasKey('old', $this->upgrader->state['dropdowns_to_merge'][$GLOBALS['current_language']]);
        $this->assertArrayHasKey('custom', $this->upgrader->state['dropdowns_to_merge'][$GLOBALS['current_language']]);
        $this->assertArrayHasKey(
            'activity_dom',
            $this->upgrader->state['dropdowns_to_merge'][$GLOBALS['current_language']]['old']
        );
        $this->assertArrayHasKey(
            'activity_dom',
            $this->upgrader->state['dropdowns_to_merge'][$GLOBALS['current_language']]['custom']
        );
    }

    /**
     * @covers SugarUpgradeLoadDropdowns::run
     */
    public function testRun_NoCustomizations_NothingToMerge()
    {
        $core = <<<EOF
\$app_list_strings = array(
    'sales_stage_default_key' => 'Prospecting',
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'Task',
        'Email' => 'Email',
        'Note' => 'Note',
    ),
);

EOF;

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $core);

        $this->script->run();

        $this->assertEmpty($this->upgrader->state['dropdowns_to_merge']);
    }

    /**
     * @covers SugarUpgradeLoadDropdowns::run
     */
    public function testRun_MultipleLanguagesAreCustomized()
    {
        $secondLanguage = $GLOBALS['current_language'] === 'es_ES' ? 'en_us' : 'es_ES';
        $secondLanguageCoreFile = "{$this->corePath}{$secondLanguage}.lang.php";
        $secondLanguageCustomFile = "{$this->customPath}{$secondLanguage}.lang.php";

        if (!isset(SugarTestHelper::$oldFiles[$secondLanguageCoreFile])) {
            SugarTestHelper::saveFile($secondLanguageCoreFile);
        }

        if (!isset(SugarTestHelper::$oldFiles[$secondLanguageCustomFile])) {
            SugarTestHelper::saveFile($secondLanguageCustomFile);
        }

        $core = <<<EOF
\$app_list_strings = array(
    'sales_stage_default_key' => 'Prospecting',
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'Task',
        'Email' => 'Email',
        'Note' => 'Note',
    ),
);

EOF;

        $custom = <<<EOF
\$app_list_strings = array(
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'To Do',
        'Email' => 'Email',
        'Note' => 'Note',
        'SMS' => 'Text Message',
    ),
);

EOF;

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $core);
        SugarTestLanguageFileUtilities::write($this->customPath, $GLOBALS['current_language'], $custom);

        SugarTestLanguageFileUtilities::write($this->corePath, $secondLanguage, $core);
        SugarTestLanguageFileUtilities::write($this->customPath, $secondLanguage, $custom);

        $this->script->run();

        $this->assertArrayHasKey($GLOBALS['current_language'], $this->upgrader->state['dropdowns_to_merge']);
        $this->assertArrayHasKey($secondLanguage, $this->upgrader->state['dropdowns_to_merge']);
    }

    /**
     * @covers SugarUpgradeLoadDropdowns::run
     */
    public function testRun_CustomHasDropdownsNotFoundInOld()
    {
        $core = <<<EOF
\$app_list_strings = array(
    'sales_stage_default_key' => 'Prospecting',
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'Task',
        'Email' => 'Email',
        'Note' => 'Note',
    ),
    'meeting_status_dom' => array(
        'Planned' => 'Planned',
        'Held' => 'Held',
        'Not Held' => 'Not Held',
    ),
);

EOF;

        $custom = <<<EOF
\$app_list_strings = array(
    'foo_dom' => array(
        'Foo' => 'Foo',
        'Biz' => 'Biz',
        'Baz' => 'Baz',
    ),
);

EOF;

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $core);
        SugarTestLanguageFileUtilities::write($this->customPath, $GLOBALS['current_language'], $custom);

        $this->script->run();

        $this->assertEmpty($this->upgrader->state['dropdowns_to_merge'][$GLOBALS['current_language']]['old']);
        $this->assertArrayHasKey(
            'foo_dom',
            $this->upgrader->state['dropdowns_to_merge'][$GLOBALS['current_language']]['custom']
        );
    }

    /**
     * Note that restricted dropdowns should not be customizable. This test case simply proves that the upgrader won't
     * attempt to do anything with a dropdown that is restricted.
     *
     * @covers SugarUpgradeLoadDropdowns::run
     */
    public function testRun_RestrictedDropdownsAreIgnored()
    {
        $core = <<<EOF
\$app_list_strings = array(
    'eapm_list' => array(
        'Sugar' => 'Sugar',
        'WebEx' => 'WebEx',
        'GoToMeeting' => 'GoToMeeting',
        'IBMSmartCloud' => 'IBM SmartCloud',
        'Google' => 'Google',
        'Box' => 'Box.net',
        'Facebook' => 'Facebook',
        'Twitter' => 'Twitter',
    ),
);

EOF;

        $custom = <<<EOF
\$app_list_strings = array(
    'eapm_list' => array(
        'Foo' => 'Bar',
    ),
);

EOF;

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $core);
        SugarTestLanguageFileUtilities::write($this->customPath, $GLOBALS['current_language'], $custom);

        $this->script->run();

        $this->assertEmpty($this->upgrader->state['dropdowns_to_merge']);
    }
}
