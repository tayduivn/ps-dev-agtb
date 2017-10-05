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
     * @dataProvider dataProviderTemplateStringVariables
     * @covers ::checkStringHasVariables
     */
    public function testCheckStringHasVariables($tplString, $expected, $msg)
    {
        $result = SugarTestReflection::callProtectedMethod(
            'EmailTemplate',
            'checkStringHasVariables',
            array($tplString)
        );
        $this->assertEquals($result, $expected, $msg);
    }

    public function dataProviderTemplateStringVariables()
    {
        return array(
            array(
                '<p>Hello Test,</p>',
                0,
                'Assert that template string does not contain a dynamic variable',
            ),
            array(
                '<p>Hello $accountName,</p>',
                0,
                'Assert that template string does not contain a dynamic variable because it lacks an underscore',
            ),
            array(
                '<p>Hello $1,000,000,</p>',
                0,
                'Assert that template string does not contain a dynamic variable with dollar value',
            ),
            array(
                '<p>Hello $account_name,</p>',
                1,
                'Assert that template string has $module_field',
            ),
            array(
                '<p>Hello $accountName_field,</p>',
                1,
                'Assert that template string has $moduleName_field',
            ),
            array(
                '<p>Hello $account_fieldName,</p>',
                1,
                'Assert that template string has $moduleName_field',
            ),
            array(
                '<p>Hello $accountName_fieldName,</p>',
                1,
                'Assert that template string has $accountName_fieldName',
            ),
            array(
                '<p>Hello $accountName_fieldName_c,</p>',
                1,
                'Assert that template string has $accountName_fieldName_c',
            ),
            array(
                '<p>Hello $accountName_fieldName_is_something_else,</p>',
                1,
                'Assert that template string has $accountName_fieldName_is_something_else',
            ),
            array(
                '<p>Hello $account_name1,</p>',
                1,
                'Assert that template string has $module_field1 with numbers',
            ),
        );
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
