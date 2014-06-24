<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'include/SugarObjects/templates/person/Person.php';

class PersonTemplateTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_bean;
    private $_user;

    public function setUp()
    {
        // Can't use Person since Localization needs actual bean
        $this->_bean = BeanFactory::getBean('Contacts');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('files');
    }

    public function tearDown()
    {
        unset($this->_bean);
        SugarTestHelper::tearDown();
    }

    public function testNameIsReturnedAsSummaryText()
    {
        $GLOBALS['current_user']->setPreference('default_locale_name_format', 'l f');

        $this->_bean->first_name = 'Test';
        $this->_bean->last_name = 'Contact';
        $this->_bean->title = '';
        $this->_bean->salutation = '';
        $this->assertEquals('Contact Test', $this->_bean->get_summary_text());
    }

    /**
     * @ticket 38648
     */
    public function testNameIsReturnedAsSummaryTextWhenSalutationIsInvalid()
    {
        $GLOBALS['current_user']->setPreference('default_locale_name_format', 's l f');

        $this->_bean->salutation = 'Tester';
        $this->_bean->first_name = 'Test';
        $this->_bean->last_name = 'Contact';
        $this->_bean->title = '';
        $this->assertEquals('Tester Contact Test', $this->_bean->get_summary_text());
    }

    public function testCustomPersonTemplateFound()
    {
        // write out a custom Person File
        mkdir_recursive("custom/include/SugarObjects/templates/person/");
        SugarTestHelper::saveFile("custom/include/SugarObjects/templates/person/vardefs.php");
        SugarAutoLoader::put("custom/include/SugarObjects/templates/person/vardefs.php", file_get_contents("tests/include/SugarObjects/templates/test-vardefs/person-vardef.php"));
        VardefManager::addTemplate('Contacts', 'Contact', 'person', false);
        $this->assertArrayHasKey('customField', $GLOBALS['dictionary']['Contact']['fields']);

    }
}
