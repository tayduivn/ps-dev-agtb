<?php
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

require_once('modules/Users/UsersApiHelper.php');
require_once('include/api/RestService.php');

class UsersApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $helper;
    protected $bean = null;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');

        $this->bean = BeanFactory::newBean('Users');
        $this->bean->id = create_guid();

        $this->helper = $this->getMock('UsersApiHelper', array('checkUserAccess'), array(new UsersServiceMockup()));
    }

    public function tearDown()
    {
        unset($this->bean);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testFormatForApi_HasAccessArgumentsPassed_ReturnsHasAccessResult()
    {
        $options = array(
            'args' => array(
                'has_access_module' => 'Foo',
                'has_access_record' => '123'
            ),
        );

        $this->helper->expects($this->once())
            ->method('checkUserAccess')
            ->will($this->returnValue(true));

        $data = $this->helper->formatForApi($this->bean, array(), $options);
        $this->assertEquals($data['has_access'], true, "Has Access should be true");
    }

    public function testFormatForApi_NoHasAccessArgumentsPassed_DoesNotReturnHasAccessResult()
    {
        $options = array(
            'args' => array(),
        );

        $this->helper->expects($this->never())
            ->method('checkUserAccess');

        $data = $this->helper->formatForApi($this->bean, array(), $options);
        $this->assertEquals(array_key_exists('has_access', $data), false, "Has Access data should not exist");
    }
}

class UsersServiceMockup extends ServiceBase
{
    public function __construct() {$this->user = $GLOBALS['current_user'];}
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
