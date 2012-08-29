<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
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


/**
 * Bug #55038
 * Multi-Select Field Shows as Drop Down in Mobile Browser
 *
 * @author mgusev@sugarcrm.com
 * @ticked 55038
 */
class Bug55038Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Account
     */
    protected $bean = null;

    /**
     * @var array
     */
    protected $field_defs = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
        $_POST = array(
            'bug' => array(
                '0',
                '1',
                '2'
            ),
            'name' => __CLASS__,
            'team_id' => 1
        );

        $this->bean = new Account();
        $this->field_defs = $this->bean->field_defs;
        $this->bean->field_defs = array(
            'bug' => array(
                'required' => false,
                'source' => 'custom_fields',
                'name' => 'bug',
                'type' => 'multienum',
                'default' => NULL,
                'isMultiSelect' => true
            )
        );
        $this->bean->bug = '';
        $this->bean->team_id = 1;
        $this->bean->name = __CLASS__;
    }

    public function tearDown()
    {
        $this->bean->field_defs = $this->field_defs;
        $_POST = array();
        SugarTestHelper::tearDown();
    }

    /**
     * Test tries asserts that zero key with zero value saved too
     *
     * @group 55038
     * @return void
     */
    public function testPreSave()
    {
        $viewWirelesSsave = new ViewWirelesssave_55038_Mock();
        $viewWirelesSsave->module = 'Accounts';
        $viewWirelesSsave->bean = $this->bean;
        $viewWirelesSsave->pre_save();

        $this->assertEquals('^0^,^1^,^2^', $this->bean->bug, 'Some keys are missed');
    }
}

class ViewWirelesssave_55038_Mock extends ViewWirelesssave
{
    public function pre_save()
    {
        return parent::pre_save();
    }
}
