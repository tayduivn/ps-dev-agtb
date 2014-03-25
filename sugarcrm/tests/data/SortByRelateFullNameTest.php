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
 * Make sure that list data is properly sorted by relate full name field
 */
class SortByRelateFullNameTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testSortByRelateFullName()
    {
        $contact = BeanFactory::getBean('Notes');
        $query = $contact->create_new_list_query('contact_name', null, array(), array(), 0, '', true);

        $order_by = $query['order_by'];

        // ORDER BY should contain "last_name" since it's in "sort_on" attribute of contact.name
        $this->assertContains('last_name', $order_by);

        // but shouldn't contain "first_name" since it's not in "sort_on" attribute of contact.name
        $this->assertNotContains('first_name', $order_by);
    }
}
