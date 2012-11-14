<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'tests/include/SubPanel/SubPanelTestBase.php';

/**
 * Bug 57699
 * 
 * Setup and TearDown are called in the parent
 */
class Bug57699Test extends SubPanelTestBase
{
    protected $_testModule = 'Accounts';
    
    public function setUp() {
        parent::setUp();
        
        // Set up our test defs
        $this->_testDefs = array(
            'order' => 40,
            'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
            'type' => 'collection',
            'subpanel_name' => 'history',   //this values is not associated with a physical file.
            'sort_order' => 'desc',
            'sort_by' => 'date_entered',
            'header_definition_from_subpanel'=> 'calls',
            'module'=>'History',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateNoteButton'),
            ),	
            'collection_list' => array(		
                'notes' => array(
                    'module' => 'Notes',
                    'subpanel_name' => 'ForCalls',
                    'get_subpanel_data' => 'notes',
                ),		
            ), 
        );
    }
    
    /**
     * Tests that Notes is a shown subpanel for Calls out of the box
     * 
     * @group Bug57699
     */
    public function testNotesSubpanelOnCallsAllowedOnDefaultInstallation() {
        $subpanel = new aSubPanel('history', $this->_testDefs, $this->_testBean);
        $this->assertArrayHasKey('notes', $subpanel->sub_subpanels, "Notes module not found in History subpanel's Notes subpanel");
    }
    
    /**
     * Tests that Notes is a shown subpanel for Calls even when removed from the
     * module tabs
     * 
     * @group Bug57699
     */
    public function testNotesSubpanelOnCallsAllowedWhenNotesIsHiddenFromNav() {
        // Adjust the module list by removing Notes from nav and prove it's still there
        $currentTabs = $this->_currentTabs;
        unset($currentTabs['Notes']);
        $this->_tabController->set_system_tabs($currentTabs);
        
        $subpanel = new aSubPanel('history', $this->_testDefs, $this->_testBean);
        $this->assertArrayHasKey('notes', $subpanel->sub_subpanels, "Notes module not found in History subpanel's Notes subpanel after module list modified");
    }
    
    /**
     * Tests that Notes is not a shown subpanel for Calls when removed from subpanels
     * 
     * @group Bug57699
     */
    public function testNotesSubpanelOnCallsNotAllowedWhenNotesIsHiddenFromSubpanels() {
        // Remove Notes from the subpanel modules and test it is NOT shown
        $hidden = $this->_currentSubpanels['hidden'];
        $hidden['notes'] = 'notes';
        $hiddenKeyArray = TabController::get_key_array($hidden);
        $this->_subPanelDefinitions->set_hidden_subpanels($hiddenKeyArray);
        
        // Rebuild the cache
        $this->_subPanelDefinitions->get_all_subpanels(true);
        $this->_subPanelDefinitions->get_hidden_subpanels();
        
        $subpanel = new aSubPanel('history', $this->_testDefs, $this->_testBean);
        $this->assertEmpty($subpanel->sub_subpanels, "History subpanel's subpanel should be empty after Notes removed from subpanel module list");
    }
}