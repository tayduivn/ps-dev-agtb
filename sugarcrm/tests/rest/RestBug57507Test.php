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
require_once 'tests/rest/RestTestBase.php';

/**
 * Bug 57507 - Empty int's & floats shouldn't be 0
 */
class RestBug57507Test extends RestTestBase
{
    public function setUp()
    {
        parent::setUp();

        if ( !isset($this->accounts) ) {
            $this->accounts = array();
        }
        $account = BeanFactory::newBean('Accounts');
        $account->name = "Bug 57507 Test Account";
        $account->team_id = '1';
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->save();
        $this->accounts[] = $account;

        $this->opps = array();
        $this->calls = array();
    }

    public function tearDown()
    {
        // Transition this to _cleanUpRecords() when it is available
        foreach ( $this->opps as $opp ) {
            $opp->mark_deleted($opp->id);
        }
        foreach ( $this->calls as $call ) {
            $call->mark_deleted($call->id);
        }
        foreach ( $this->accounts as $account ) {
            $account->mark_deleted($account->id);
        }
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testEmptySaveInt()
    {
        $reply = $this->_restCall("Calls/",
                                  json_encode(array('name' => 'Test call, empty int',
                                                    'duration_hours' => 1,
                                                    'duration_minutes' => 15,
                                                    'date_start' => TimeDate::getInstance()->asIso(TimeDate::getInstance()->getNow()),
                                                    'status' => 'Not Held',
                                                    'direction' => 'Incoming',
                                                    'repeat_count' => null,
                                                  )),
                                  'POST');
        $this->assertTrue(!empty($reply['reply']['id']),'Could not create a call..response was: ' . print_r($reply, true));
        $call = BeanFactory::getBean('Calls',$reply['reply']['id']);
        $this->calls[] = $call;
        // because of a change to SugarFieldInt this should return null
        $this->assertTrue($call->repeat_count == null,"The repeat count has a value.");
        
    }

    /**
     * @group rest
     */
    public function testEmptyRetrieveInt()
    {
        $call = BeanFactory::newBean('Calls');
        $call->name = 'Test call, empty int';
        $call->duration_hours = '1';
        $call->duration_minutes = 15;
        $call->date_start = TimeDate::getInstance()->getNow()->asDb();
        $call->status = 'Not Held';
        $call->direction = 'Incoming';
        $call->repeat_count = null;
        $call->save();
        $this->calls[] = $call;
        
        $reply = $this->_restCall("Calls/".$call->id);

        $this->assertNull($reply['reply']['repeat_count'],'Repeat count is different from null');
    }
}