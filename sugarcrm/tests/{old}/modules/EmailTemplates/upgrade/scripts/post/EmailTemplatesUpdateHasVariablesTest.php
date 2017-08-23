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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'modules/EmailTemplates/upgrade/scripts/post/2_EmailTemplatesUpdateHasVariables.php';

/**
 * Class EmailTemplatesUpdateHasVariablesTest test for SugarUpgradeEmailTemplatesUpdateHasVariables upgrade script
 */
class EmailTemplatesUpdateHasVariablesTest extends UpgradeTestCase
{
    protected $templateIds = array();

    protected $db;

    protected function setUp()
    {
        parent::setUp();
        $this->db = DBManagerFactory::getInstance();
    }

    protected function tearDown()
    {
        SugarTestEmailTemplateUtilities::removeAllCreatedEmailTemplates();
        parent::tearDown();
    }

    /**
     * @dataProvider dataProviderTemplateStringVariables
     * @covers EmailTemplate::checkStringHasVariables
     */
    public function testCheckStringHasVariables($subject, $bodyStr, $bodyHtmlStr, $expected, $msg)
    {
        $emailTpl = SugarTestEmailTemplateUtilities::createEmailTemplate('', array(
            'subject' => $subject,
            'body' => $bodyStr,
            'body_html' => $bodyHtmlStr,
        ));

        // Set date_modified to some date in the past so we can verify that date_modified is not changed.
        $emailTpl->setModifiedDate(TimeDate::getInstance()->asDb(new SugarDateTime('2016-01-01')));
        $emailTpl->update_date_modified = false;
        $emailTpl->save();
        $emailTpl->update_date_modified = true;

        // Retrieve the template so that the fields are converted. We want to compare apples to apples, when comparing
        // the date modified timestamps, and we need the date_modified field formatted the same as it will be after
        // retrieving the template post-upgrade.
        $emailTpl->retrieve();
        $originalDateModified = $emailTpl->date_modified;

        $this->db->query('UPDATE email_templates SET has_variables = 0');

        $script = $this->upgrader->getScript('post', '2_EmailTemplatesUpdateHasVariables');
        $script->db = $this->db;
        $script->from_version = '7.9.0.0';
        $script->run();

        $emailTpl->retrieve();
        $this->assertEquals($expected, $emailTpl->has_variables, $msg);
        $this->assertSame($originalDateModified, $emailTpl->date_modified, 'The date modified timestamp changed');
    }

    public function dataProviderTemplateStringVariables()
    {
        return array(
            array(
                '',
                '',
                '<p>Hello Test,</p>',
                0,
                'Assert that template string does not contain a dynamic variable',
            ),
            array(
                '',
                '',
                '<p>Hello $accountName,</p>',
                0,
                'Assert that template string does not contain a dynamic variable because it lacks an underscore',
            ),
            array(
                '',
                '',
                '<p>Hello $1,000,000,</p>',
                0,
                'Assert that template string does not contain a dynamic variable with dollar value',
            ),
            array(
                '',
                '',
                '<p>Hello $account_name,</p>',
                1,
                'Assert that template string has $module_field',
            ),
            array(
                '',
                '',
                '<p>Hello $accountName_field,</p>',
                1,
                'Assert that template string has $moduleName_field',
            ),
            array(
                '',
                '',
                '<p>Hello $account_fieldName,</p>',
                1,
                'Assert that template string has $moduleName_field',
            ),
            array(
                '',
                '',
                '<p>Hello $accountName_fieldName,</p>',
                1,
                'Assert that template string has $accountName_fieldName',
            ),
            array(
                '',
                '',
                '<p>Hello $accountName_fieldName_c,</p>',
                1,
                'Assert that template string has $accountName_fieldName_c',
            ),
            array(
                '',
                '',
                '<p>Hello $accountName_fieldName_is_something_else,</p>',
                1,
                'Assert that template string has $accountName_fieldName_is_something_else',
            ),
            array(
                '',
                '',
                '<p>Hello $account_name1,</p>',
                1,
                'Assert that template string has $module_field1 with numbers',
            ),
            array(
                '',
                'Hello Test,',
                '',
                0,
                'Assert that template string does not contain a dynamic variable',
            ),
            array(
                '',
                'Hello $accountName,',
                '',
                0,
                'Assert that template string does not contain a dynamic variable because it lacks an underscore',
            ),
            array(
                '',
                'Hello $1,000,000,',
                '',
                0,
                'Assert that template string does not contain a dynamic variable with dollar value',
            ),
            array(
                'Win $1,000,000',
                '',
                '',
                0,
                'Assert that template subject does not contain a dynamic variable with dollar value',
            ),
            array(
                '',
                'Hello $account_name,',
                '',
                1,
                'Assert that template string has $module_field',
            ),
            array(
                '',
                'Hello $accountName_field,',
                '',
                1,
                'Assert that template string has $moduleName_field',
            ),
            array(
                '',
                'Hello $account_fieldName,',
                '',
                1,
                'Assert that template string has $moduleName_field',
            ),
            array(
                '',
                'Hello $accountName_fieldName,',
                '',
                1,
                'Assert that template string has $accountName_fieldName',
            ),
            array(
                '',
                'Hello $accountName_fieldName_c,',
                '',
                1,
                'Assert that template string has $accountName_fieldName_c',
            ),
            array(
                '',
                'Hello $accountName_fieldName_is_something_else,',
                '',
                1,
                'Assert that template string has $accountName_fieldName_is_something_else',
            ),
            array(
                '',
                'Hello $account_name1,',
                '',
                1,
                'Assert that template string has $module_field1 with numbers',
            ),
            array(
                'Hello $accountName_fieldName_c',
                '',
                '',
                1,
                'Assert that template subject has $accountName_fieldName_c',
            ),
            array(
                'Hello $accountName_fieldName_is_something_else,',
                '',
                '',
                1,
                'Assert that template subject has $accountName_fieldName_is_something_else',
            ),
            array(
                'Hello $account_name1',
                '',
                '',
                1,
                'Assert that template subject has $module_field1 with numbers',
            ),
        );
    }
}
