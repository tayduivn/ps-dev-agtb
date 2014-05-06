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

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarLayoutdefsMetaDataUpgrader.php';
require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';

class SidecarLayoutdefsMetaDataUpgraderTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SidecarLayoutdefsMetaDataUpgraderMock
     */
    protected $upgrader;

    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var array
     */
    protected $subpanelData = array();

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));

        $file = array(
            'module' => $this->module,
        );
        $this->upgrader = new SidecarLayoutdefsMetaDataUpgraderMock(new SidecarMetaDataUpgrader(), $file);

        $this->subpanelData = array(
            'test_relationship' => array(
                'module' => 'custom_module',
                'subpanel_name' => 'default',
                'title_key' => 'LBL_TEST_TITLE_1',
                'get_subpanel_data' => 'unexisting_relationships',
            ),
            'test_function' => array(
                'module' => 'custom_module',
                'subpanel_name' => 'default',
                'title_key' => 'LBL_TEST_TITLE_2',
                'get_subpanel_data' => 'function:global_function',
            ),
        );
        $this->upgrader->loadSubpanelData($this->module, $this->subpanelData);
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Should ignore subpanels with 'function:' get_subpanel_data.
     */
    public function testConvertSubpanelDataFunction()
    {
        $this->upgrader->setLegacyViewdefs(array('test_function' => $this->subpanelData['test_function']));

        $this->upgrader->convertLegacyViewDefsToSidecar();

        $actualDefs = $this->upgrader->getSidecarViewDefs();

        $this->assertEmpty($actualDefs);
    }

    /**
     * Should ignore subpanels with 'relationship' get_subpanel_data, where relationship doesn't exist.
     */
    public function testConvertSubpanelDataUnexistingRelationship()
    {
        $this->upgrader->setLegacyViewdefs(array('test_relationship' => $this->subpanelData['test_relationship']));

        $this->upgrader->convertLegacyViewDefsToSidecar();

        $actualDefs = $this->upgrader->getSidecarViewDefs();
        $this->assertEmpty($actualDefs);
    }
}

class SidecarLayoutdefsMetaDataUpgraderMock extends SidecarLayoutdefsMetaDataUpgrader
{
    /**
     * Fill static subpanel defs.
     *
     * @param $module Module name.
     * @param array $data
     */
    public function loadSubpanelData($module, Array $data)
    {
        self::$supanelData[$module] = $data;
    }

    /**
     * Load legacy view defs manually.
     *
     * @param array $data $layout_defs[{module}]['subpanel_setup'].
     * @return void
     */
    public function setLegacyViewdefs(Array $data)
    {
        $this->legacyViewdefs = $data;
    }
}
