<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarSubpanelMetaDataUpgrader.php';
require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';

class SidecarSubpanelUpgraderTest extends PHPUnit_Framework_TestCase
{
    protected $subpanelUpgrader;
    protected $oldDefs;
    protected $expectedDefs;
    protected $oldFileName = 'custom/modules/Accounts/metadata/subpanels/ForAwesometest.php';

    protected function setUp()
    {
        $this->upgrader = new SidecarMetaDataUpgrader();
    }

    protected function tearDown()
    {
        unset($this->subpanelUpgrader);
    }

    public function testViewDefsUpgrade()
    {
        $this->setUpViewDefs();
        if (!is_dir(dirname($this->oldFileName))) {
            sugar_mkdir(dirname($this->oldFileName), null, true);
        }
        write_array_to_file(
            "subpanel_layout",
            $this->oldDefs,
            $this->oldFileName
        );

        $fileArray = array(
            'fullpath' => $this->oldFileName,
        );

        $this->subpanelUpgrader = new SidecarSubpanelViewDefUpgraderMock($this->upgrader, $fileArray);

        $this->subpanelUpgrader->convertLegacyViewDefsToSidecar();

        $this->assertEquals($this->expectedDefs, $this->subpanelUpgrader->newSubpanel);
        unlink($this->oldFileName);
    }

    public function testLayoutDefsUpgrade()
    {
        $this->setUpLayoutDefs();

        $fileArray = array();
        $this->subpanelUpgrader = new SidecarSubpanelViewDefUpgraderMock($this->upgrader, $fileArray);
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
        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']['calls']['override_subpanel_name']",
            'ForCalls',
            "custom/Extension/modules/Accounts/Ext/Layoutdefs/overridecalls.php"
        );
        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']['calls']['override_subpanel_name']",
            'ForCalls',
            "custom/modules/Accounts/Ext/Layoutdefs/layoutdefs.ext.php"
        );

        $this->subpanelUpgrader->convertLegacySubpanelLayoutDefsToSidecar('Accounts');

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
            'layout' => 'subpanel',
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
                        ),
                    ),
                ),
            ),
        );
    }
}

class SidecarSubpanelViewDefUpgraderMock extends SidecarSubpanelMetaDataUpgrader
{
    public function save()
    {
        // do nothing
    }
}
