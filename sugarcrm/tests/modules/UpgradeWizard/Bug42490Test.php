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
 
require_once('modules/UpgradeWizard/uw_utils.php');
require_once('modules/MySettings/TabController.php');

class Bug42490Test extends Sugar_PHPUnit_Framework_TestCase 
{
	private $_originalEnabledTabs;
	private $_tc;
	
    public function setUp()
    {
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user', array(true, 1));
        $this->_tc = new TabController();
        $tabs = $this->_tc->get_tabs_system();
        $this->_originalEnabledTabs = $tabs[0];
    }

	public function tearDown() 
	{
        if (!empty($this->_originalEnabledTabs))
        {
            $this->_tc->set_system_tabs($this->_originalEnabledTabs);
        }
	}

	public function testUpgradeDisplayedTabsAndSubpanels() 
	{
        $modules_to_add = array(
            //BEGIN SUGARCRM flav!=dce ONLY
            'Calls',
            'Meetings',
            'Tasks',
            'Notes',
            //BEGIN SUGARCRM flav!=sales ONLY
            'Prospects',
            'ProspectLists',
            //END SUGARCRM flav!=sales ONLY
            //END SUGARCRM flav!=dce ONLY
        );

		upgradeDisplayedTabsAndSubpanels('610');
		
		$all_tabs = $this->_tc->get_tabs_system();
		$tabs = $all_tabs[0];
		
		foreach($modules_to_add as $module)
		{
            $this->assertArrayHasKey($module, $tabs, 'Assert that ' . $module . ' tab is set for system tabs');
		}
	}
}
