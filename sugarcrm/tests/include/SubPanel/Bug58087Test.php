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
 * Bug 58087 - Compose Email in activities sub panel
 * 
 * Tests the presence of the notes module in subpanels for offline client. Extends
 * the SubPanelTestBase which handle most of the setup and tear down.
 */
class Bug58087Test extends SubPanelTestBase
{
    protected $_modListHeaderGlobal = array();
    protected $_sugarConfig;
    protected $_testModule = 'Accounts';
    
    public function setUp() {
        parent::setUp();
        
        // Set up our test defs - borrowed from Accounts subpaneldefs
        $this->_testDefs = array(
            'order' => 10,
            'sort_order' => 'desc',
            'sort_by' => 'date_start',
            'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
            'type' => 'collection',
            'subpanel_name' => 'activities',   //this values is not associated with a physical file.
            //BEGIN SUGARCRM flav!=dce ONLY
            'header_definition_from_subpanel'=> 'meetings',
            //END SUGARCRM flav!=dce ONLY
            'module'=>'Activities',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateTaskButton'),
                //BEGIN SUGARCRM flav!=dce ONLY
                array('widget_class' => 'SubPanelTopScheduleMeetingButton'),
                array('widget_class' => 'SubPanelTopScheduleCallButton'),
                //END SUGARCRM flav!=dce ONLY
                array('widget_class' => 'SubPanelTopComposeEmailButton'),
            ),
            'collection_list' => array(
                'tasks' => array(
                    'module' => 'Tasks',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'tasks',
                ),
                //BEGIN SUGARCRM flav!=dce ONLY
                'meetings' => array(
                    'module' => 'Meetings',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'meetings',
                ),
                'calls' => array(
                    'module' => 'Calls',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'calls',
                ),
                //END SUGARCRM flav!=dce ONLY
            ),
        );
        
        // This test requires modListHeader
        if (!empty($GLOBALS['modListHeader'])) {
            $this->_modListHeaderGlobal = $GLOBALS['modListHeader'];
        }
        
        $GLOBALS['modListHeader'] = query_module_access_list($GLOBALS['current_user']);
        
        // One test will modify sugar_config
        $this->_sugarConfig = $GLOBALS['sugar_config'];
    }
    
    public function tearDown()
    {
        parent::tearDown();
        
        if (!empty($this->_modListHeaderGlobal)) {
            $GLOBALS['modListHeader'] = $this->_modListHeaderGlobal;
        }
        
        $GLOBALS['sugar_config'] = $this->_sugarConfig;
    }

    /**
     * @group Bug58087
     */
    public function testEmailActionMenuItemExistsInSubpanelActionsOnDefaultInstallation()
    {
        $subpanel = new aSubPanel('activities', $this->_testDefs, $this->_testBean);
        $buttons = $subpanel->get_buttons();
        $test = $this->_hasEmailAction($buttons);
        $this->assertTrue($test, "Compose Email action missing when it was expected");
    }

    /**
     * @group Bug58087
     */
    public function testEmailActionMenuItemDoesNotExistInSubpanelActionsWhenInOfflineClient() 
    {
        $GLOBALS['sugar_config']['disc_client'] = true;
        $GLOBALS['sugar_config']['oc_converted'] = true;
        
        // Test it
        $subpanel = new aSubPanel('activities', $this->_testDefs, $this->_testBean);
        $buttons = $subpanel->get_buttons();
        $test = $this->_hasEmailAction($buttons);
        $this->assertFalse($test, "Compose Email button returned when it was supposed to be excluded");
    }

    /**
     * Helper method that scans an array and checks for the presence of a value
     * 
     * @param array $buttons
     * @return bool
     */
    protected function _hasEmailAction($buttons) 
    {
        foreach ($buttons as $button) {
            if (isset($button['widget_class']) && $button['widget_class'] == 'SubPanelTopComposeEmailButton') {
                return true;
            }
        }
        
        return false;
    }
}