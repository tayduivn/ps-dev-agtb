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

require_once 'tests/SugarTestHelper.php';
require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'modules/Contacts/upgrade/scripts/post/7_RemoveDuplicateAccountsContacts.php';

/**
 * Test for removing duplicate rows from the accounts_contacts table non-destructively.
 */
class RemoveDuplicateAccountsContactsTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $contact_id = "Dupe_Acct_Cont_Contact_Id";
    protected static $account_id = "Dupe_Acct_Cont_Account_Id";
    protected $db;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->db = $GLOBALS['db'];
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $contact_id = self::$contact_id;
        $account_id = self::$account_id;
        $this->db->query("DELETE FROM accounts_contacts WHERE contact_id = '$contact_id' OR account_id = '$account_id'");
        parent::tearDown();
    }

    /**
     * @param array  $def
     * @param string $layout
     * @param string $file
     * @param array  $expectedLayout
     *
     * @dataProvider provider
     *
     * Functional test
     * @group functional
     */
    public function testRun($startingRows, $expectedRows)
    {
        $contact_id = self::$contact_id;
        $account_id = self::$account_id;
        $db = $this->db;
        $upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $values = implode(",", $startingRows);
        $query = "INSERT into accounts_contacts (id, contact_id, account_id, date_modified, primary_account, deleted)"
            . "VALUES $values";

        $db->query($query);

        $script = new SugarUpgradeRemoveDuplicateAccountsContacts($upgradeDriver);
        $script->from_version = "7.1.5";
        $script->db = $db;

        $script->run();

        $results = $db->query("SELECT * FROM accounts_contacts "
                . "WHERE contact_id = '{$contact_id}' OR account_id = '{$account_id}'"
        );
        $count = $db->getRowCount($results);

        $this->assertEquals(count($expectedRows), $count, "Incorrect number of rows returned");

        $i = 0;
        while ($row = $db->fetchRow($results)) {
            $this->assertEquals($expectedRows[$i], $row);
            $i++;
        }
    }

    public function provider()
    {
        $contact_id = self::$contact_id;
        $account_id = self::$account_id;
        return array(
            //Basic use case
            array(
                //Starting rows
                array(
                    "('s1', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                    "('s2', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                ),
                //Expected rows
                array(
                    array(
                        'id' => 's1',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '1',
                        'deleted' => '0',
                    ),
                ),
            ),
            //Deleted use case
            array(
                //Starting rows
                array(
                    "('s1', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                    "('s2', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                    "('s3', '{$contact_id}', '{$account_id}', NULL, 1, 1)",
                ),
                //Expected rows
                array(
                    array(
                        'id' => 's1',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '1',
                        'deleted' => '0',
                    ),
                    array(
                        'id' => 's3',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '1',
                        'deleted' => '1',
                    ),
                ),
            ),
            //Primary Flag use case
            array(
                //Starting rows
                array(
                    "('s1', '{$contact_id}', '{$account_id}', NULL, 0, 0)",
                    "('s2', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                    "('s3', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                ),
                //Expected rows
                array(
                    array(
                        'id' => 's1',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '0',
                        'deleted' => '0',
                    ),
                    array(
                        'id' => 's2',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '1',
                        'deleted' => '0',
                    ),
                ),
            ),
            //Multiple Contacts per account
            array(
                //Starting rows
                array(
                    "('s1', '{$contact_id}', '{$account_id}', NULL, 0, 0)",
                    "('s2', '{$contact_id}_2', '{$account_id}', NULL, 0, 0)",
                    "('s3', '{$contact_id}', '{$account_id}', NULL, 0, 0)",
                ),
                //Expected rows
                array(
                    array(
                        'id' => 's1',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '0',
                        'deleted' => '0',
                    ),
                    array(
                        'id' => 's2',
                        'contact_id' => $contact_id . "_2",
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '0',
                        'deleted' => '0',
                    ),
                ),
            ),
        );
    }
}
