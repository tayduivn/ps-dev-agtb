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

require_once "tests/upgrade/UpgradeTestCase.php";
require_once 'upgrade/scripts/post/7_FixCallsMeetingsReminderSelection.php';

/**
 * Class tests the fix of separate selection of reminder time for Emails and Popups in Calls & Meetings.
 *
 * @covers SugarUpgradeFixCallsMeetingsReminderSelection
 */
class FixCallsMeetingsReminderSelectionTest extends UpgradeTestCase
{
    /** @var array */
    protected $viewsList = array('record', 'list');

    /** @var array */
    protected $modules = array('CallsCRYS1309');

    /** @var SugarUpgradeFixCallsMeetingsReminderSelection */
    protected $upgradeFixCallsMeetings = null;

    /** @var SugarUpgradeFixCallsMeetingsReminderSelection|\PHPUnit_Framework_MockObject_MockObject */
    protected $upgradeMock = null;

    /** @var array */
    protected $popupDefs = array(
        'listviewdefs' => array(
            array(
                'name' => 'reminder_time',
                'label' => 'LBL_POPUP_REMINDER_TIME',
                'type' => 'event-status',
                'enum_width' => 'auto',
                'dropdown_width' => 'auto',
                'dropdown_class' => 'select2-menu-only',
                'container_class' => 'select2-menu-only',
            ),
            array(
                'name' => 'email_reminder_time',
                'type' => 'event-status',
                'enum_width' => 'auto',
                'dropdown_width' => 'auto',
                'dropdown_class' => 'select2-menu-only',
                'container_class' => 'select2-menu-only',
            ),
        )
    );

    /** @var array */
    protected $customRecordViewDefs = array(
        'CallsCRYS1309' => array(
            'base' => array(
                'view' => array(
                    'record' => array(
                        'panels' => array(
                            array(
                                'name' => 'panel_header',
                                'header' => true,
                                'fields' => array(
                                    array(
                                        'name' => 'status',
                                        'type' => 'event-status',
                                        'enum_width' => 'auto',
                                        'dropdown_width' => 'auto',
                                        'dropdown_class' => 'select2-menu-only',
                                        'container_class' => 'select2-menu-only',
                                    ),
                                    array(
                                        'name' => 'reminders',
                                        'type' => 'event-status',
                                        'enum_width' => 'auto',
                                        'dropdown_width' => 'auto',
                                        'dropdown_class' => 'select2-menu-only',
                                        'container_class' => 'select2-menu-only',
                                    ),
                                    array(
                                        'name' => 'reminder_time',
                                        'label' => 'LBL_POPUP_REMINDER_TIME',
                                        'type' => 'event-status',
                                        'enum_width' => 'auto',
                                        'dropdown_width' => 'auto',
                                        'dropdown_class' => 'select2-menu-only',
                                        'container_class' => 'select2-menu-only',
                                    ),
                                    array(
                                        'name' => 'email_reminder_time',
                                        'type' => 'event-status',
                                        'enum_width' => 'auto',
                                        'dropdown_width' => 'auto',
                                        'dropdown_class' => 'select2-menu-only',
                                        'container_class' => 'select2-menu-only',
                                    ),
                                ),
                            ),
                            array(
                                'name' => 'panel_body',
                                'label' => 'LBL_RECORD_BODY',
                                'columns' => 2,
                                'labelsOnTop' => true,
                                'placeholders' => true,
                                'fields' => array(
                                    array(
                                        'name' => 'repeat_type',
                                        'span' => 3,
                                        'related_fields' => array(
                                            'repeat_parent_id',
                                        ),
                                    ),
                                    'direction',
                                    array(
                                        'name' => 'description',
                                        'span' => 12,
                                        'rows' => 3,
                                    ),
                                    'parent_name',
                                    array(
                                        'name' => 'reminders',
                                        'type' => 'fieldset',
                                        'inline' => true,
                                        'equal_spacing' => true,
                                        'show_child_labels' => true,
                                        'fields' => array(
                                            'reminder_time',
                                            'email_reminder_time',
                                        ),
                                    ),
                                    array(
                                        'name' => 'email_reminder_time',
                                        'type' => 'fieldset',
                                        'inline' => true,
                                        'equal_spacing' => true,
                                        'show_child_labels' => true,
                                        'fields' => array(
                                            'reminder_time',
                                            'email_reminder_time',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );

    /** @var array */
    protected $customListViewDefs = array(
        'CallsCRYS1309' => array(
            'base' => array(
                'view' => array(
                    'list' => array(
                        'panels' => array(
                            array(
                                'name' => 'panel_header',
                                'header' => true,
                                'fields' => array(
                                    array(
                                        'name' => 'status',
                                        'type' => 'event-status',
                                        'enum_width' => 'auto',
                                        'dropdown_width' => 'auto',
                                        'dropdown_class' => 'select2-menu-only',
                                        'container_class' => 'select2-menu-only',
                                    ),
                                    array(
                                        'name' => 'reminders',
                                        'type' => 'event-status',
                                        'enum_width' => 'auto',
                                        'dropdown_width' => 'auto',
                                        'dropdown_class' => 'select2-menu-only',
                                        'container_class' => 'select2-menu-only',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );

    /** @var array */
    protected $canonicalListViewDefs = array(
        'CallsCRYS1309' => array(
            'base' => array(
                'view' => array(
                    'list' => array(
                        'panels' => array(
                            array(
                                'name' => 'panel_header',
                                'header' => true,
                                'fields' => array(
                                    array(
                                        'name' => 'status',
                                        'type' => 'event-status',
                                        'enum_width' => 'auto',
                                        'dropdown_width' => 'auto',
                                        'dropdown_class' => 'select2-menu-only',
                                        'container_class' => 'select2-menu-only',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );

    /** @var array */
    protected $canonicalRecordViewDefs = array(
        'CallsCRYS1309' => array(
            'base' => array(
                'view' => array(
                    'record' => array(
                        'panels' => array(
                            array(
                                'name' => 'panel_header',
                                'header' => true,
                                'fields' => array(
                                    array(
                                        'name' => 'status',
                                        'type' => 'event-status',
                                        'enum_width' => 'auto',
                                        'dropdown_width' => 'auto',
                                        'dropdown_class' => 'select2-menu-only',
                                        'container_class' => 'select2-menu-only',
                                    ),
                                    array(
                                        'name' => 'reminders',
                                        'type' => 'event-status',
                                        'enum_width' => '800',
                                        'dropdown_width' => 'auto',
                                        'dropdown_class' => 'select-menu-only',
                                        'container_class' => 'select-menu-only',
                                    ),
                                    array(
                                        'name' => 'reminder_time',
                                        'label' => 'LBL_POPUP_REMINDER_TIME',
                                        'type' => 'event-status',
                                        'enum_width' => 'auto',
                                        'dropdown_width' => 'auto',
                                        'dropdown_class' => 'select2-menu-only',
                                        'container_class' => 'select2-menu-only',
                                    ),
                                    array(
                                        'name' => 'email_reminder_time',
                                        'type' => 'event-status',
                                        'enum_width' => 'auto',
                                        'dropdown_width' => 'auto',
                                        'dropdown_class' => 'select2-menu-only',
                                        'container_class' => 'select2-menu-only',
                                    ),
                                ),
                            ),
                            array(
                                'name' => 'panel_body',
                                'label' => 'LBL_RECORD_BODY',
                                'columns' => 2,
                                'labelsOnTop' => true,
                                'placeholders' => true,
                                'fields' => array(
                                    array(
                                        'name' => 'repeat_type',
                                        'span' => 3,
                                        'related_fields' => array(
                                            'repeat_parent_id',
                                        ),
                                    ),
                                    'direction',
                                    array(
                                        'name' => 'description',
                                        'span' => 12,
                                        'rows' => 3,
                                    ),
                                    'parent_name',
                                    array(
                                        'name' => 'email_reminder_time',
                                        'type' => 'fieldset',
                                        'inline' => true,
                                        'equal_spacing' => true,
                                        'show_child_labels' => true,
                                        'fields' => array(
                                            'reminder_time',
                                            'email_reminder_time',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
        $this->upgradeMock = $this->getMock(
            'SugarUpgradeFixCallsMeetingsReminderSelection',
            array('fixSidecarView', 'fixPopupdefs'),
            array($this->upgrader)
        );

        $this->upgradeFixCallsMeetings = new SugarUpgradeFixCallsMeetingsReminderSelection(
            $this->upgrader,
            $this->modules,
            $this->viewsList
        );

        SugarTestHelper::ensureDir(array(
            "modules/CallsCRYS1309/clients/base/views/record",
            "modules/CallsCRYS1309/clients/base/views/list",
            "custom/modules/CallsCRYS1309/clients/base/views/record",
            "custom/modules/CallsCRYS1309/clients/base/views/list",
            "custom/modules/CallsCRYS1309/metadata",
        ));

        SugarTestHelper::saveFile(array(
            "modules/CallsCRYS1309/clients/base/views/record/record.php",
            "modules/CallsCRYS1309/clients/base/views/list/list.php",
            "custom/modules/CallsCRYS1309/clients/base/views/record/record.php",
            "custom/modules/CallsCRYS1309/clients/base/views/list/list.php",
            "custom/modules/CallsCRYS1309/metadata/popupdefs.php",
        ));

        write_array_to_file(
            'viewdefs',
            $this->customRecordViewDefs,
            "custom/modules/CallsCRYS1309/clients/base/views/record/record.php"
        );
        write_array_to_file(
            'viewdefs',
            $this->canonicalRecordViewDefs,
            "modules/CallsCRYS1309/clients/base/views/record/record.php"
        );
        write_array_to_file(
            'viewdefs',
            $this->customListViewDefs,
            "custom/modules/CallsCRYS1309/clients/base/views/list/list.php"
        );
        write_array_to_file(
            'viewdefs',
            $this->canonicalListViewDefs,
            "modules/CallsCRYS1309/clients/base/views/list/list.php"
        );
        write_array_to_file(
            'popupMeta',
            $this->popupDefs,
            "custom/modules/CallsCRYS1309/metadata/popupdefs.php"
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Expected data provider for testUpgrade.
     *
     * @see FixCallsMeetingsReminderSelectionTest::testUpgrade
     * @return array
     */
    public static function upgradeProvider()
    {
        return array(
            'updateVersionBelow78' => array(
                'version' => '7.7',
                'expectedRecordViewDefs' => array(
                    'CallsCRYS1309' => array(
                        'base' => array(
                            'view' => array(
                                'record' => array(
                                    'panels' => array(
                                        array(
                                            'name' => 'panel_header',
                                            'header' => true,
                                            'fields' => array(
                                                array(
                                                    'name' => 'status',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                                array(
                                                    'name' => 'reminders',
                                                    'type' => 'event-status',
                                                    'enum_width' => '800',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select-menu-only',
                                                    'container_class' => 'select-menu-only',
                                                ),
                                                array(
                                                    'name' => 'reminder_time',
                                                    'label' => 'LBL_REMINDER_TIME',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                            ),
                                        ),
                                        array(
                                            'name' => 'panel_body',
                                            'label' => 'LBL_RECORD_BODY',
                                            'columns' => 2,
                                            'labelsOnTop' => true,
                                            'placeholders' => true,
                                            'fields' => array(
                                                array(
                                                    'name' => 'repeat_type',
                                                    'span' => 3,
                                                    'related_fields' => array(
                                                        'repeat_parent_id',
                                                    ),
                                                ),
                                                'direction',
                                                array(
                                                    'name' => 'description',
                                                    'span' => 12,
                                                    'rows' => 3,
                                                ),
                                                'parent_name',
                                                array(
                                                    'name' => 'reminders',
                                                    'type' => 'event-status',
                                                    'enum_width' => '800',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select-menu-only',
                                                    'container_class' => 'select-menu-only',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'expectedListViewDefs' => array(
                    'CallsCRYS1309' => array(
                        'base' => array(
                            'view' => array(
                                'list' => array(
                                    'panels' => array(
                                        array(
                                            'name' => 'panel_header',
                                            'header' => true,
                                            'fields' => array(
                                                array(
                                                    'name' => 'status',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'expectedPopupDefs' => array(
                    'listviewdefs' => array(
                        array(
                            'name' => 'reminder_time',
                            'label' => 'LBL_POPUP_REMINDER_TIME',
                            'type' => 'event-status',
                            'enum_width' => 'auto',
                            'dropdown_width' => 'auto',
                            'dropdown_class' => 'select2-menu-only',
                            'container_class' => 'select2-menu-only',
                        ),
                    ),
                ),
            ),
            'doNotUpdateVersion78' => array(
                'version' => '7.8',
                'expectedRecordViewDefs' => array(
                    'CallsCRYS1309' => array(
                        'base' => array(
                            'view' => array(
                                'record' => array(
                                    'panels' => array(
                                        array(
                                            'name' => 'panel_header',
                                            'header' => true,
                                            'fields' => array(
                                                array(
                                                    'name' => 'status',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                                array(
                                                    'name' => 'reminders',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                                array(
                                                    'name' => 'reminder_time',
                                                    'label' => 'LBL_POPUP_REMINDER_TIME',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                                array(
                                                    'name' => 'email_reminder_time',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                            ),
                                        ),
                                        array(
                                            'name' => 'panel_body',
                                            'label' => 'LBL_RECORD_BODY',
                                            'columns' => 2,
                                            'labelsOnTop' => true,
                                            'placeholders' => true,
                                            'fields' => array(
                                                array(
                                                    'name' => 'repeat_type',
                                                    'span' => 3,
                                                    'related_fields' => array(
                                                        'repeat_parent_id',
                                                    ),
                                                ),
                                                'direction',
                                                array(
                                                    'name' => 'description',
                                                    'span' => 12,
                                                    'rows' => 3,
                                                ),
                                                'parent_name',
                                                array(
                                                    'name' => 'reminders',
                                                    'type' => 'fieldset',
                                                    'inline' => true,
                                                    'equal_spacing' => true,
                                                    'show_child_labels' => true,
                                                    'fields' => array(
                                                        'reminder_time',
                                                        'email_reminder_time',
                                                    ),
                                                ),
                                                array(
                                                    'name' => 'email_reminder_time',
                                                    'type' => 'fieldset',
                                                    'inline' => true,
                                                    'equal_spacing' => true,
                                                    'show_child_labels' => true,
                                                    'fields' => array(
                                                        'reminder_time',
                                                        'email_reminder_time',
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'expectedListViewDefs' => array(
                    'CallsCRYS1309' => array(
                        'base' => array(
                            'view' => array(
                                'list' => array(
                                    'panels' => array(
                                        array(
                                            'name' => 'panel_header',
                                            'header' => true,
                                            'fields' => array(
                                                array(
                                                    'name' => 'status',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                                array(
                                                    'name' => 'reminders',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'expectedPopupDefs' => array(
                    'listviewdefs' => array(
                        array(
                            'name' => 'reminder_time',
                            'label' => 'LBL_POPUP_REMINDER_TIME',
                            'type' => 'event-status',
                            'enum_width' => 'auto',
                            'dropdown_width' => 'auto',
                            'dropdown_class' => 'select2-menu-only',
                            'container_class' => 'select2-menu-only',
                        ),
                        array(
                            'name' => 'email_reminder_time',
                            'type' => 'event-status',
                            'enum_width' => 'auto',
                            'dropdown_width' => 'auto',
                            'dropdown_class' => 'select2-menu-only',
                            'container_class' => 'select2-menu-only',
                        ),
                    ),
                ),
            ),
            'doNotUpdateVersionHigher78' => array(
                'version' => '7.9',
                'expectedRecordViewDefs' => array(
                    'CallsCRYS1309' => array(
                        'base' => array(
                            'view' => array(
                                'record' => array(
                                    'panels' => array(
                                        array(
                                            'name' => 'panel_header',
                                            'header' => true,
                                            'fields' => array(
                                                array(
                                                    'name' => 'status',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                                array(
                                                    'name' => 'reminders',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                                array(
                                                    'name' => 'reminder_time',
                                                    'label' => 'LBL_POPUP_REMINDER_TIME',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                                array(
                                                    'name' => 'email_reminder_time',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                            ),
                                        ),
                                        array(
                                            'name' => 'panel_body',
                                            'label' => 'LBL_RECORD_BODY',
                                            'columns' => 2,
                                            'labelsOnTop' => true,
                                            'placeholders' => true,
                                            'fields' => array(
                                                array(
                                                    'name' => 'repeat_type',
                                                    'span' => 3,
                                                    'related_fields' => array(
                                                        'repeat_parent_id',
                                                    ),
                                                ),
                                                'direction',
                                                array(
                                                    'name' => 'description',
                                                    'span' => 12,
                                                    'rows' => 3,
                                                ),
                                                'parent_name',
                                                array(
                                                    'name' => 'reminders',
                                                    'type' => 'fieldset',
                                                    'inline' => true,
                                                    'equal_spacing' => true,
                                                    'show_child_labels' => true,
                                                    'fields' => array(
                                                        'reminder_time',
                                                        'email_reminder_time',
                                                    ),
                                                ),
                                                array(
                                                    'name' => 'email_reminder_time',
                                                    'type' => 'fieldset',
                                                    'inline' => true,
                                                    'equal_spacing' => true,
                                                    'show_child_labels' => true,
                                                    'fields' => array(
                                                        'reminder_time',
                                                        'email_reminder_time',
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'expectedListViewDefs' => array(
                    'CallsCRYS1309' => array(
                        'base' => array(
                            'view' => array(
                                'list' => array(
                                    'panels' => array(
                                        array(
                                            'name' => 'panel_header',
                                            'header' => true,
                                            'fields' => array(
                                                array(
                                                    'name' => 'status',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                                array(
                                                    'name' => 'reminders',
                                                    'type' => 'event-status',
                                                    'enum_width' => 'auto',
                                                    'dropdown_width' => 'auto',
                                                    'dropdown_class' => 'select2-menu-only',
                                                    'container_class' => 'select2-menu-only',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'expectedPopupDefs' => array(
                    'listviewdefs' => array(
                        array(
                            'name' => 'reminder_time',
                            'label' => 'LBL_POPUP_REMINDER_TIME',
                            'type' => 'event-status',
                            'enum_width' => 'auto',
                            'dropdown_width' => 'auto',
                            'dropdown_class' => 'select2-menu-only',
                            'container_class' => 'select2-menu-only',
                        ),
                        array(
                            'name' => 'email_reminder_time',
                            'type' => 'event-status',
                            'enum_width' => 'auto',
                            'dropdown_width' => 'auto',
                            'dropdown_class' => 'select2-menu-only',
                            'container_class' => 'select2-menu-only',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Should update files if version below than 7.8.
     * In update process should remove email_reminder_time fields from custom view.
     * In update process should remove remainders field if it not exists in default view.
     * In update process should set custom remainders equal to default reminders field if it exists in default view.
     * In update process should remove email_reminder_time field from custom popupMeta array.
     *
     * @dataProvider upgradeProvider
     * @covers SugarUpgradeFixCallsMeetingsReminderSelection::run
     * @param string $version
     * @param array $expectedRecordViewDefs
     * @param array $expectedListViewDefs
     * @param array $expectedPopupDefs
     */
    public function testUpgrade($version, $expectedRecordViewDefs, $expectedListViewDefs, $expectedPopupDefs)
    {
        $viewdefs = array();
        $popupMeta = array();
        $this->upgrader->from_version = $version;
        $this->upgradeFixCallsMeetings->run();
        include "custom/modules/CallsCRYS1309/clients/base/views/record/record.php";
        include "custom/modules/CallsCRYS1309/metadata/popupdefs.php";

        $this->assertEquals($expectedRecordViewDefs, $viewdefs);
        $this->assertEquals($expectedPopupDefs, $popupMeta);

        include "custom/modules/CallsCRYS1309/clients/base/views/list/list.php";
        $this->assertEquals($expectedListViewDefs, $viewdefs);
    }
}
