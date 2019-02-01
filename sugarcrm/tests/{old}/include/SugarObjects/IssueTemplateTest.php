<?php
//FILE SUGARCRM flav=ent ONLY
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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarObjects\Templates\Issue;

use PHPUnit\Framework\TestCase;

/**
 * Class IssueTemplateTest
 * @package Sugarcrm\SugarcrmTestsUnit\inc\SugarObjects\Templates\Issue
 */
class IssueTemplateTest extends TestCase
{
    public function setUp()
    {
        $this->manager = \MetaDataManager::getManager();
    }

    public function tearDown()
    {
        \MetaDataManager::resetManagers();
    }

    public function checkModuleHasFieldsProvider(): array
    {
        return [
            ['Bugs', 'follow_up_datetime', true],
            ['Bugs', 'resolved_datetime', true],
            ['Cases', 'follow_up_datetime', true],
            ['Cases', 'resolved_datetime', true],
            ['DataPrivacy', 'follow_up_datetime', true],
            ['DataPrivacy', 'resolved_datetime', true],
            ['Accounts', 'follow_up_datetime', false],
            ['Accounts', 'resolved_datetime', false],
            ['Contacts', 'follow_up_datetime', false],
            ['Contacts', 'resolved_datetime', false],
        ];
    }

    /**
     * Checks that modules should have certain issue type fields.
     *
     * @param string $module The module we would like to check fields
     * @param string $field The field to be checked
     * @param bool $hasField Whether the module should has the field beening checked
     * @dataProvider checkModuleHasFieldsProvider
     */
    public function testCheckModuleHasFields(string $module, string $field, bool $hasField)
    {
        $field_defs = \BeanFactory::newBean($module)->field_defs;
        $this->assertSame($hasField, array_key_exists($field, $field_defs));
    }

    /**
     * Checks that modules that should have follow_up_datetime on the record
     * view do so.
     *
     * @param string $module The module for which we would like to check that
     *   follow_up_datetime is on the record view.
     * @dataProvider hasFollowUpDateFieldOnRecordViewProvider
     */
    public function testCheckModuleHasFollowUpDateFieldOnRecordView(string $module)
    {
        $this->assertContains('follow_up_datetime', $this->manager->getModuleViewFields($module, 'record'));
    }


    public function hasFollowUpDateFieldOnRecordViewProvider(): array
    {
        return [
            ['Bugs'],
            ['Cases'],
            ['DataPrivacy'],
        ];
    }

    /**
     * Checks that modules that should not have follow_up_datetime on the
     * record view do not.
     *
     * @param string $module The module we would like to verify does not
     *   have follow_up_datetime on its record view.
     * @dataProvider hasNoFollowUpDateFieldOnRecordViewProvider
     */
    public function testCheckModulesHasNoFollowUpDateFieldOnRecordView(string $module)
    {
        $this->assertNotContains('follow_up_datetime', $this->manager->getModuleViewFields($module, 'record'));
    }

    public function hasNoFollowUpDateFieldOnRecordViewProvider(): array
    {
        return [
            ['Accounts'], // not an issue type module
            ['Contacts'], // not an issue type module
        ];
    }
}
