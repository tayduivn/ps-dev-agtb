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

// FILE SUGARCRM flav=ent ONLY
use Sugarcrm\Sugarcrm\Portal\Factory as PortalFactory;

class SugarQueryPortalVisibilityTest extends TestCase
{
    public $bean = null;
    public $vis = null;
    public $query = null;
    public $oldSession = null;

    public function setUp()
    {
        $this->oldSession = $_SESSION;
    }

    public function tearDown()
    {
        if (!empty($this->oldSession)) {
            $_SESSION = $this->oldSession;
        }
    }

    public static function setupBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test we call the proper methods
     */
    public function testVisibilityCall()
    {
        $bean = $this->createPartialMock('SugarBean', array('loadVisibility'));

        $vis = $this->getMockBuilder('SupportPortalVisibility')
            ->setMethods(['addVisibilityQuery'])
            ->setConstructorArgs([$bean])
            ->getMock();
        $bean->expects($this->any())->method('loadVisibility')->will($this->returnValue($vis));
        $bean->module_dir = 'test';
        $query = new SugarQuery();
        $vis->expects($this->once())->method('addVisibilityQuery')->with($query)->will($this->returnValue($query));
        $bean->addVisibilityQuery($query);
        unset($vis);
        unset($bean);
        unset($query);
    }

    /**
     * Test SugarBPM module should be ignored by portal visibility
     *
     * @param string $moduleName
     * @param bool $ignoreVisibility
     * @dataProvider getPortalVisibilityProvider
     */
    public function testSupportPortalVisibility(string $moduleName, bool $ignoreVisibility)
    {
        $portalSession = PortalFactory::getInstance('Session');
        $portalSession->unsetCache();
        $_SESSION['type'] = 'support_portal';

        $bean = $this->createPartialMock($moduleName, array('loadVisibility'));
        $query = new SugarQuery();

        $originalQuery = clone $query;
        $visibility = new SupportPortalVisibility($bean);
        $sugarQuery = $visibility->addVisibilityQuery($query);

        // For SugarBPM module, such as pmse_BpmProcessDefinition, the original query
        // shouldn't be added with visibility (0=1) criteria to the where clause.
        // i.e. the returned $sugarQuery should still be equal to the $originalQuery
        if ($ignoreVisibility) {
            $this->assertEquals($sugarQuery, $originalQuery);
        } else {
            $this->assertNotEquals($sugarQuery, $originalQuery);
        }
    }

    public function getPortalVisibilityProvider(): array
    {
        return [
            ['pmse_BpmProcessDefinition', true],
            ['pmse_Business_Rules', true],
            ['pmse_Emails_Templates', true],
            ['Account', false],
            ['Contact', false],
        ];
    }

    public function testQueryReturnWithAccounts()
    {
        $this->markTestIncomplete('[BR-3907] Testing SQL doesn\'t work with prepared statements');

        $contact = new ContactsPortalVisibilityQueryMock();
        $contact->setVisibility(new SupportPortalVisibilityQueryMock($contact));
        $contact->id = 1;
        $_SESSION['contact_id'] = 1;
        $_SESSION['type'] = 'support_portal';
        $query = new SugarQuery();
        $query->select('*');
        $query->from($contact);
        $contact->addVisibilityQuery($query);

        $this->assertArrayHasKey('accounts_pv', $query->join);
        /** @var SugarQuery_Builder_Condition $condition */
        $condition = $query->join['accounts_pv']->on['and']->conditions[2];
        $this->assertEquals('Accounts', $condition->field->moduleName);
        $this->assertEquals('id', $condition->field->field);
        $this->assertEquals('IN', $condition->operator);
        $this->assertEquals(array(1, 2, 3, 4), $condition->values);

        unset($_SESSION);
        unset($contact);
        unset($query);
    }

    public function testQueryReturnWithoutAccounts()
    {
        $this->markTestIncomplete('Bug in SugarQuery Remove this when fixed: https://sugarcrm.atlassian.net/browse/BR-210');
        $contact = new ContactsPortalVisibilityQueryMock();
        $contact->setVisibility(new SupportPortalVisibility($contact));
        $contact->id = 1;
        $_SESSION['contact_id'] = 1;
        $_SESSION['type'] = 'support_portal';
        $query = new SugarQuery();
        $query->from($contact);
        $contact->addVisibilityQuery($query);

        $queryShouldBe = "SELECT  * FROM contacts WHERE contacts.deleted = 0 AND  ( contacts.id = '1' )";

        $this->assertEquals($queryShouldBe, $query, "The query does not match");
        unset($_SESSION);
        unset($contact);
        unset($query);
    }
}

class ContactsPortalVisibilityQueryMock extends Contact
{
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }
}

class SupportPortalVisibilityQueryMock extends SupportPortalVisibility
{
    public function getAccountIds()
    {
        return self::$accountIds = array('1','2','3','4');
    }
}
