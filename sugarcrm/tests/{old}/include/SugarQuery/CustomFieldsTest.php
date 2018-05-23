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

use PHPUnit\Framework\TestCase;

class CustomFieldsTest extends TestCase
{
    /**
     * @var Account[]
     */
    private static $accounts;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');

        SugarTestHelper::setUpCustomField('Accounts', [
            'name' => 'custom_name_c',
            'type' => 'varchar',
        ]);

        SugarTestHelper::setUpCustomField('Accounts', [
            'name' => 'custom_relate_name_c',
            'type' => 'relate',
            'ext2' => 'Accounts',
            'link' => 'member_of',
        ]);

        $account1 = SugarTestAccountUtilities::createAccount(null, [
            'name' => 'Account #1',
            'custom_name_c' => 'Custom Account #1',
        ]);
        $account2 = SugarTestAccountUtilities::createAccount(null, [
            'name' => 'Account #2',
            'custom_name_c' => 'Custom Account #2',
        ]);

        $account1->account_id_c = $account2->id;
        $account1->save();

        $account2->account_id_c = $account1->id;
        $account2->save();

        self::$accounts = [
            'Account #1' => $account1,
            'Account #2' => $account2,
        ];
    }

    public static function tearDownAfterClass()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * @test
     */
    public function selectCustomField()
    {
        $query = $this->createQuery();
        $query->select('custom_name_c');

        $this->assertSame([
            self::$accounts['Account #1']->id => [
                'custom_name_c' => 'Custom Account #1',
            ],
            self::$accounts['Account #2']->id => [
                'custom_name_c' => 'Custom Account #2',
            ],
        ], $this->fetchAll($query));
    }

    /**
     * @test
     */
    public function selectCustomRelateField()
    {
        global $current_user;

        $query = $this->createQuery();
        $query->select('custom_relate_name_c');

        $this->assertSame([
            self::$accounts['Account #1']->id => [
                'custom_relate_name_c' => 'Account #2',
                'custom_relate_name_c_owner' => $current_user->id,
            ],
            self::$accounts['Account #2']->id => [
                'custom_relate_name_c' => 'Account #1',
                'custom_relate_name_c_owner' => $current_user->id,
            ],
        ], $this->fetchAll($query));
    }

    /**
     * @test
     */
    public function filterByCustomField()
    {
        $query = $this->createQuery();
        $query->where()
            ->equals('custom_name_c', 'Custom Account #1');

        $this->assertSame([
            self::$accounts['Account #1']->id,
        ], array_keys($this->fetchAll($query)));
    }

    /**
     * @test
     */
    public function orderByCustomField()
    {
        $query = $this->createQuery();
        $query->orderBy('custom_name_c', 'DESC');

        $this->assertSame([
            self::$accounts['Account #2']->id,
            self::$accounts['Account #1']->id,
        ], array_keys($this->fetchAll($query)));
    }

    private function createQuery() : SugarQuery
    {
        global $current_user;

        $query = new SugarQuery();
        $query->from(BeanFactory::newBean('Accounts'));
        $query->select('id');
        $query->where()->equals('created_by', $current_user->id);

        return $query;
    }

    private function fetchAll(SugarQuery $query) : array
    {
        $data = [];

        foreach ($query->execute() as $row) {
            $id = $row['id'];
            unset($row['id']);

            $data[$id] = $row;
        }

        return $data;
    }
}
