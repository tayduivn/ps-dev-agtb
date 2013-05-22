<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'include/SugarQuery/SugarQuery.php';
require_once 'data/SugarVisibility.php';
require_once 'data/visibility/SupportPortalVisibility.php';

class SugarQueryPortalVisibilityTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $bean = null;
    public $vis = null;
    public $query = null;

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
        $bean = $this->getMock('SugarBean', array('loadVisibility'));
        $vis = $this->getMock('SupportPortalVisibility', array('addVisibilityFromQuery', 'addVisibilityWhereQuery'), array($bean));
        $bean->expects($this->any())->method('loadVisibility')->will($this->returnValue($vis));
        $bean->module_dir = 'test';        
        $query = new SugarQuery();
        $vis->expects($this->once())->method('addVisibilityFromQuery')->with($query)->will($this->returnValue($query));
        $vis->expects($this->once())->method('addVisibilityWhereQuery')->with($query)->will($this->returnValue($query));
        $bean->addVisibilityQuery($query);
        unset($vis);
        unset($bean);
        unset($query);        
    }

    public function testQueryReturnWithAccounts()
    {
        $contact = new ContactsPortalVisibilityQueryMock();
        $contact->setVisibility(new SupportPortalVisibilityQueryMock($contact));
        $contact->id = 1;
        $_SESSION['contact_id'] = 1;
        $_SESSION['type'] = 'support_portal';
        $query = new SugarQuery();
        $query->from($contact);
        $contact->addVisibilityQuery($query);
        $queryShouldBe = "SELECT  * FROM contacts INNER JOIN  accounts_contacts ON contacts.id=accounts_contacts.contact_id AND accounts_contacts.deleted=0

 INNER JOIN  accounts accounts_pv ON accounts_pv.id=accounts_contacts.account_id AND accounts_pv.deleted=0
 AND accounts_pv.id IN ('1','2','3','4')  WHERE contacts.deleted = 0";
        
        $this->assertEquals($queryShouldBe, $query->compileSql(), "The query does not match");
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
        
        $this->assertEquals($queryShouldBe, $query->compileSql(), "The query does not match");
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
        return $this->accountIds = array('1','2','3','4');
    }
}
