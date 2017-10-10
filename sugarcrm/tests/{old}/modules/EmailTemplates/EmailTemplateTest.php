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
 * @coversDefaultClass EmailTemplate
 */
class EmailTemplateTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        parent::tearDown();
    }

    /**
     * @coversNothing
     */
    public function testSystemTemplates_ProperConfiguration_ExistWithProperTypes()
    {
        $this->assertNotEmpty(
            $GLOBALS['sugar_config']['passwordsetting']['lostpasswordtmpl'],
            'lostpasswordtmpl id not set in config'
        );

        $this->assertNotEmpty(
            $GLOBALS['sugar_config']['passwordsetting']['generatepasswordtmpl'],
            'generatepasswordtmpl id not set in config'
        );

        $lostPasswordTemplateId = $GLOBALS['sugar_config']['passwordsetting']['lostpasswordtmpl'];
        $generatePasswordTemplateId = $GLOBALS['sugar_config']['passwordsetting']['generatepasswordtmpl'];

        $lostPasswordTemplateType =
            $GLOBALS['db']->getOne("SELECT type FROM email_templates WHERE id='$lostPasswordTemplateId'");
        $this->assertEquals('system', $lostPasswordTemplateType, "Lost Password Template Type not 'system'");

        $generatePasswordTemplateType =
            $GLOBALS['db']->getOne("SELECT type FROM email_templates WHERE id='$generatePasswordTemplateId'");
        $this->assertEquals('system', $generatePasswordTemplateType, "Generate Password Template Type not 'system'");
    }

    /**
     * @covers ::parse_template_bean
     * @covers ::add_replacement
     * @covers ::convertToType
     * @covers ::parseUserValues
     */
    public function testParseTemplateBean_BeanIsContact()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $contact = SugarTestContactUtilities::createContact('', [
            'account_id' => $account->id,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            // Need to set the name because we're pretending that all data has been read from the database.
            'account_name' => $account->name,
        ]);

        $template = 'Welcome $contact_first_name from $contact_account_name, ' .
            'I am your account manager $contact_user_name.';
        $expected = "Welcome {$contact->first_name} from {$account->name}, " .
            "I am your account manager {$GLOBALS['current_user']->name}.";
        $actual = EmailTemplate::parse_template_bean($template, 'Contacts', $contact);

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::parse_template_bean
     * @covers ::add_replacement
     * @covers ::convertToType
     * @covers ::parseUserValues
     */
    public function testParseTemplateBean_BeanIsUser()
    {
        $template = 'Welcome $contact_user_first_name $contact_user_last_name, ' .
            'Your username is $contact_user_user_name.';
        $expected = "Welcome {$GLOBALS['current_user']->first_name} {$GLOBALS['current_user']->last_name}, " .
            "Your username is {$GLOBALS['current_user']->user_name}.";
        $actual = EmailTemplate::parse_template_bean($template, 'Users', $GLOBALS['current_user']);

        $this->assertSame($expected, $actual);
    }
}
