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

require_once 'modules/MySettings/TabController.php';

/**
 * User profile Save tests
 *
 * @author mmarum
 */
class SaveTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $current_user;
    protected $tabs;
    protected $savedTabs;

    public function setUp()
    {
        parent::setUp();
        $this->current_user = SugarTestHelper::setUp('current_user', array(true, 1));
        $this->tabs = new TabController();
        $this->savedTabs = $this->tabs->get_user_tabs($this->current_user);
    }

    public function tearDown()
    {
        $this->tabs->set_user_tabs($this->savedTabs, $this->current_user, "display");
        unset($this->bean);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Home always needs to be first display tab
     */
    public function testAddHomeToDisplayTabsOnSave()
    {
        $current_user = $this->current_user;
        $_POST['record'] = $current_user->id;
        $_REQUEST['display_tabs_def'] = 'display_tabs[]=Leads';  //Save only included Leads
        include('modules/Users/Save.php');
        //Home was prepended
        $this->assertEquals(array('Home' => 'Home', 'Leads' => 'Leads'), $this->tabs->get_user_tabs($focus));
    }
}
