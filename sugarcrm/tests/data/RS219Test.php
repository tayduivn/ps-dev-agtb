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
 * Bug #RS-219
 * @description Fixes Oracle error ORA-00918 while creating new list query.
 *
 * @ticket RS219
 */
class RS219Test extends Sugar_PHPUnit_Framework_TestCase
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
     * Tests SugarBean::create_new_list_query for valid count of fields.
     *
     * @param {string} $beanName
     * @param {array} $filter
     * @param {string} $needed
     *
     * @group RS219
     * @dataProvider getTestsData
     */
    public function testCreateNewListQuery($beanName, $filter, $needed)
    {
        $bean = BeanFactory::getBean($beanName);
        $query = $bean->create_new_list_query('', '', $filter, array(), 0, '', true);
        $this->assertEquals(substr_count($query['select'], $needed), 1);
    }

    public function getTestsData()
    {
        return array(
            /**
             * Need 2 relate fields with same link.
             * Relationship's `rel_key` value should be named like one of selected field.
             */
            array(
                'Contacts',
                array('account_name', 'account_id'),
                'account_id'
            ),
            array(
                'Contacts',
                array('account_name'),
                'account_id'
            ),
            array(
                'Contacts',
                array('account_id'),
                'account_id'
            ),
            /**
             * Need 2 relate fields with same link.
             * One of them need to have another one in `additional_fields`.
             */
            array(
                'Leads',
                array('campaign_name', 'campaign_id'),
                'campaign_id'
            ),
            array(
                'Leads',
                array('campaign_name'),
                'campaign_id'
            ),
            array(
                'Leads',
                array('campaign_id'),
                'campaign_id'
            ),
        );
    }
}

