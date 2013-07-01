<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


require_once 'data/SugarBean.php';
require_once 'modules/Contacts/Contact.php';
require_once 'include/SubPanel/SubPanel.php';
require_once 'include/SubPanel/SubPanel.php';
require_once 'include/SubPanel/SubPanelDefinitions.php';

/**
 * @ticket 41853
 * @ticket 40171
 */
class Bug40171Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $bean;

    public function setUp()
    {
        global $moduleList, $beanList, $beanFiles;
        require('include/modules.php');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->bean = new Contact();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        foreach ($this->filename_check as $filename) {
            @SugarAutoLoader::unlink($filename);
        }
        require_once('ModuleInstall/ModuleInstaller.php');
        $moduleInstaller = new ModuleInstaller();
        $moduleInstaller->silent = true; // make sure that the ModuleInstaller->log() function doesn't echo while rebuilding the layoutdefs
        $moduleInstaller->rebuild_layoutdefs();
    }

    public function testSubpanelOverride()
    {
        // Create Subpanel 1
        $subpanel_1 = array(
            'order' => 100,
            'module' => 'Cases',
            'subpanel_name' => 'default',
            'sort_order' => 'asc',
            'sort_by' => 'id',
            'title_key' => 'LBL_CONTACTS_CASES_1_FROM_CASES_TITLE',
            'get_subpanel_data' => 'contacts_cases_1',
            'top_buttons' =>
            array(
                0 => array(
                    'widget_class' => 'SubPanelTopButtonQuickCreate',
                ),
                1 => array(
                    'widget_class' => 'SubPanelTopSelectButton',
                    'mode' => 'MultiSelect',
                ),
            ),
        );
        $subpanel_list_fields_1['list_fields'] = array(
            'priority' =>
            array(
                'type' => 'enum',
                'vname' => 'LBL_PRIORITY',
                'sortable' => false,
                'width' => '10%',
                'default' => true,
            ),
        );
        $subpanel_def_1 = new aSubPanel("contacts_cases_1", $subpanel_1, $this->bean);
        $subpanel_1 = new SubPanel('Contacts', 'fab4', $subpanel_def_1->_instance_properties['subpanel_name'], $subpanel_def_1);
        $subpanel_1->saveSubPanelDefOverride($subpanel_def_1, 'list_fields', $subpanel_list_fields_1);

        $extname_1 = '_overridesubpanel-for-' . $subpanel_def_1->name;

        // Create SubPane 2
        $subpanel_2 = array(
            'order' => 100,
            'module' => 'Cases',
            'subpanel_name' => 'default',
            'sort_order' => 'asc',
            'sort_by' => 'id',
            'title_key' => 'LBL_CONTACTS_CASES_2_FROM_CASES_TITLE',
            'get_subpanel_data' => 'contacts_cases_2',
            'top_buttons' =>
            array(
                0 => array(
                    'widget_class' => 'SubPanelTopButtonQuickCreate',
                ),
                1 => array(
                    'widget_class' => 'SubPanelTopSelectButton',
                    'mode' => 'MultiSelect',
                ),
            ),
        );
        $subpanel_list_fields_2 = array(
            'case_number' =>
            array(
                'vname' => 'LBL_LIST_NUMBER',
                'width' => '6%',
                'default' => true,
            ),
        );
        $subpanel_def_2 = new aSubPanel("contacts_cases_2", $subpanel_2, $this->bean);
        $subpanel_2 = new SubPanel('Contacts', 'fab4', $subpanel_def_2->_instance_properties['subpanel_name'], $subpanel_def_2);
        $subpanel_2->saveSubPanelDefOverride($subpanel_def_2, 'list_fields', $subpanel_list_fields_2);

        $extname_2 = '_overridesubpanel-for-' . $subpanel_def_2->name;

        // Check files genertaed by subpanel overriding : layout override and subpanel overire
        $this->filename_check[] = 'custom/Extension/modules/' . $subpanel_def_1->parent_bean->module_dir . "/Ext/clients/base/layouts/subpanels/$extname_1.php";
        $this->assertTrue(file_exists(end($this->filename_check)));
        $this->filename_check[] = 'custom/Extension/modules/' . $subpanel_def_2->parent_bean->module_dir . "/Ext/clients/base/layouts/subpanels/$extname_2.php";
        $this->assertTrue(file_exists(end($this->filename_check)));

        // no longer in layoutdefs
        foreach (SugarAutoLoader::existing(
                     SugarAutoLoader::loadExtension(
                         "sidecarsubpanelbaselayout",
                         $subpanel_def_2->parent_bean->module_dir
                     )
                 ) as $file) {
            include $file;
        }
        // Check override_subpanel_name are differents
        $this->assertTrue(isset($viewdefs['Contacts']['base']['layout']['subpanels']['components'][0]));
        $this->assertTrue(isset($viewdefs['Contacts']['base']['layout']['subpanels']['components'][1]));
        $this->assertNotEquals(
            $viewdefs['Contacts']['base']['layout']['subpanels']['components'][0]['override_subpanel_list_view'],
            $viewdefs['Contacts']['base']['layout']['subpanels']['components'][1]['override_subpanel_list_view']
        );

    }


}
