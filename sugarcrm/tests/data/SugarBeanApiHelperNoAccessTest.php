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

require_once 'include/api/ServiceBase.php';
require_once 'data/SugarBeanApiHelper.php';

/**
 * @group ApiTests
 */
class SugarBeanApiHelperNoAccessTest extends Sugar_PHPUnit_Framework_TestCase
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
        $mock->id = 'SugarBeanApiHelperMockBean-1';
        $mock->favorite = false;
        $mock->module_name = 'Test';
        $mock->module_dir = 'Test';
        $mock->field_defs = array(
                'testInt' => array(
                    'type' => 'int',
                ),
                'testDecimal' => array(
                    'type' => 'decimal'
                ),
                'testBool' => array(
                    'type' => 'bool'
                ),
            );
        $mock->expects($this->any())
             ->method('ACLFieldAccess')
             ->will($this->returnValue(false));                
        $this->bean = $mock;
        $this->beanApiHelper = new SugarBeanApiHelper(new ServiceMockup());
    }

    public function tearDown()
    {
        // clean up all roles created
        foreach ($this->roles AS $role) {
            $role->mark_deleted($role->id);
            $role->mark_relationships_deleted($role->id);
            $GLOBALS['db']->query("DELETE FROM acl_fields WHERE role_id = '{$role->id}'");
        }
        unset($_SESSION['ACL']);
        SugarTestHelper::tearDown();
    }

    public function testNoEmail1FieldAccess()
    {
        $this->bean->field_defs['email'] = array('type' => 'email');
        $this->bean->field_defs['email1'] = array('type' => 'varchar');
        $this->bean->emailAddress = array();
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Test']['fields']['email1'] = SugarACL::ACL_NO_ACCESS;
        $this->beanApiHelper->formatForApi($this->bean, array('email', 'email1'));
        $this->assertTrue(!isset($data['email']));
        unset($this->bean->field_defs['email']);
        unset($this->bean->emailAddress);
    }

    public function testNoEmail1FieldAccessSave()
    {
        $this->setExpectedException(
          'SugarApiExceptionNotAuthorized', 'Not allowed to edit field email in module: Test'
        );
        $this->bean->field_defs['email'] = array('type' => 'email');
        $this->bean->field_defs['email1'] = array('type' => 'varchar');
        $this->bean->emailAddress = array();
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Test']['fields']['email1'] = SugarACL::ACL_NO_ACCESS;
        $data['email'] = 'test@test.com';
        $data['module'] = 'Test';
        $this->beanApiHelper->populateFromApi($this->bean, $data);

    }

}

class ServiceMockup extends ServiceBase
{
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
