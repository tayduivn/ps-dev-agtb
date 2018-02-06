<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient\Rest as RestApiClient;
use Sugarcrm\Sugarcrm\Security\Subject\User;

class AuditTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $contactBean;
    private $user1;
    private $user2;

    /**
     * Beans registered through BeanFactory
     * @var array
     */
    private $registeredBeans = [];

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        parent::setUp();
    }

    private function createContactBeanWithAuditLog()
    {
        $this->user1 = SugarTestUserUtilities::createAnonymousUser();
        $this->user2 = SugarTestUserUtilities::createAnonymousUser();
        $this->contactBean = SugarTestContactUtilities::createContact(
            null,
            ['assigned_user_id' => $this->user1->id]
        );

        $context = Container::getInstance()->get(Context::class);
        $subject = new User($GLOBALS['current_user'], new RestApiClient());
        $context->activateSubject($subject);
        $context->setAttribute('platform', 'base');

        //retrieve bean otherwise change will not be detected.
        $this->contactBean = $this->contactBean->retrieve();
        $this->contactBean->assigned_user_id = $this->user2->id;
        $this->contactBean->save(false);
    }

    private function registerBean(\SugarBean $bean)
    {
        BeanFactory::registerBean($bean);
        $this->registeredBeans[] = $bean;
    }

    public function tearDown()
    {
        unset($this->bean);
        foreach ($this->registeredBeans as $bean) {
            BeanFactory::unregisterBean($bean);
        }
        parent::tearDown();
    }

    public function testGetAuditLogTranslation()
    {
        $this->createContactBeanWithAuditLog();

        $audit = BeanFactory::newBean('Audit');
        $auditLog = $audit->getAuditLog($this->contactBean);

        $this->assertNotEmpty($auditLog, 'Audit log not created or retrieved.');
        $this->assertEquals(
            $this->user2->user_name,
            $auditLog[0]['after'],
            'Ids in audit log not translated.'
        );
    }

    public function testFormatSourceSubject()
    {
        $this->createContactBeanWithAuditLog();

        $audit = BeanFactory::newBean('Audit');
        $auditLog = $audit->getAuditLog($this->contactBean);

        $this->assertNotEmpty($auditLog, 'Audit log not created or retrieved.');
        $this->assertEquals(
            $GLOBALS['current_user']->name,
            $auditLog[0]["source"]["subject"]["name"],
            'Audit log source subject not formatted correctly.'
        );
    }

    public function testHandleRelateField()
    {
        $userBeforeMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(array('get_summary_text'))
            ->getMock();
        $userBeforeMock->module_name = 'Users';
        $userBeforeMock->id = create_guid();
        $userBeforeMock->expects($this->once())
            ->method('get_summary_text')
            ->will($this->returnValue('Jim Brennan'));
        $this->registerBean($userBeforeMock);
        $userAfterMock = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(array('get_summary_text'))
            ->getMock();
        $userAfterMock->module_name = 'Users';
        $userAfterMock->id = create_guid();
        $userAfterMock->expects($this->once())
            ->method('get_summary_text')
            ->will($this->returnValue('Sally Bronsen'));
        $this->registerBean($userAfterMock);
        $row = array(
            'field_name' => 'user_id_c',
            'before_value_string' => $userBeforeMock->id,
            'after_value_string' => $userAfterMock->id,
        );
        $expected = array(
            'field_name' => 'user_c',
            'after' => 'Sally Bronsen',
            'before' => 'Jim Brennan'
        );
        $bean = new SugarBean();
        $bean->audit_enabled_fields = array(
            'user_c' => array(
                'name' => 'user_c',
                'type' => 'relate',
                'id_name' => 'user_id_c',
                'module' => 'Users')
        );
        $bean->auditEnabledRelateFields = array(
            'user_id_c' => array(
                'name' => 'user_c',
                'type' => 'relate',
                'id_name' => 'user_id_c',
                'module' => 'Users')
        );
        $audit = BeanFactory::newBean('Audit');
        SugarTestReflection::callProtectedMethod($audit, 'handleRelateField', array($bean, &$row));
        $this->assertEquals($expected, $row, "Expected Result was incorrect for relate field");
    }
}
