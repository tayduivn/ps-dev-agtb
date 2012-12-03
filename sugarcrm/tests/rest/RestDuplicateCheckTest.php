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

require_once('tests/rest/RestTestBase.php');

class RestDuplicateCheckTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');

        //create test leads
        $this->convertedLead = SugarTestLeadUtilities::createLead();
        $this->convertedLead->first_name = 'TestConvertFirst';
        $this->convertedLead->last_name = 'TestLast';
        $this->convertedLead->status = 'Converted';
        $this->convertedLead->save();
        $this->newLead = SugarTestLeadUtilities::createLead();
        $this->newLead->first_name = 'TestNewFirst';
        $this->newLead->last_name = 'TestLast';
        $this->newLead->save();
        $this->newLead2 = SugarTestLeadUtilities::createLead();
        $this->newLead2->first_name = 'TestNewFirst2'; //diff first name
        $this->newLead2->last_name = 'TestLast'; //same last name
        $this->newLead2->save();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestLeadUtilities::removeAllCreatedLeads();

        parent::tearDown();
    }

    /**
     * @group rest
     * @group rest_dupe_check
     */
    public function testDuplicateCheck_TwoFieldsPassed_ReturnsOneResult() {
        $restReply = $this->_restCall("Leads/duplicateCheck",
            json_encode(array('field_data' => array('first_name'=>$this->newLead->first_name, 'last_name' => $this->newLead->last_name))),
            'POST');
        $this->assertEquals(1, count($restReply['reply']), "Should only return one result");
        $this->assertEquals($this->newLead->first_name, $restReply['reply'][0]['first_name'], "Should find lead with correct first name");
        $this->assertEquals($this->newLead->last_name, $restReply['reply'][0]['last_name'], "Should find lead with correct last name");
    }

    /**
     * @group rest
     * @group rest_dupe_check
     */
    public function testDuplicateCheck_OneFieldsPassedAndOneFieldBlank_ReturnsTwoResults() {
        $restReply = $this->_restCall("Leads/duplicateCheck",
            json_encode(array('field_data' => array('first_name'=>'', 'last_name' => $this->newLead->last_name))),
            'POST');
        $this->assertEquals(2, count($restReply['reply']), "Should return two results");
        $this->assertEquals($this->newLead->last_name, $restReply['reply'][0]['last_name'], "Should find lead with correct last name");
        $this->assertEquals($this->newLead2->last_name, $restReply['reply'][1]['last_name'], "Should find lead with correct last name");
    }

    /**
     * @group rest
     * @group rest_dupe_check
     */
    public function testDuplicateCheck_OneFieldsPassedAndOneFieldOmmitted_ReturnsTwoResults() {
        $restReply = $this->_restCall("Leads/duplicateCheck",
            json_encode(array('field_data' => array('last_name' => $this->newLead->last_name))),
            'POST');
        $this->assertEquals(2, count($restReply['reply']), "Should return two results");
        $this->assertEquals($this->newLead->last_name, $restReply['reply'][0]['last_name'], "Should find lead with correct last name");
        $this->assertEquals($this->newLead2->last_name, $restReply['reply'][1]['last_name'], "Should find lead with correct last name");
    }

}
