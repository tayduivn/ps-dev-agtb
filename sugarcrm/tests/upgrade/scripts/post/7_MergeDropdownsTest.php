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
require_once 'upgrade/scripts/post/7_MergeDropdowns.php';

/**
 * @covers SugarUpgradeMergeDropdowns
 */
class SugarUpgradeMergeDropdownsTest extends UpgradeTestCase
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

        $this->script = $this->upgrader->getScript('post', '7_MergeDropdowns');
    }

    public function tearDown()
    {
        SugarTestLanguageFileUtilities::clearCache();

        parent::tearDown();
    }

    /**
     * @covers SugarUpgradeMergeDropdowns::run
     */
    public function testRun_NothingToMerge_TheCustomFileIsNotWrittenToDisk()
    {
        $this->upgrader->state['dropdowns_to_merge'] = array();

        $this->script->run();

        $this->assertFileNotExists("{$this->customPath}{$GLOBALS['current_language']}.lang.php");
    }

    /**
     * @covers SugarUpgradeMergeDropdowns::run
     */
    public function testRun_SavesAMergedDropdown()
    {
        $old = array(
            'activity_dom' => array(
                'Call' => 'Call',
                'Meeting' => 'Meeting',
                'Task' => 'Task',
                'Email' => 'Email',
                'Note' => 'Note',
            ),
        );

        $custom = array(
            'activity_dom' => array(
                'Call' => 'Call',
                'Meeting' => 'Meeting',
                'Task' => 'To Do',
                'Email' => 'Email',
                'Note' => 'Note',
            ),
        );

        $new = <<<EOF
\$app_list_strings = array(
    'activity_dom' => array(
        'Insert_At_Beginning' => 'Insert At Beginning',
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'Task',
        'Email' => 'Email',
        'Note' => 'Note',
    ),
);

EOF;

        $this->upgrader->state['dropdowns_to_merge'] = array(
            $GLOBALS['current_language'] => array(
                'old' => $old,
                'custom' => $custom,
            ),
        );

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $new);

        $this->script->run();

        $actual = return_app_list_strings_language($GLOBALS['current_language']);
        $this->assertArrayHasKey('Insert_At_Beginning', $actual['activity_dom']);
        $this->assertEquals('To Do', $actual['activity_dom']['Task']);
    }

    /**
     * @covers SugarUpgradeMergeDropdowns::run
     */
    public function testRun_SavesDropdownsInMultipleLanguages()
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

        $old = array(
            'activity_dom' => array(
                'Call' => 'Call',
                'Meeting' => 'Meeting',
                'Task' => 'Task',
                'Email' => 'Email',
                'Note' => 'Note',
            ),
        );

        $custom = array(
            'activity_dom' => array(
                'Call' => 'Call',
                'Meeting' => 'Meeting',
                'Task' => 'To Do',
                'Email' => 'Email',
                'Note' => 'Note',
            ),
        );

        $new = <<<EOF
\$app_list_strings = array(
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'Task',
        'Email' => 'Email',
        'Note' => 'Note',
    ),
);

EOF;

        $this->upgrader->state['dropdowns_to_merge'] = array();
        $this->upgrader->state['dropdowns_to_merge'][$GLOBALS['current_language']] = array(
            'old' => $old,
            'custom' => $custom,
        );
        $this->upgrader->state['dropdowns_to_merge'][$secondLanguage] = array(
            'old' => $old,
            'custom' => $custom,
        );

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $new);
        SugarTestLanguageFileUtilities::write($this->corePath, $secondLanguage, $new);

        $this->script->run();

        $actual = return_app_list_strings_language($GLOBALS['current_language']);
        $this->assertEquals('To Do', $actual['activity_dom']['Task']);

        $actual = return_app_list_strings_language($secondLanguage);
        $this->assertEquals('To Do', $actual['activity_dom']['Task']);
    }

    /**
     * @covers SugarUpgradeMergeDropdowns::run
     */
    public function testRun_RetainsCustomCreatedDropdowns()
    {
        $old = array(
            'activity_dom' => array(
                'Call' => 'Call',
                'Meeting' => 'Meeting',
                'Task' => 'Task',
                'Email' => 'Email',
                'Note' => 'Note',
            ),
        );

        $custom = array(
            'foo_dom' => array(
                'foo' => 'Foo',
                'bar' => 'Bar',
            ),
        );

        $new = <<<EOF
\$app_list_strings = array(
    'activity_dom' => array(
        'Call' => 'Call',
        'Meeting' => 'Meeting',
        'Task' => 'Task',
        'Email' => 'Email',
        'Note' => 'Note',
    ),
);

EOF;

        $this->upgrader->state['dropdowns_to_merge'] = array(
            $GLOBALS['current_language'] => array(
                'old' => $old,
                'custom' => $custom,
            ),
        );

        SugarTestLanguageFileUtilities::write($this->corePath, $GLOBALS['current_language'], $new);

        $custom = <<<EOF
\$app_list_strings = array(
    'foo_dom' => array(
        'foo' => 'Foo',
        'bar' => 'Bar',
    ),
);

EOF;

        SugarTestLanguageFileUtilities::write($this->customPath, $GLOBALS['current_language'], $custom);

        $this->script->run();

        $actual = return_app_list_strings_language($GLOBALS['current_language']);
        $this->assertArrayHasKey('foo_dom', $actual);
    }

    /**
     * Test for check settings of use_push parameter
     *
     * @param array $old Old $app_list_strings values
     * @param array $new New $app_list_strings values
     * @param array $custom Custom $app_list_strings values
     * @param array $dropdownsToPush Array of dropdonws that need to be used with use_push parameter
     * @param array $use_push Expected result of use_push parameter
     *
     * @dataProvider usePushSettingsProvider
     */
    public function testRun_UsePushSettings($old, $new, $custom, $dropdownsToPush, $use_push)
    {
        $params = array();

        $parserMock = $this->getMock('ParserDropDown', array('saveDropDown'));
        $parserMock->expects($this->once())
                   ->method('saveDropDown')
                   ->will(
                       $this->returnCallback(
                           function ($data) use (&$params) {
                               $params = $data;
                               return $data;
                           }
                       )
                   );

        $helperMock = $this->getMock('UpgradeDropdownsHelper', array('getDropdowns', 'getDropdownsToPush'));
        $helperMock->expects($this->once())->method('getDropdowns')->willReturn($new);
        $helperMock->expects($this->once())->method('getDropdownsToPush')->willReturn($dropdownsToPush);

        $this->upgrader->state['dropdowns_to_merge'] = array(
            $GLOBALS['current_language'] => array(
                'old' => $old,
                'custom' => $custom,
            ),
        );

        $mockObject = $this->getMock(
            'SugarUpgradeMergeDropdowns',
            array('getDropdownParser', 'getDropdownHelper'),
            array($this->upgrader)
        );

        $mockObject->expects($this->once())->method('getDropdownParser')->willReturn($parserMock);
        $mockObject->expects($this->once())->method('getDropdownHelper')->willReturn($helperMock);

        $mockObject->run();

        $this->assertEquals($params['use_push'], $use_push);

    }

    public function usePushSettingsProvider()
    {
        return array(
            array(
                'old' => array(
                    'moduleList' => array(
                        'ACLRoles' => 'Roles',
                        'Bugs' => 'Bugs',
                        'iFrames' => 'My Sites',
                        'test' => 'test',
                    ),
                ),
                'new' => array(
                    'moduleList' => array(
                        'ACLRoles' => 'Roles',
                        'Bugs' => 'Bug Tracker',
                        'WebLogicHooks' => 'Web Logic Hooks',
                        'iFrames' => 'My Sites',
                    ),
                ),
                'custom' => array(
                    'moduleList' => array('Bugs' => 'Help Desks'),
                ),
                'dropdowns' => array('moduleList'),
                true,
            ),
            array(
                'old' => array(
                    'moduleList' => array(
                        'ACLRoles' => 'Roles',
                        'Bugs' => 'Bugs',
                        'iFrames' => 'My Sites',
                        'test' => 'test',
                    ),
                ),
                'new' => array(
                    'moduleList' => array(
                        'ACLRoles' => 'Roles',
                        'Bugs' => 'Bug Tracker',
                        'WebLogicHooks' => 'Web Logic Hooks',
                        'iFrames' => 'My Sites',
                    ),
                ),
                'custom' => array(
                    'moduleList' => array('Bugs' => 'Help Desks'),
                ),
                'dropdowns' => array(),
                false,
            ),
        );
    }
}
