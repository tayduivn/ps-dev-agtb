<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

/**
 * @ticket 67730
 */
class Bug67730Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testUserBeanAclFields()
    {
        global $dictionary, $current_user;
        $acl_fields = (isset($dictionary['User']['acl_fields']) && $dictionary['User']['acl_fields'] === false) ? false : true;
        $this->assertEquals($acl_fields, $current_user->acl_fields, "current_user->acl_fileds should be $acl_fields");
        $bean = BeanFactory::getBean('Users', $current_user->id);
        $this->assertEquals($acl_fields, $bean->acl_fields, "acl_fileds of cached User bean should be $acl_fields");
    }
}
