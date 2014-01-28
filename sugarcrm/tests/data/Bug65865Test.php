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

require_once("data/BeanFactory.php");

class Bug65865Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestuserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testGetBeanDeleted()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->name = "Test deleted";
        $account->save();
        $account->mark_deleted($account->id);
        $this->assertNotNull(BeanFactory::getBean('Accounts', $account->id, array('deleted' => false, 'strict_retrieve' => true)));
        $this->assertNull(BeanFactory::getBean('Accounts', $account->id,  array('strict_retrieve' => true)));
    }

    public function testGetBeanDisableRowLevelSecurity()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->name = "Test disable_row_level_security";
        $user = SugarTestUserUtilities::createAnonymousUser();
        $teamSet = new TeamSet();
        $teamSet->addTeams($user->getPrivateTeamID());
        $account->team_id = $user->getPrivateTeamID();
        $account->team_set_id = $teamSet->id;
        $account->assigned_user_id = $user->id;
        $account->disable_row_level_security = true;
        $account->save();
        $this->assertNotNull(BeanFactory::getBean('Accounts', $account->id, array('disable_row_level_security' => true, 'strict_retrieve' => true)));
        $this->assertNull(BeanFactory::getBean('Accounts', $account->id,  array('strict_retrieve' => true)));
    }
}
