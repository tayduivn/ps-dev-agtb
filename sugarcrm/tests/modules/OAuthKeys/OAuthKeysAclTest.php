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


/* This unit test class covers the ACLs added for extra modules, this does not cover the Users/Employees modules, those are more intense. */
class OAuthKeysAclTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        // Need to be an admin to get to OAuthKeys
        $GLOBALS['current_user']->is_admin = 1;
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function testCreate()
    {
        $testBean = BeanFactory::newBean('OAuthKeys');
        
        $canCreate = $testBean->ACLAccess('create');
        $this->assertTrue($canCreate,"Should be able to create a new record.");

        $canCreateType = $testBean->ACLFieldAccess('oauth_type','create');
        $this->assertTrue($canCreateType,"Should be able to create oauth_type");

        $canCreateName = $testBean->ACLFieldAccess('name','create');
        $this->assertTrue($canCreateName,"Should be able to create name");
    }

    public function testEdit()
    {
        $testBean = BeanFactory::newBean('OAuthKeys');
        $testBean->id = "JUST_A_SMALL_TOWN_GIRL";
        $testBean->name = "Living in a lonely world";
        $testBean->c_key = "midnight_train";
        
        $canEdit = $testBean->ACLAccess('edit');
        $this->assertTrue($canEdit,"Should be able to edit an existing record.");

        $canEditType = $testBean->ACLFieldAccess('oauth_type','edit');
        $this->assertFalse($canEditType,"Should not be able to edit oauth_type");

        $canEditName = $testBean->ACLFieldAccess('name','edit');
        $this->assertTrue($canEditName,"Should be able to edit name");
    }
 
    public function testSpecialCKey()
    {
        $testBean = BeanFactory::newBean('OAuthKeys');
        $testBean->id = "STREETLIGHT_PEOPLE";
        $testBean->name = "Up and down the boulevard";
        $testBean->c_key = "sugar";
        
        $canEdit = $testBean->ACLAccess('edit');
        $this->assertFalse($canEdit,"Should not be able to edit a record with a c_key of 'sugar'");
    }
}
