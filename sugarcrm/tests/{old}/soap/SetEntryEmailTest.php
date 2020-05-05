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
/**
 * @ticket 22504
 */
class SetEntryEmailTest extends SOAPTestCase
{
    /**
     * Create test account
     */
    protected function setUp() : void
    {
        $this->acc = SugarTestAccountUtilities::createAccount();
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v3_1/soap.php';
        parent::setUp();
    }

    protected function tearDown() : void
    {
        if (!empty($this->email_id)) {
            $GLOBALS['db']->query("DELETE FROM emails WHERE id='{$this->email_id}'");
            $GLOBALS['db']->query("DELETE FROM emails_beans WHERE email_id='{$this->email_id}'");
            $GLOBALS['db']->query("DELETE FROM emails_text WHERE email_id='{$this->email_id}'");
            $GLOBALS['db']->query("DELETE FROM emails_email_addr_rel WHERE email_id='{$this->email_id}'");
        }
        parent::tearDown();
    }

    public function testEmailImport()
    {
        $this->_login();
        $nv = [
            'from_addr' => 'test@test.com',
            'parent_type' => 'Accounts',
            'parent_id' => $this->acc->id,
            'description' => 'test',
            'name' => 'Test Subject',
        ];
        $result = $this->_soapClient->call('set_entry', ['session'=>$this->_sessionId,"module_name" => 'Emails', 'name_value_list' => $nv]);
        $this->email_id = $result['id'];
        $email = new Email();
        $email->retrieve($this->email_id);
        $email->load_relationship('accounts');
        $acc = $email->accounts->get();
        $this->assertEquals($this->acc->id, $acc[0]);
    }
}
