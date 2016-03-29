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

require_once 'modules/EmailTemplates/EmailTemplate.php';

class EmailTemplateTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderTemplateStringVariables
     * @covers EmailTemplate::checkStringHasVariables
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
                'Assert that template string does not contain a dynamic variable'
            ),
            array(
                '<p>Hello $accountName,</p>',
                0,
                'Assert that template string does not contain a dynamic variable because it lacks an underscore'
            ),
            array(
                '<p>Hello $1,000,000,</p>',
                0,
                'Assert that template string does not contain a dynamic variable with dollar value'
            ),
            array(
                '<p>Hello $account_name,</p>',
                1,
                'Assert that template string has $module_field'
            ),
            array(
                '<p>Hello $accountName_field,</p>',
                1,
                'Assert that template string has $moduleName_field'
            ),
            array(
                '<p>Hello $account_fieldName,</p>',
                1,
                'Assert that template string has $moduleName_field'
            ),
            array(
                '<p>Hello $accountName_fieldName,</p>',
                1,
                'Assert that template string has $accountName_fieldName'
            ),
            array(
                '<p>Hello $accountName_fieldName_c,</p>',
                1,
                'Assert that template string has $accountName_fieldName_c'
            ),
            array(
                '<p>Hello $accountName_fieldName_is_something_else,</p>',
                1,
                'Assert that template string has $accountName_fieldName_is_something_else'
            ),
            array(
                '<p>Hello $account_name1,</p>',
                1,
                'Assert that template string has $module_field1 with numbers'
            ),
        );
    }
}
