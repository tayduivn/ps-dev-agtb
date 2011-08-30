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
 
require_once 'include/ListView/ListViewSmarty.php';

/**
 * Bug45566Test
 * 
 * A simple test to verify that we still have a uid form element even when the ListViewSmarty multiSelect class variable is set to false
 * Other verifications will be needed, but this was a critical variable that was missing
 *
 */
class Bug45566Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }


    public function testListViewDisplayMultiSelect()
    {
        $lv = new ListViewSmarty();
        $lv->multiSelect = false;
        $lv->should_process = true;
        $account = new Account();
        $lv->seed = $account;
        $lv->displayColumns = array();
        $mockData = array();
        $mockData['data'] = array();
        $mockData['pageData'] = array('ordering'=>'ASC', 'offsets' => array('current'=>0, 'next'=>0, 'total'=>0), 'bean'=>array('moduleDir'=>$account->module_dir));
        $lv->process('include/ListView/ListViewGeneric.tpl', $mockData, $account->module_dir);
        $this->assertEquals('<textarea style="display: none" name="uid"></textarea>', $lv->ss->_tpl_vars['multiSelectData'], 'Assert that multiSelectData Smarty variable was still assigned');
    }

}

?>