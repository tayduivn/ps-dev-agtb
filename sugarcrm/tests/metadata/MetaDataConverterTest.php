<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once 'include/MetaDataManager/MetaDataConverter.php';

class MetaDataConverterTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testConvertWirelessListToLegacy() {
        $file = 'modules/Bugs/clients/mobile/views/list/list.php';
        require $file;
        
        $this->assertInternalType('array', $viewdefs['Bugs']['mobile']['view']['list'], 'Expected view def structure not found');
        
        $converted = MetaDataConverter::toLegacy('list', $viewdefs['Bugs']['mobile']['view']['list']);
        $this->assertArrayHasKey('BUG_NUMBER', $converted, 'BUG_NUMBER missing from the conversion');
        $this->assertArrayHasKey('NAME', $converted, 'NAME missing from the conversion');
    }
    
    public function testConvertWirelessDetailToLegacy() {
        $file = 'modules/Bugs/clients/mobile/views/detail/detail.php';
        require $file;
        
        $this->assertInternalType('array', $viewdefs['Bugs']['mobile']['view']['detail'], 'Expected view def structure not found');
        
        $converted = MetaDataConverter::toLegacy('detail', $viewdefs['Bugs']['mobile']['view']['detail']);
        $this->assertNotEmpty($converted['panels'][0][0], 'First string field name is missing');
        $this->assertEquals('bug_number', $converted['panels'][0][0], 'First field name is not as expected');
    }
    
    public function testNoConversionForNonConvertableViewType() {
        $file = 'modules/Bugs/clients/mobile/views/search/search.php';
        require $file;
        
        $this->assertArrayHasKey('layout', $searchdefs['Bugs'], 'No layout found where layout was expected');
        
        $converted = MetaDataConverter::toLegacy('search', $searchdefs);
        $this->assertEquals($converted, $searchdefs, 'Viewdefs converted unexpectedly');
    }
    
    public function testConvertFieldsets() {
        $file = 'tests/metadata/supportfiles/Callsmobileedit.php';
        require $file;
        
        $this->assertInternalType('array', $viewdefs['Calls']['mobile']['view']['edit'], 'Expected view def structure not found for Calls mobile edit');
        $converted = MetaDataConverter::toLegacy('edit', $viewdefs['Calls']['mobile']['view']['edit']);
        $converted = MetaDataConverter::fromGridFieldsets($converted);
        
        $this->assertTrue(isset($converted['panels'][4][0]), "Conversion failed to convert fieldset at offset 4");
        $this->assertEquals('duration_hours', $converted['panels'][4][0], "duration_hours did not convert from a fieldset");
        
        $this->assertTrue(isset($converted['panels'][5][0]), "Conversion failed to convert fieldset at offset 5");
        $this->assertEquals('duration_minutes', $converted['panels'][5][0], "duration_minutes did not convert from a fieldset");
    }
    /**
     * Test converting subpanels
     */
    public function testConvertSubpanels()
    {
        static $fieldMap = array(
            'name' => true,
            'label' => true,
            'type' => true,
            'target_module' => true,
            'target_record_key' => true,
        );
        $converter = new MetaDataConverter();
        require_once 'include/SubPanel/SubPanelDefinitions.php';
        $bean = BeanFactory::getBean('Quotes');

        $spDefs = new SubPanelDefinitions($bean);
        $layout_defs = $spDefs->layout_defs;
        $this->assertTrue(is_array($layout_defs));
        $this->assertNotEmpty($layout_defs['subpanel_setup']);

        foreach ($layout_defs['subpanel_setup'] as $name => $subpanel_info) {
            $aSubPanel = $spDefs->load_subpanel($name, '', $bean);
            $this->assertInstanceOf('aSubpanel', $aSubPanel);

            // no collections
            if ($aSubPanel->isCollection()) {
                continue;
            }
            $panel_definition = $converter->fromLegacySubpanelsViewDefs($aSubPanel->panel_definition, 'Quotes');
        }

        $this->assertNotEmpty($panel_definition, "Panel Definition not set");
        foreach ($panel_definition['panels'] as $panel) {
            $this->assertArrayHasKey('name', $panel, "Panel should have a name field");
            $this->assertArrayHasKey('label', $panel, "Panel should have a label field");
            $this->assertArrayHasKey('fields', $panel, "Panels should have fields");
            foreach ($panel['fields'] as $fieldDef) {
                foreach ($fieldDef as $key => $value) {
                    $this->assertContains($key, $fieldMap);
                }
            }
        }
    }
    /**
     * Test adding custom link to profileactions
     */
    public function testConvertProfileactions()
    {
        $converter = new MetaDataConverter();

        // Create Input field map includes different test link to see if these links are
        // converted properly which has correct label, route and acl_action
        $testlink = array();
        $testlink['google'] = array('linkinfo' => array('Google' => 'https://www.google.com/'),
            'submenu' => ''
        );
        $testlink['contact'] = array('linkinfo' => array('LBL_CONTACTS' => '#Contacts'),
            'submenu' => ''
        );
        $testlink['report'] = array('linkinfo' => array('LBL_REPORTS' => 'index.php?module=Reports&action=index'),
            'submenu' => ''
        );
        $testlink['administration'] = array('linkinfo' => array('LBL_ADMIN' => 'index.php?module=Administration&action=index'),
            'submenu' => ''
        );
        $testlink['support'] = array(
            'linkinfo' => array('LBL_TRAINING' => 'javascript:void(window.open(\'http://support.sugarcrm.com\'))'),
            'submenu' => ''
        );
        $testlink['task'] = array('linkinfo' => array('LBL_TASKS' => '#Tasks'),
            'submenu' => array(
                'case' => array('LBL_CASES' => '#Cases'),
                'note' => array('LBL_NOTES' => '#Notes'),
                'bug'  => array('LBL_BUGS' => '#Bugs'),
                'support' => array('LBL_TRAINING' => 'javascript:void(window.open(\'http://support.sugarcrm.com\'))'),
            )
        );
        $testlink['attachment'] = array('linkinfo' => array('ATTACHMENTS' => 'client/base/views/attachments/attachments.php'),
            'submenu' => ''
        );
        // Transform globalcontrollink format into regular associate array format
        $inputTestLinks = $converter->processFromGlobalControlLinkFormat($testlink);

        // Expected output field map
        $expectedOutput = array(
            'Google' => array( 'label' => 'Google', 'route' => 'https://www.google.com/', 'acl_action' => ''),
            'LBL_CONTACTS' => array( 'label' => 'LBL_CONTACTS', 'route' => '#Contacts', 'acl_action' => ''),
            'LBL_TRAINING' => array( 'label' => 'LBL_TRAINING', 'route' => 'http://support.sugarcrm.com', 'acl_action' => '', 'openwindow' => true),
            'LBL_TASKS' => array( 'label' => 'LBL_TASKS', 'route' => '#Tasks', 'acl_action' => '',
                'submenu' => array(
                    array( 'label' => 'LBL_CASES', 'route' => '#Cases', 'acl_action' => ''),
                    array( 'label' => 'LBL_NOTES', 'route' => '#Notes', 'acl_action' => ''),
                    array( 'label' => 'LBL_BUGS', 'route' => '#Bugs', 'acl_action' => ''),
                    array( 'label' => 'LBL_TRAINING', 'route' => 'http://support.sugarcrm.com', 'acl_action' => '', 'openwindow' => true),
                )
            ),
            'LBL_REPORTS' => array( 'label' => 'LBL_REPORTS', 'route' => '#bwc/index.php?module=Reports&action=index', 'acl_action' => 'list'),
            'LBL_ADMIN' => array( 'label' => 'LBL_ADMIN', 'route' => '#bwc/index.php?module=Administration&action=index', 'acl_action' => 'admin'),
            '' => '',
        );

        // Test if custom links are converted correctly by comparing with expectedOutput
        foreach($inputTestLinks as $item){
            $convertedItem = $converter->convertCustomMenu($item);
            if(!empty($item['SUBMENU'])){
                $convertedSubmenu = array();
                foreach($item['SUBMENU'] as $submenu){
                    $convertedSubmenu[] = $converter->convertCustomMenu($submenu);
                }
                $convertedItem['submenu'] = $convertedSubmenu;
            }
            $this->assertEquals($expectedOutput[$convertedItem['label']], $convertedItem, "{$convertedItem['label']} array did not convert correctly");
        }
    }
}
