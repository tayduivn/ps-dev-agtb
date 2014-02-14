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

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarSelectionListMetaDataUpgrader.php';
require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';

class SidecarSelectionListMetaDataUpgraderTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SidecarSelectionListMetaDataUpgrader
     */
    protected $selectionListUpgrader;

    protected $module = 'Accounts';
    protected $client = 'base';

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));

        $file = array(
            'client' => $this->client,
            'module' => $this->module,
            'type' => 'base',
            'basename' => 'popupdefs',
            'timestamp' => null,
            'fullpath' => "modules/{$this->module}/metadata/popupdefs.php",
            'package' => null,
            'deployed' => true,
            'sidecar' => false,
            'viewtype' => 'popuplist',
        );

        $this->selectionListUpgrader = new SidecarSelectionListMetaDataUpgrader(new SidecarMetaDataUpgrader(), $file);
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Default selection-list.php should be a copy of list view.
     */
    public function testConvertLegacyViewDefsToSidecar()
    {
        $this->selectionListUpgrader->convertLegacyViewDefsToSidecar();

        require "modules/{$this->module}/metadata/listviewdefs.php";
        $actualFieldNames = array_keys($listViewDefs[$this->module]);

        $sidecarListViewDefs = $this->selectionListUpgrader->getSidecarViewDefs();
        $expectedFieldNames = array_map(function ($val) {
                return $val['name'];
            },
            $sidecarListViewDefs[$this->module][$this->client]['view']['selection-list']['panels'][0]['fields']
        );

        $this->assertEquals($actualFieldNames, $expectedFieldNames, '', 0, 10, true, true);
    }
}
