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

require_once 'data/visibility/SupportPortalVisibility.php';

/**
 * @coversDefaultClass \SupportPortalVisibility
 */
class SupportPortalVisibilityTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $_SESSION['type'] = 'support_portal';
    }

    protected function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestNoteUtilities::removeAllCreatedNotes();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        unset($_SESSION['type']);
    }

    /**
     * @dataProvider addVisibilityFromAccountsBasedRuleDataProvider
     * @covers ::addVisibilityFrom
     */
    public function testAccountBasedSql($module, $accountsIds, $expected)
    {
        $bean = $this->setUpAccountBasedFixture($module);
        $visibility = $this->getVisibility($bean, $accountsIds);
        $rows = $this->selectSql($bean, $visibility);
        $this->assertEquals($expected, $rows);
    }

    /**
     * @dataProvider addVisibilityFromAccountsBasedRuleDataProvider
     * @covers ::addVisibilityFromQuery
     */
    public function testAccountBasedSugarQuery($module, $accountsIds, $expected)
    {
        $bean = $this->setUpAccountBasedFixture($module);
        $visibility = $this->getVisibility($bean, $accountsIds);
        $rows = $this->selectSugarQuery($bean, $visibility);
        $this->assertEquals($expected, $rows);
    }

    private function setUpAccountBasedFixture($module)
    {
        $accounts = array();
        $accounts[] = SugarTestAccountUtilities::createAccount('account-1');
        $accounts[] = SugarTestAccountUtilities::createAccount('account-2');
        $testUtilities = "SugarTest{$module}Utilities";
        $testUtilitiesMethod = "create$module";
        $bean = $testUtilities::$testUtilitiesMethod(strtolower($module) . '-1');
        $bean->load_relationship('accounts');
        $bean->accounts->add($accounts[0]);

        return $bean;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function addVisibilityFromAccountsBasedRuleDataProvider()
    {
        return array(
            array('Contact', array('account-1', 'account-2'), array(array('id' => 'contact-1'))),
            array('Contact', array('account-3'), array()),
            array('Case', array('account-1', 'account-2'), array(array('id' => 'case-1'))),
            array('Case', array('account-3'), array()),
            // for Leads there are no special portal rules
            array('Lead', array('account-1', 'account-2'), array(array('id' => 'lead-1')))
        );
    }

    /**
     * @covers ::addVisibilityFrom
     */
    public function testNotesSql()
    {
        $bean = $this->setUpNotesFixture();
        $visibility = $this->getVisibility($bean, array('account-1', 'account-2'));
        $rows = $this->selectSql($bean, $visibility);
        $this->assertEquals(array('id' => 'note-2'), $rows);
    }

    /**
     * @covers ::addVisibilityFromQuery
     */
    public function testNotesSugarQuery()
    {
        $bean = $this->setUpNotesFixture();
        $visibility = $this->getVisibility($bean, array('account-1', 'account-2'));
        $rows = $this->selectSugarQuery($bean, $visibility);
        $this->assertEquals(array('id' => 'note-2'), $rows);
    }

    private function setUpNotesFixture()
    {
        $accounts = array();
        $accounts[] = SugarTestAccountUtilities::createAccount('account-1');
        $accounts[] = SugarTestAccountUtilities::createAccount('account-2');
        $case = SugarTestCaseUtilities::createCase('case-2');
        $case->load_relationship('accounts');
        $case->accounts->add($accounts[0]);
        $bean = SugarTestNoteUtilities::createNote('note-2');
        $bean->load_relationship('cases');
        $bean->cases->add($case);

        return $bean;
    }

    /**
     * @param SugarBean $bean
     * @param array $accountIds
     * @return SupportPortalVisibility|PHPUnit_Framework_MockObject_MockObject
     */
    private function getVisibility(SugarBean $bean, array $accountIds)
    {
        $visibility = $this->getMockBuilder('SupportPortalVisibility')
            ->setConstructorArgs(array($bean))
            ->setMethods(array('getAccountIds'))
            ->getMock();
        $visibility->expects($this->any())
            ->method('getAccountIds')
            ->will($this->returnValue($accountIds));

        return $visibility;
    }

    /**
     * Selects the data using SQL implementation
     *
     * @param SugarBean $bean
     * @param SupportPortalVisibility $visibility
     * @return array
     */
    private function selectSql(SugarBean $bean, SupportPortalVisibility $visibility)
    {
        $query = 'SELECT ' . $bean->table_name . '.id FROM ' . $bean->table_name;
        $visibility->addVisibilityFrom($query);
        $query .= ' WHERE ' . $bean->table_name . '.deleted = 0';

        $result = $bean->db->query($query);
        $rows = array();
        while ($row = $bean->db->fetchRow($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Selects the data using SugarQuery implementation
     *
     * @param SugarBean $bean
     * @param SupportPortalVisibility $visibility
     * @return array
     */
    private function selectSugarQuery(SugarBean $bean, SupportPortalVisibility $visibility)
    {
        $query = new SugarQuery();
        $query->select('id');
        $query->from($bean);
        $visibility->addVisibilityFromQuery($query);
        return $query->execute();
    }
}
