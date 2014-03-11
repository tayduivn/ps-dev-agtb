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
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


/**
 * Bug #67170
 * @ticket 67170
 */
class Bug67170Test extends Sugar_PHPUnit_Framework_TestCase
{


    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Contacts'));
        SugarTestHelper::setUp('current_user', array(true, 1));

    }


    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests that create_export_query, which uses 'create_new_list_query' does not perform extra
     * table joins that are not needed for exporting
     */
    public function testListQuery()
    {
        $bean = BeanFactory::getBean('Contacts');
        //simulate call from export_utils to retrieve export query
        $query = $bean->create_export_query('', 'contacts.deleted id = 0');

        $this->assertNotContains('calls_contacts',$query, ' calls_contacts was found in string, extra table joins have been introduced into export query');
        $this->assertNotContains('opportunities',$query, ' opportunities was found in string, extra table joins have been introduced into export query');
    } 

}
