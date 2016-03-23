<?php
require_once 'data/visibility/SupportPortalVisibility.php';

/**
 * @coversDefaultClass \SupportPortalVisibility
 */
class SupportPortalVisibilityTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @codeCoverageIgnore
     */
    protected function tearDown()
    {
        unset($_SESSION['type']);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestNoteUtilities::removeAllCreatedNotes();
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }

    /**
     * @codeCoverageIgnore
     */
    public function addVisibilityFromAccountsBasedRuleDataProvider()
    {
        return array(
            array(array(array('id' => 'contact-1')), 'Contact', array('account-1', 'account-2')),
            array(array(), 'Contact', array('account-3')),
            array(array(array('id' => 'case-1')), 'Case', array('account-1', 'account-2')),
            array(array(), 'Case', array('account-3')),
            // for Leads there are no special portal rules
            array(array(array('id' => 'lead-1')), 'Lead', array('account-1', 'account-2'))
        );
    }

    /**
     * @dataProvider addVisibilityFromAccountsBasedRuleDataProvider
     * @covers ::addVisibilityFromQuery
     */
    public function testAddVisibilityFromAccountsBasedRule($expected, $module, $accountsIds)
    {
        $accounts = array();
        $accounts[] = SugarTestAccountUtilities::createAccount('account-1');
        $accounts[] = SugarTestAccountUtilities::createAccount('account-2');
        $testUtilities = "SugarTest{$module}Utilities";
        $testUtilitiesMethod = "create$module";
        $bean = $testUtilities::$testUtilitiesMethod(strtolower($module) . '-1');
        $bean->load_relationship('accounts');
        $bean->accounts->add($accounts[0]);

        $_SESSION['type'] = 'support_portal';
        $visibility = $this->getMockBuilder('SupportPortalVisibility')
            ->setConstructorArgs(array($bean))
            ->setMethods(array('getAccountIds'))
            ->getMock();
        $visibility->expects($this->exactly(2))
            ->method('getAccountIds')
            ->will($this->returnValue($accountsIds));

        $query = 'SELECT ' . $bean->table_name . '.id FROM ' . $bean->table_name;
        $visibility->addVisibilityFrom($query);
        $query .= ' WHERE ' . $bean->table_name . '.deleted = 0';
        $rowsBC = $this->getQueryResults($query);

        $sugarQuery = new SugarQuery();
        $sugarQuery->select('id');
        $sugarQuery->from($bean);
        $visibility->addVisibilityFromQuery($sugarQuery);
        $rows = $this->getQueryResults($sugarQuery->compileSql());

        $this->assertEquals($expected, $rowsBC, 'Both old and new `addVisibilityFromQuery` methods work the same way');
        $this->assertEquals($expected, $rows, 'Both old and new `addVisibilityFromQuery` methods work the same way');
    }

    /**
     * @covers ::addVisibilityFromQuery
     */
    public function testAddVisibilityFromNotes()
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

        $_SESSION['type'] = 'support_portal';
        $visibility = $this->getMockBuilder('SupportPortalVisibility')
            ->setConstructorArgs(array($bean))
            ->setMethods(array('getAccountIds'))
            ->getMock();
        $visibility->expects($this->exactly(2))
            ->method('getAccountIds')
            ->will($this->returnValue(array('account-1', 'account-2')));

        $query = 'SELECT ' . $bean->table_name . '.id FROM ' . $bean->table_name;
        $visibility->addVisibilityFrom($query);
        $rowsBC = $this->getQueryResults($query);

        $sugarQuery = new SugarQuery();
        $sugarQuery->select('id');
        $sugarQuery->from($bean);
        $visibility->addVisibilityFromQuery($sugarQuery);
        $rows = $this->getQueryResults($sugarQuery->compileSql());

        $expected = array(array('id' => 'note-2'));
        $this->assertEquals($expected, $rowsBC, 'Both old and new `addVisibilityFromQuery` methods work the same way');
        $this->assertEquals($expected, $rows, 'Both old and new `addVisibilityFromQuery` methods work the same way');
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $query
     *
     * @return array
     */
    protected function getQueryResults($query)
    {
        $result = $GLOBALS['db']->query($query);
        $rows = array();
        while ($row = $GLOBALS['db']->fetchRow($result)) {
            $rows[] = $row;
        }
        sort($rows);
        return $rows;
    }
}
