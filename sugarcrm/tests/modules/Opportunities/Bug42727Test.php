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

require_once "modules/Opportunities/Opportunity.php";

class MockOpportunity extends Opportunity {

    public $mailWasSent = false;
    public $notify_inworkflow = true;
    public $set_created_by = false;
    
    public function send_assignment_notifications() {
        $this->mailWasSent = true;
    }
}

class Bug42727Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_opportunity;
    protected $_opportunityIds = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->_opportunity = new MockOpportunity();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query('DELETE FROM opportunities WHERE id IN (\'' . implode("', '", $this->_opportunityIds) . '\')');
        SugarTestHelper::tearDown();
    }

    
    public function testSentMail() 
    {
        $this->_opportunity->created_by = $this->_opportunity->assigned_user_id = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->_opportunityIds[] = $this->_opportunity->save();
        $this->assertTrue($this->_opportunity->isOwner($this->_opportunity->created_by));
        $this->assertFalse($this->_opportunity->mailWasSent);
    }
    
    public function testNotSentMail() 
    {
        $this->_opportunity->created_by = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->_opportunity->assigned_user_id = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->_opportunityIds[] = $this->_opportunity->save();
        $this->assertFalse($this->_opportunity->isOwner($this->_opportunity->created_by));
        $this->assertTrue($this->_opportunity->mailWasSent);    
    }
}
