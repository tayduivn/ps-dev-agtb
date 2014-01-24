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
 * @ticket 66010
 */
class Bug66010Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testCreateNewListQuery()
    {
        $bean = BeanFactory::getBean('Accounts');
        $query = $bean->create_new_list_query("","",array('create_by_name','modified_by_name'),array(),0,"",true);
        $this->assertEquals(1, substr_count($query['select'], 'accounts.created_by'));
        $this->assertEquals(1, substr_count($query['select'], 'accounts.modified_user_id'));
        $query = $bean->create_new_list_query("","",array(),array(),0,"",true);
        $this->assertEquals(0, substr_count($query['select'], 'accounts.modified_user_id'));
        $query = $bean->create_new_list_query("","",array('modified_by_name','modified_user_id'),array(),0,"",true);
        $this->assertEquals(1, substr_count($query['select'], 'accounts.modified_user_id'));
    }
}
