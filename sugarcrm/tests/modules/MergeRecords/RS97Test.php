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

require_once 'modules/MergeRecords/MergeRecord.php';

/**
 *  RS97: Prepare MergeRecord.
 */
class RS97Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testMerge()
    {
        $acc1 = SugarTestAccountUtilities::createAccount();
        $acc2 = SugarTestAccountUtilities::createAccount();
        $bean = new MergeRecord('Contacts');

        $this->assertEquals('Contacts', $bean->merge_module);

        $bean->load_merge_bean('Accounts', false, $acc1->id);
        $this->assertEquals($acc1->id, $bean->merge_bean->id);
        $this->assertEquals('Accounts', $bean->merge_module);

        $bean->load_merge_bean2('Accounts', false, $acc2->id);
        $this->assertEmpty($bean->merge_bean2);

        $bean->merge_module2 = 'Accounts';
        $bean->load_merge_bean2('Accounts', false, $acc2->id);
        $this->assertEquals($acc2->id, $bean->merge_bean2->id);

        $where = $bean->create_where_statement();
        $need = array("{$acc1->table_name}.id !=" . DBManagerFactory::getInstance()->quoted($acc1->id));
        $this->assertEquals($need, $where);

        $where = $bean->generate_where_statement(array('id = 1', 'name = 2'));
        $need = "id = 1 AND name = 2";
        $this->assertEquals($need, $where);

        $result = $bean->get_inputs_for_search_params(array());
        $this->assertEmpty($result);

        $bean->populate_search_params(array('nameSearchField' => 'value', 'nameSearchType' => 'RS97Test'));
        $this->assertArrayHasKey('name', $bean->field_search_params);

        $where = $bean->build_generic_where_clause('');
        $need = $acc1->build_generic_where_clause('');
        $this->assertEquals($need, $where);


        $where = $bean->fill_in_additional_list_fields();
        $need = $acc1->fill_in_additional_list_fields();
        $this->assertEquals($need, $where);

        $where = $bean->fill_in_additional_detail_fields();
        $need = $acc1->fill_in_additional_detail_fields();
        $this->assertEquals($need, $where);

        $where = $bean->get_summary_text();
        $need = $acc1->get_summary_text();
        $this->assertEquals($need, $where);

        $where = $bean->get_list_view_data();
        $need = $acc1->get_list_view_data();
        $this->assertEquals($need, $where);
    }
}
