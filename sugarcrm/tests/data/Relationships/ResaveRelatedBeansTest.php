<?php
//FILE SUGARCRM flav=pro ONLY
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
 * Make sure that the resave pulls all beans from cache again
 * so it's not stuck with an old version of a bean overwriting changes
 */
class ResaveRelatedBeansTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $id;

    protected function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->id = 'ResaveRelatedBeansTestId';
    }

    protected function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testResaveRelatedBeans()
    {
        // Create account and add it to resave list
        $account = SugarTestAccountUtilities::createAccount($this->id);
        SugarRelationship::addToResaveList($account);

        // Now update some data for the account
        $account = BeanFactory::getBean('Accounts');
        $account->id = $this->id;
        $account->name = 'New Name';
        $account->save();

        // And the resave fires after the save()
        SugarRelationship::resaveRelatedBeans();
        // Let's make sure that the latest changes don't get overwritten by an old queued version of the bean
        $savedAccount = BeanFactory::getBean('Accounts', $this->id);

        $this->assertEquals(
            $account->name,
            $savedAccount->name,
            'resaveRelatedBeans() should pull in the latest version from the cache'
        );
    }
}
