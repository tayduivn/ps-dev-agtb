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

require_once 'include/vCard.php';

class vCardAccountLinkTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $contactId;
    private $leadId;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $account = SugarTestAccountUtilities::createAccount();
        $account->name = "SDizzle Inc";
        $account->save();
    }
    
    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id = {$this->contactId}");
        $GLOBALS['db']->query("DELETE FROM leads WHERE id  = {$this->leadId}");
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if account is linked with bean when importing from vCard
     */
    public function testImportedVcardAccountLink()
    {
        $filename  = dirname(__FILE__) . "/vcf/SimpleVCard.vcf";
        
        $vcard = new vCard();
        $this->contactId = $vcard->importVCard($filename, 'Contacts');
        $contactRecord = BeanFactory::getBean('Contacts', $this->contactId);
        
        $this->assertFalse(empty($contactRecord->account_id), "Contact should have an account record associated");

        $vcard = new vCard();
        $this->leadId = $vcard->importVCard($filename, 'Leads');
        $leadRecord = BeanFactory::getBean('Leads', $this->leadId);

        $this->assertTrue(empty($leadRecord->account_id), "Lead should not have an account record associated");
    }
}
