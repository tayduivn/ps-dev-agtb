<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


/**
 * @ticket 67378
 */
class Bug67378Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Contact
     */
    private $contact;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $this->contact = SugarTestContactUtilities::createContact();
    }

    protected function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testSubpanelSortOn()
    {
        $data = array(
            'name' => 'assigned_user_name',
            'link' => 'assigned_user_link',
            'vname' => 'LBL_ASSIGNED_TO',
            'rname' => 'full_name',
            'type' => 'relate',
            'reportable' => false,
            'source' => 'non-db',
            'table' => 'users',
            'id_name' => 'assigned_user_id',
            'module' => 'Users',
            'duplicate_merge' => 'disabled',
            'massupdate' => false,
            'db_concat_field' => array(
                0 => 'first_name',
                1 => 'last_name',
            )
        );

        $user = BeanFactory::getBean('Users');
        $relate_query = $user->getRelateFieldQuery($data, 'whatever');

        // key should exist
        $this->assertArrayHasKey('assigned_user_name', $relate_query['fields'], 'Result should have assigned_user_name key.');

        // sorting field should be correct
        $this->assertEquals('whatever.last_name', $relate_query['fields']['assigned_user_name']);

        // sorting field should be in the select array
        $this->assertContains('whatever.last_name assigned_user_name', $relate_query['select']);
    }
}
