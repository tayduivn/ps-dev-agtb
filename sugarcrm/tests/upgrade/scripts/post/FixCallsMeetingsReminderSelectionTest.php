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
require_once 'upgrade/scripts/post/7_FixCallsMeetingsReminderSelection.php';

/**
 * Class tests the fix of separate selection of reminder time for Emails and Popups in Calls & Meetings.
 */
class FixCallsMeetingsReminderSelectionTest extends UpgradeTestCase
{
    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @covers SugarUpgradeFixCallsMeetingsReminderSelection::run
     */
    public function testAllNeededViewsAndDefsAreProcessed()
    {
        $mock = $this->getMock(
            'SugarUpgradeFixCallsMeetingsReminderSelection',
            array('fixSidecarView', 'fixPopupdefs', 'getCanonicalSidecarFieldDef', 'saveViewDefsToFile'),
            array($this->upgrader)
        );

        $mock->from_version = '7.7';

        $mock->expects($this->atLeastOnce())
            ->method('fixSidecarView')
            ->withConsecutive(
                array($this->equalTo('Calls'), $this->equalTo('record')),
                array($this->equalTo('Calls'), $this->equalTo('list')),
                array($this->equalTo('Calls'), $this->equalTo('selection-list')),
                array($this->equalTo('Meetings'), $this->equalTo('record')),
                array($this->equalTo('Meetings'), $this->equalTo('list')),
                array($this->equalTo('Meetings'), $this->equalTo('selection-list'))
            );

        $mock->expects($this->exactly(2))->method('fixPopupdefs');

        $mock->run();
    }

    /**
     * @covers SugarUpgradeFixCallsMeetingsReminderSelection::fixSidecarView
     *
     * @dataProvider sidecarDefsProvider
     *
     * @param mixed $given Original custom data.
     * @param mixed $expected Data after upgrade.
     * @param mixed $canonical canonical fieldDef from stock module if any is found.
     */
    public function testFixSidecarView($given, $expected, $canonical)
    {
        // Record view and Calls module are used as an example.
        $module = 'Calls';
        $view = 'record';
        $file = "custom/modules/$module/clients/base/views/$view/$view.php";
        SugarAutoLoader::ensureDir(dirname($file));
        SugarTestHelper::saveFile($file);

        $stub = $this->getMock(
            'SugarUpgradeFixCallsMeetingsReminderSelection',
            array('getCanonicalSidecarFieldDef'),
            array($this->upgrader)
        );
        $stub->method('getCanonicalSidecarFieldDef')->willReturn($canonical);

        $dataToWrite = array();
        $dataToWrite[$module]['base']['view'][$view]['panels']['foo-panel']['fields'] = $given;
        write_array_to_file('viewdefs', $dataToWrite, $file);

        $stub->fixSidecarView($module, $view);

        $this->assertFileExists($file);
        include $file;
        $this->assertEquals($expected, $viewdefs[$module]['base']['view'][$view]['panels']['foo-panel']['fields']);
    }

    /**
     * @covers SugarUpgradeFixCallsMeetingsReminderSelection::fixPopupdefs
     */
    public function testFixPopupdefs()
    {
        // Record view and Calls module are used as an example.
        $module = 'Calls';
        $file = "custom/modules/$module/metadata/popupdefs.php";
        SugarAutoLoader::ensureDir(dirname($file));
        SugarTestHelper::saveFile($file);

        $dataToWrite = array();
        $dataToWrite['listviewdefs']['EMAIL_REMINDER_TIME'] = array(
            'name' => 'email_reminder_time'
        );
        write_array_to_file('popupMeta', $dataToWrite, $file);

        $script = new SugarUpgradeFixCallsMeetingsReminderSelection($this->upgrader);
        $script->fixPopupdefs($module);

        $this->assertFileExists($file);
        include $file;
        $this->assertArrayNotHasKey('EMAIL_REMINDER_TIME', $popupMeta['listviewdefs']);
    }

    /**
     * Data provider for testVCardMenuItemCreation
     *
     * @return array
     */
    public function sidecarDefsProvider()
    {
        return array(
            array(
                'old' => array(
                    array(
                        'name' => 'reminders'
                    ),
                ),
                'new' => array(
                    array(
                        'name' => 'reminders'
                    ),
                ),
                'canonical' => array(
                    'name' => 'reminders'
                ),
            ),

            array(
                array(
                    array(
                        'name' => 'reminders'
                    ),
                ),
                array(),
                null,
            ),

            array(
                array(
                    array(
                        'name' => 'reminders'
                    ),
                ),
                array('reminder_time'),
                'reminder_time',
            ),

            array(
                array(
                    array(
                        'name' => 'email_reminder_time'
                    ),
                ),
                array(),
                null,
            ),

            array(
                array('email_reminder_time'),
                array(),
                null,
            ),

            array(
                array('some_other_field', 'email_reminder_time'),
                array('some_other_field'),
                null,
            ),

            array(
                array(
                    'some_other_field',
                    array(
                        'name' => 'reminders'
                    ),
                ),
                array('some_other_field', 'reminder_time'),
                'reminder_time',
            ),

            array(
                'old' => array(
                    array(
                        'name' => 'reminder_time',
                        'label' => 'LBL_POPUP_REMINDER_TIME'
                    ),
                ),
                'new' => array(
                    array(
                        'name' => 'reminder_time',
                        'label' => 'LBL_REMINDER_TIME'
                    ),
                ),
                null,
            ),
        );
    }
}
