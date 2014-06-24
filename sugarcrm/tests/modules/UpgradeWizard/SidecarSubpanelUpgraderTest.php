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

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarSubpanelMetaDataUpgrader.php';
require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';
require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarLayoutdefsMetaDataUpgrader.php';

class SidecarSubpanelUpgraderTest extends PHPUnit_Framework_TestCase
{
    protected $oldDefs;
    protected $expectedDefs;

    /** @var SidecarMetaDataUpgrader */
    protected $upgrader;
    protected $filesToRemove = array();

    protected function setUp()
    {
        $this->upgrader = new SidecarMetaDataUpgrader();
        $this->filesToRemove = array();
    }

    protected function tearDown()
    {
        foreach ($this->filesToRemove as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testViewDefsUpgrade()
    {
        $this->setUpViewDefs();

        $oldFileName = 'custom/modules/Accounts/metadata/subpanels/ForAwesometest.php';
        if (!is_dir(dirname($oldFileName))) {
            sugar_mkdir(dirname($oldFileName), null, true);
        }
        write_array_to_file(
            "subpanel_layout",
            $this->oldDefs,
            $oldFileName
        );
        $this->filesToRemove[] = $oldFileName;

        $fileArray = array(
            'module' => 'Accounts',
            'client' => 'base',
            'fullpath' => $oldFileName,
        );

        $subpanelUpgrader = new SidecarSubpanelViewDefUpgraderMock($this->upgrader, $fileArray);
        $subpanelUpgrader->upgrade();

        $this->assertEquals($this->expectedDefs, $subpanelUpgrader->sidecarViewdefs);
    }

    public function testLayoutDefsUpgrade()
    {
        $this->setUpLayoutDefs();

        $fileArray = array(
            'module' => 'Accounts',
            'client' => 'base',
            'filename' => 'overridecalls.php',
            'fullpath' => 'custom/Extension/modules/Accounts/Ext/Layoutdefs/overridecalls.php',
        );
        $subpanelUpgrader = new SidecarLayoutdefsMetaDataUpgrader($this->upgrader, $fileArray);
        if (!is_dir("custom/modules/Accounts/metadata/")) {
            sugar_mkdir("custom/modules/Accounts/metadata/", null, true);
        }
        if (!is_dir("custom/Extension/modules/Accounts/Ext/Layoutdefs/")) {
            sugar_mkdir("custom/Extension/modules/Accounts/Ext/Layoutdefs/", null, true);
        }

        if (!is_dir("custom/modules/Accounts/Ext/Layoutdefs/")) {
            sugar_mkdir("custom/modules/Accounts/Ext/Layoutdefs/", null, true);
        }

        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']",
            $this->oldLayoutDefs,
            "custom/modules/Accounts/metadata/subpaneldefs.php"
        );
        $this->filesToRemove[] = 'custom/modules/Accounts/metadata/subpaneldefs.php';

        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']['calls']['override_subpanel_name']",
            'ForCalls',
            "custom/Extension/modules/Accounts/Ext/Layoutdefs/overridecalls.php"
        );
        $this->filesToRemove[] = 'custom/Extension/modules/Accounts/Ext/Layoutdefs/overridecalls.php';

        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']['calls']['override_subpanel_name']",
            'ForCalls',
            "custom/modules/Accounts/Ext/Layoutdefs/layoutdefs.ext.php"
        );
        $this->filesToRemove[] = 'custom/modules/Accounts/Ext/Layoutdefs/layoutdefs.ext.php';

        $subpanelUpgrader->upgrade();

        $this->assertFileExists(
            "custom/Extension/modules/Accounts/Ext/clients/base/layouts/subpanels/overridecalls.php"
        );

        include "custom/Extension/modules/Accounts/Ext/clients/base/layouts/subpanels/overridecalls.php";

        $this->assertEquals($this->expectedNewLayoutDefs, $viewdefs['Accounts']['base']['layout']['subpanels']['components'][0]);
    }

    public function setUpLayoutDefs()
    {
        $this->oldLayoutDefs = array(
            'calls' => array(
                'module' => 'Calls',
                'subpanel_name' => 'ForHistory',
                'get_subpanel_data' => 'calls',
            ),
        );

        $this->expectedNewLayoutDefs = array(
            'override_subpanel_list_view' => array(
                'view' => 'subpanel-for-calls',
                'link' => 'calls',
            ),
        );

    }

    public function setUpViewDefs()
    {
        // setup old defs
        $this->oldDefs = array(
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateButton'),
                array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'Accounts'),
            ),
            'where' => '',
            'list_fields' => array(
                'name' =>
                array(
                    'vname' => 'LBL_LIST_ACCOUNT_NAME',
                    'widget_class' => 'SubPanelDetailViewLink',
                    'width' => '45%',
                    'default' => true,
                ),
                'billing_address_city' =>
                array(
                    'vname' => 'LBL_LIST_CITY',
                    'width' => '20%',
                    'default' => true,
                ),
                'billing_address_country' =>
                array(
                    'type' => 'varchar',
                    'vname' => 'LBL_BILLING_ADDRESS_COUNTRY',
                    'width' => '7%',
                    'default' => true,
                ),
                'phone_office' =>
                array(
                    'vname' => 'LBL_LIST_PHONE',
                    'width' => '20%',
                    'default' => true,
                ),
                'edit_button' =>
                array(
                    'vname' => 'LBL_EDIT_BUTTON',
                    'widget_class' => 'SubPanelEditButton',
                    'width' => '4%',
                    'default' => true,
                ),
                'remove_button' =>
                array(
                    'vname' => 'LBL_REMOVE',
                    'widget_class' => 'SubPanelRemoveButtonAccount',
                    'width' => '4%',
                    'default' => true,
                ),
            )
        );

        $this->expectedDefs = array(
            'panels' =>
            array(
                array(
                    'name' => 'panel_header',
                    'label' => 'LBL_PANEL_1',
                    'fields' =>
                    array(
                        array(
                            'default' => true,
                            'label' => 'LBL_LIST_ACCOUNT_NAME',
                            'enabled' => true,
                            'name' => 'name',
                            'link' => true,
                            'type' => 'name'
                        ),
                        array(
                            'default' => true,
                            'label' => 'LBL_LIST_CITY',
                            'enabled' => true,
                            'name' => 'billing_address_city',
                        ),
                        array(
                            'type' => 'varchar',
                            'default' => true,
                            'label' => 'LBL_BILLING_ADDRESS_COUNTRY',
                            'enabled' => true,
                            'name' => 'billing_address_country',
                        ),
                        array(
                            'default' => true,
                            'label' => 'LBL_LIST_PHONE',
                            'enabled' => true,
                            'name' => 'phone_office',
                            'type' => 'phone'
                        ),
                    ),
                ),
            ),
            'type' => 'subpanel-list'
        );
    }
}

class SidecarSubpanelViewDefUpgraderMock extends SidecarSubpanelMetaDataUpgrader
{
    public $sidecarViewdefs  = 'bad default';

    public function handleSave()
    {
        // do nothing
    }
}
