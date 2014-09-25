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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

/**
 * Bug #PAT-973
 */
class PAT973Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true));
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests SugarBean::create_new_list_query.
     */
    public function testCreateNewListQuery()
    {
        $bean = BeanFactory::getBean("Contacts");
        $filter = array(
            "account_id",
            "opportunity_role_fields",
            "opportunity_role_id",
            "opportunity_role"
        );
        $params = array(
            "distinct" => false,
            "joined_tables" => array(0 => "opportunities_contacts"),
            "include_custom_fields" => true,
            "collection_list" => null
        );
        $query = $bean->create_new_list_query("", "", $filter, $params, 0, "", true);

        $this->assertNotContains("opportunity_role_fields", $query["secondary_select"], "secondary_select should not contain fields with relationship_fields defined (e.g. opportunity_role_fields).");
        $this->assertContains("opportunity_role_id", $query["secondary_select"], "secondary_select should contain the fields that's defined in relationship_fields (e.g. opportunity_role_id).");
    }
}
