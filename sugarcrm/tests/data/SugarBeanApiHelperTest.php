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

require_once 'tests/SugarTestRestUtilities.php';
require_once 'tests/SugarTestACLUtilities.php';
require_once 'data/SugarBeanApiHelper.php';

/**
 * @group ApiTests
 */
class SugarBeanApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $bean;
    public $beanApiHelper;

    public $oldDate;
    public $oldTime;

    public $roles = array();

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        // Mocking out SugarBean to avoid having to deal with any dependencies other than those that we need for this test
        $mock = $this->getMock('SugarBean');
        $mock->expects($this->any())
             ->method('ACLFieldAccess')
             ->will($this->returnValue(true));
        $mock->id = 'SugarBeanApiHelperMockBean-1';
        $mock->favorite = false;
        $mock->field_defs = array(
                'testInt' => array(
                    'type' => 'int',
                ),
                'testDecimal' => array(
                    'type' => 'decimal'
                ),
            );
        $this->bean = $mock;
        $serviceMock = SugarTestRestUtilities::getMock();
        $this->beanApiHelper = new SugarBeanApiHelper($serviceMock);
    }

    public function tearDown()
    {
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider providerFunction
     */
    public function testFormatForApi($fieldName, $fieldValue, $expectedFormattedValue, $message)
    {
        $this->bean->$fieldName = $fieldValue;

        $data = $this->beanApiHelper->formatForApi($this->bean);

        $this->assertSame($expectedFormattedValue, $data[$fieldName], $message);
    }

    public function providerFunction()
    {
        return array(
            array('testInt', '', null, 'Bug 57507 regression: expected formatted value for a null int type to be NULL'),
            array('testDecimal', '', null, 'Bug 59692 regression: expected formatted value for a null decimal type to be NULL'),
            array('testInt', '1', 1, "Int type conversion of '1' failed"),
            array('testDecimal', '1', 1.0, "Decimal type conversion of '1' failed"),
            array('testInt', 1.0, 1, "Int type conversion of 1.0 failed"),
            array('testDecimal', 1, 1.0, "Decimal type conversion of 1 failed"),
            array('testInt', '0', 0, "Int type conversion of '0' failed"),
            array('testDecimal', '0', 0.0, "Decimal type conversion of '0' failed"),
            array('testInt', 0.0, 0, "Int type conversion of 0.0 failed"),
            array('testDecimal', 0, 0.0, "Decimal type conversion of 0 failed"),
        );
    }

    public function testJsonFieldSave()
    {
        $userPrefs = BeanFactory::newBean('UserPreferences');

        $submittedData = array(
            'contents' => 'abcd1234'
        );

        $this->beanApiHelper->populateFromApi($userPrefs, $submittedData);

        $this->assertEquals($userPrefs->contents, json_encode($submittedData['contents']));
    }

    public function testListWithOwnerAccess()
    {
        // create role that is all fields read only
        $role = SugarTestACLUtilities::createRole('SUGARBEANAPIHELPER - UNIT TEST ' . create_guid(), array('Meetings'), array('access', 'list', 'view'), array('view'));

        SugarTestACLUtilities::setupUser($role);

        // create a meeting not owned by current user
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->name = 'SugarBeanApiHelperTest Meeting';
        $meeting->assigned_user_id = 1;
        $meeting->id = create_guid();

        // verify I can format the bean for the api and I can see the name and id;
        $data = $this->beanApiHelper->formatForApi($meeting);
        $this->assertEquals($meeting->id, $data['id'], "ID Doesn't Match");
        $this->assertEquals($meeting->name, $data['name'], "Name Doesn't Match");
    }

    public function testListCertainFieldsNoAccess()
    {
        // create role that is all fields read only
        $this->roles[] = $role = $this->createRole('SUGARBEANAPIHELPER - UNIT TEST ' . create_guid(), array('Accounts'), array('access', 'list', 'view'), array('view'));

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }

        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);

        // set the name field as Read Only
        $aclField = new ACLField();

        $aclField->setAccessControl('Accounts', $role->id, 'website', -99);

        unset($_SESSION['ACL']);

        ACLField::loadUserFields('Accounts', 'Account', $GLOBALS['current_user']->id, true );

        // create a meeting not owned by current user
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'SugarBeanApiHelperTest Meeting';
        $account->assigned_user_id = 1;
        $account->id = create_guid();

        $data = $this->beanApiHelper->formatForApi($account, array('id', 'name', 'website'));

        $this->assertNotEmpty($data['_acl']['fields']->website, "Website did not come back and should have.  The acls were: " . print_r($data['_acl'], true));

    }
}
