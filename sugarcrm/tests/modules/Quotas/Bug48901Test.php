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

/**
 *  Bug #48901
 *      Quotas Continue to Display for Deleted Users
 * @ticket 48901
 * @author arymarchik@sugarcrm.com
 */
class Bug48901Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $_timeperiod;
    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        parent::setUp();

        $this->_timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();
    }

    /**
     * @group 48901
     * @outputBuffering disabled
     */
    public function testQuotasDeletedUsers()
    {
        global $current_user;
        $ammount_diff = 100;
        $focus = new Quota48901Mock();
        $amount = $focus->getGroupQuota($this->_timeperiod->id, false);
        $user = SugarTestUserUtilities::createAnonymousUser(false);
        $user->reports_to_id = $current_user->id;
        $user->save();

        $bean = new Quota48901Mock();
        $bean->quota_type = "Direct";
        $bean->created_by = $current_user->id;
        $bean->user_id = $user->id;
        $bean->timeperiod_id = $this->_timeperiod->id;
        $bean->amount = $ammount_diff;
        $bean->amount_base_currency = $ammount_diff;
        $bean->currency_id = -99;
        $bean->committed = 0;
        $bean->save();
        $amount2 = $focus->getGroupQuota($this->_timeperiod->id, false);

        $this->assertEquals($amount2 - $amount, $ammount_diff);
        $data = $bean->getUserManagedSelectData($this->_timeperiod->id);
        $this->assertContains($user->id, $this->getUsersArray($data));

        $user->mark_deleted($user->id);

        $amount2 = $focus->getGroupQuota($this->_timeperiod->id, false);

        $this->assertEquals($amount, $amount2);
        $data = $bean->getUserManagedSelectData($this->_timeperiod->id);
        $this->assertNotContains($user->id, $this->getUsersArray($data));

        $bean->db->delete($bean, array('id' => $bean->id));
    }

    private function getUsersArray($data)
    {
        $result = array();
        foreach($data as $k => $v)
        {
            array_push($result, $v['user_id']);
        }
        return $result;
    }

    public function tearDown()
    {
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestHelper::tearDown();
    }
}

class Quota48901Mock extends Quota
{
    public function getUserManagedSelectData($time_period)
    {
        return parent::getUserManagedSelectData($time_period);
    }
}
