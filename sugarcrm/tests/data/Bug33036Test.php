<?php

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

class Bug33036Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $obj;
    
    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public static function tearDownAfterClass()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}

	public function setUp()
	{
	    $this->obj = new Contact();
	}

	public function tearDown()
	{
        if (! empty($this->obj->id)) {
            $this->obj->db->query("DELETE FROM contacts WHERE id = '" . $this->obj->id . "'");
        }
        unset($this->obj);
	}

    public function testAuditForRelatedFields() 
    {
        $test_account_name = 'test account name after';
        
        $account = SugarTestAccountUtilities::createAccount();
        
        $this->obj->field_defs['account_name']['audited'] = 1;
        $this->obj->name = 'test';
        $this->obj->account_id = $account->id;
        $this->obj->save();
        
        $this->obj->retrieve();
        $this->obj->account_name = $test_account_name;
        $changes = $this->obj->db->getDataChanges($this->obj);
        
        $this->assertEquals($changes['account_name']['after'], $test_account_name);
        
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }
}
