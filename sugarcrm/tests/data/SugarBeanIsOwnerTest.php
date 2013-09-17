<?php

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('data/SugarBean.php');

class SugarBeanIsOwner extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
	}

	public static function tearDownAfterClass()
	{
	    SugarTestHelper::tearDown();
	}

    public function testIsOwnerNew()
    {
        $bean = new SugarBean();

        $this->assertTrue($bean->isOwner('DONT-CARE'),"SugarBean->isOwner() should return true if there is no id.");
       
        $bean->id = "TEST-BEAN-PLEASE-IGNORE";
        $bean->new_with_id = true;

        $this->assertTrue($bean->isOwner('DONT-CARE'),"SugarBean->isOwner() should return true if there is an id but new_with_id is true");

        $bean->new_with_id = false;
        $this->assertFalse($bean->isOwner('DONT-CARE'),"SugarBean->isOwner() should return false if there is an id but new_with_id is false");

    }

    public function testIsOwnerAssignedUserId()
    {

        $bean = new SugarBean();
        $bean->id = 'TEST-BEAN-PLEASE-IGNORE';
        $bean->assigned_user_id = 'MY-ONE-AND-ONLY-USER';

        $this->assertTrue($bean->isOwner('MY-ONE-AND-ONLY-USER'),"SugarBean->isOwner() should return true if the assigned user matches the passed in user");

        $this->assertFalse($bean->isOwner('NOT-ME'),"SugarBean->isOwner() should return false if the assigned user doesn't match the passed in user");
        
        $bean->assigned_user_id = 'OTHER-KIDS';
        $bean->fetched_row = array('assigned_user_id' => 'MY-ONE-AND-ONLY-USER');
        
        $this->assertTrue($bean->isOwner('MY-ONE-AND-ONLY-USER'),"SugarBean->isOwner() should return true if the passed in user matches the fetched row assigned user");

        $this->assertTrue($bean->isOwner('OTHER-KIDS'),"SugarBean->isOwner() should return true if the passed in user matches the assigned user but not the fetched row");

        $this->assertFalse($bean->isOwner('NOT-ME'),"SugarBean->isOwner() should return false if the passed in user doesn't match the fetched row or normal assigned user ");
        

        unset($bean->fetched_row);
        unset($bean->assigned_user_id);
        
        $bean->created_by = 'MY-ONE-AND-ONLY-USER';
        
        $this->assertTrue($bean->isOwner('MY-ONE-AND-ONLY-USER'),"SugarBean->isOwner() should return true if the created by user matches the passed in user and there is no assigned user");

        $this->assertFalse($bean->isOwner('NOT-ME'),"SugarBean->isOwner() should return false if the created by user doesn't match the passed in user and there is no assigned user");

        $bean->assigned_user_id = 'OTHER-KIDS';

        $this->assertFalse($bean->isOwner('MY-ONE-AND-ONLY-USER'),"SugarBean->isOwner() should return false if the created by user matches the passed in user and there is an assigned user");
        
        
    }
}
