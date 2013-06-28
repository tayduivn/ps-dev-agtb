<?php
//FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'include/api/ServiceBase.php';
require_once 'data/SugarBeanApiHelper.php';

/**
 * @group ApiTests
 */
class SugarBeanApiHelperNoAccessTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $bean;
    public $beanApiHelper;

    protected function setUp()
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
        $this->beanApiHelper = new SugarBeanApiHelper(new SugarBeanApiHelperNoAccessTest_ServiceMockup());
    }

    protected function tearDown()
    {
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

class SugarBeanApiHelperNoAccessTest_ServiceMockup extends ServiceBase
{
    public function execute() 
    {

    }

    protected function handleException(Exception $exception) 
    {

    }
}
