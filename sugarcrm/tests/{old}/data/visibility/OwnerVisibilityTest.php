<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class OwnerVisibilityTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $account;

    protected function setUp()
    {
        parent::setUp();

        /** @var User $user */
        $user = SugarTestHelper::setUp('current_user');

        $this->account = SugarTestAccountUtilities::createAccount(null, array(
            'assigned_user_id' => $user->id,
        ));

        SugarTestAccountUtilities::createAccount(null, array(
            'assigned_user_id' => create_guid(),
        ));
    }

    protected function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        parent::tearDown();
    }

    public function testSugarQuery()
    {
        $query = new SugarQuery();
        $query->from($this->account, array(
            'team_security' => false,
        ));
        $query->select('id');

        $visibility = new OwnerVisibility($this->account);
        $visibility->addVisibilityQuery($query);

        $data = $query->execute();

        $this->assertVisibilityApplied($data);
    }

    public function testSql()
    {
        $query = 'SELECT id FROM accounts WHERE deleted = 0';

        $visibility = new OwnerVisibility($this->account);
        $visibility->addVisibilityFrom($query);
        $visibility->addVisibilityWhere($query);

        $conn = DBManagerFactory::getConnection();
        $data = $conn->executeQuery($query)->fetchAll();

        $this->assertVisibilityApplied($data);
    }

    private function assertVisibilityApplied(array $data)
    {
        $this->assertCount(1, $data);

        $this->assertArraySubset(array(
            'id' => $this->account->id,
        ), array_shift($data));
    }
}
