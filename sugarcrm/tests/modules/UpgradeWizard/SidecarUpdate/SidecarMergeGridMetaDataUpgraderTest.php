<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMergeGridMetaDataUpgrader.php';
require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';

class SidecarMergeGridMetaDataUpgraderTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SidecarSelectionListMetaDataUpgrader
     */
    protected $selectionListUpgrader;

    protected $module = 'MyFakeModule';
    protected $client = 'base';

    protected $testFiles = array(
        'modules/MyFakeModule/metadata/editviewdefs.php' => 'originalEditViewDefs',
        'modules/MyFakeModule/metadata/detailviewdefs.php' => 'originalDetailViewDefs',
        'modules/MyFakeModule/clients/base/views/record/record.php' => 'recordViewDefs',
        'custom/modules/MyFakeModule/metadata/detailviewdefs.php' => 'customDetailViewDefs',
        'custom/modules/MyFakeModule/metadata/editviewdefs.php' => 'customEditViewDefs',
    );
    //Original edit defs including a field unique to this view
    protected $originalEditViewDefs = <<<'EOQ'
<?php
$viewdefs['MyFakeModule']['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
    ),

    'panels' => array(
        'lbl_account_information' =>  array(
            array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'displayParams' =>
                    array(
                        'required' => true,
                    ),
                ),
                array(
                    'name' => 'phone_office',
                    'label' => 'LBL_PHONE_OFFICE',
                ),
            ),
            array(
                array(
                    'name' => 'website',
                    'type' => 'link',
                    'label' => 'LBL_WEBSITE',
                ),

                array(
                    'name' => 'edit_only',
                    'label' => 'LBL_FAX',
                ),
            ),
        ),
    ),
);
EOQ;
    //Original detail defs including a field unique to this view
    protected $originalDetailViewDefs = <<<'EOQ'
<?php
$viewdefs['MyFakeModule']['DetailView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'useTabs' => true,
        'tabDefs' => array(
            'LBL_ACCOUNT_INFORMATION' => array(
                'newTab' => true,
                'panelDefault' => 'expanded',
            ), 'LBL_PANEL_ADVANCED' =>
            array(
                'newTab' => true,
                'panelDefault' => 'expanded',
            ), 'LBL_PANEL_ASSIGNMENT' =>
            array(
                'newTab' => false,
                'panelDefault' => 'expanded',
            ),
        ),
    ),
    'panels' => array(
        'lbl_account_information' =>  array(
            array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'displayParams' =>
                    array(
                        'required' => true,
                    ),
                ),
                array(
                    'name' => 'phone_office',
                    'label' => 'LBL_PHONE_OFFICE',
                ),
            ),
            array(
                array(
                    'name' => 'website',
                    'type' => 'link',
                    'label' => 'LBL_WEBSITE',
                ),

                array(
                    'name' => 'phone_fax',
                    'label' => 'LBL_FAX',
                ),
            ),
        ),
    ),
);
EOQ;
    //Custom detail view defs that adds a new field and remove an existing one
    protected $customDetailViewDefs = <<<'EOQ'
<?php
$viewdefs['MyFakeModule']['DetailView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
    ),

    'panels' => array(
        'lbl_account_information' =>  array(
            array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'displayParams' =>
                    array(
                        'required' => true,
                    ),
                ),
                //Throw a blank in instead of phone_office. Since edit still has it is should remain on the new record view
                ''
            ),
            array(
                array(
                    //Replace website with a custom field
                    'name' => 'my_custom_field',
                    'type' => 'link',
                    'label' => 'SOMETHING',
                ),

                array(
                    'name' => 'detail_only',
                    'label' => 'LBL_FAX',
                ),
            ),
        ),
    ),
);
EOQ;
    //Custom edit view defs that adds a new field and remove an existing one
    protected $customEditViewDefs = <<<'EOQ'
<?php
    $viewdefs['MyFakeModule']['EditView'] = array(
        'templateMeta' => array(
            'maxColumns' => '2',
        ),

        'panels' => array(
            'lbl_account_information' =>  array(
                array(
                    array(
                        'name' => 'name',
                        'label' => 'LBL_NAME',
                        'displayParams' =>
                        array(
                            'required' => true,
                        ),
                    ),
                    array(
                        'name' => 'phone_office',
                        'label' => 'LBL_PHONE_OFFICE',
                    ),
                ),
                array(
                    array(
                        'name' => 'my_custom_field',
                        'type' => 'link',
                        'label' => 'LBL_WEBSITE',
                    ),

                    array(
                        'name' => 'edit_only',
                        'label' => 'LBL_FAX',
                    ),
                ),
            ),
        ),
    );
EOQ;
    //OOb record defs
    protected $recordViewDefs = <<<'EOQ'
<?php
$viewdefs['MyFakeModule']['base']['view']['record'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_HEADER',
            'header' => true,
            'fields' => array('name')
        ),
        array(
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'website',
                'phone_office',
                'new_record_field'
            ),
        ),
    ),
);
EOQ;

    public function setUp()
    {
        foreach($this->testFiles as $path => $contents) {
            if (!empty($this->$contents)){
                sugar_mkdir(dirname($path), null, true);
                file_put_contents($path, $this->$contents);
            }
        }
        $GLOBALS ['beanList'] [$this->module] = "Account";
        $GLOBALS['moduleList'][] = $this->module;
        $this->upgrader = new SidecarMetaDataUpgrader();

    }

    public function tearDown()
    {
        //Nuke all traces of the test module
        rmdir_recursive("modules/MyFakeModule");
        rmdir_recursive("custom/modules/MyFakeModule");
        SugarTestHelper::tearDown();
    }

    /**
     * Default selection-list.php should be a copy of list view.
     */
    public function testAddNewFieldsToRecord()
    {
        $mergeGrid = new MockSidecarMergeGridMetaDataUpgrader($this->upgrader, array(
            "client" => "base",
            "type" => "custom",
            "viewtype" => MB_RECORDVIEW,
            "module" => "MyFakeModule",
            "fullpath" => "custom/modules/MyFakeModule/metadata/testdetailviewdefs.php",
            "defsfile" => "modules/MyFakeModule/clients/base/views/record/record.php"
        ));
        $mergeGrid->upgrade();
        $finalRecordFields = $mergeGrid->getFieldsOnFinalLayout();
        //BR-1417 Assert that fields that are new to record view end up in the final product
        $this->assertArrayHasKey("new_record_field", $finalRecordFields);
        //The website fields was removed from both layouts so should not end up on the record view
        $this->assertArrayNotHasKey("website", $finalRecordFields);
        //phone_office was only removed from detail, so edit should have included it into the record
        $this->assertArrayHasKey("phone_office", $finalRecordFields);
        //Verify that fields only on the edit view are migrated
        $this->assertArrayHasKey("edit_only", $finalRecordFields);
        //Verify that fields only on the detail view are migrated
        $this->assertArrayHasKey("detail_only", $finalRecordFields);
    }
}

class MockSidecarMergeGridMetaDataUpgrader extends SidecarMergeGridMetaDataUpgrader
{
    //Turn handle save into a no-op to prevent extra files from being generated.
    public function handleSave() {

    }

    //Don't log anything to the file system, this is only a test.
    protected function logUpgradeStatus($message) {
        //echo "LOG::$message\n";
    }

    public function getFieldsOnFinalLayout() {
        $module = $this->module;
        $parser = ParserFactory::getParser($this->viewtype, $module, null, null, $this->client);
        $viewname = MetaDataFiles::getName($this->viewtype);
        $client = $this->client == 'wireless' ? 'mobile' : $this->client;
        if(empty($this->sidecarViewdefs[$module][$client]['view'][$viewname]['panels'])) {
            return array();
        }
        return $parser->getFieldsFromPanels($this->sidecarViewdefs[$module][$client]['view'][$viewname]['panels'], $parser->_fielddefs);
    }


}
