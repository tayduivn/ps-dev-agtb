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
    private $bean;
    private $manager;

    public function setUp()
    {
        $this->bean = \SugarTestCaseUtilities::createCase();
        $this->manager = \MetaDataManager::getManager();
    }

    public function tearDown()
    {
        \SugarTestCaseUtilities::removeAllCreatedCases();
        unset($this->bean);
        \MetaDataManager::resetManagers();
    }

    public function checkModuleHasFieldsProvider(): array
    {
        return [
            ['Bugs', 'follow_up_datetime', true],
            ['Bugs', 'resolved_datetime', true],
            ['Bugs', 'time_to_resolution', true],
            ['Cases', 'follow_up_datetime', true],
            ['Cases', 'resolved_datetime', true],
            ['Cases', 'time_to_resolution', true],
            ['DataPrivacy', 'follow_up_datetime', true],
            ['DataPrivacy', 'resolved_datetime', true],
            ['DataPrivacy', 'time_to_resolution', true],
            ['Accounts', 'follow_up_datetime', false],
            ['Accounts', 'resolved_datetime', false],
            ['Accounts', 'time_to_resolution', false],
            ['Contacts', 'follow_up_datetime', false],
            ['Contacts', 'resolved_datetime', false],
            ['Contacts', 'time_to_resolution', false],
        ];
    }

    /**
     * Checks that modules should have certain issue type fields.
     *
     * @param string $module The module we would like to check fields
     * @param string $field The field to be checked
     * @param bool $hasField Whether the module should have the field being checked
     * @dataProvider checkModuleHasFieldsProvider
     */
    public function testCheckModuleHasFields(string $module, string $field, bool $hasField)
    {
        $field_defs = \BeanFactory::newBean($module)->field_defs;
        $this->assertSame($hasField, array_key_exists($field, $field_defs));
    }

    /**
     * Checks that modules that should have follow_up_datetime on the given
     * view do so.
     *
     * @param string $module The module for which we would like to check that
     *   follow_up_datetime is on the given view.
     * @param string $view The view to check.
     * @dataProvider hasFollowUpDateFieldOnViewProvider
     */
    public function testCheckModuleHasFollowUpDateFieldOnView(string $module, string $view)
    {
        $this->assertContains('follow_up_datetime', $this->manager->getModuleViewFields($module, $view));
    }

    public function hasFollowUpDateFieldOnViewProvider(): array
    {
        return [
            ['Bugs', 'record'],
            ['Cases', 'list'],
            ['Cases', 'record'],
            ['DataPrivacy', 'record'],
        ];
    }

    /**
     * Checks that modules that should have time_to_resolution on the record
     * view do so.
     *
     * @param string $module The module for which we would like to check that
     *   time_to_resolution is on the record view.
     * @dataProvider hasTimeToResolutionFieldOnRecordViewProvider
     */
    public function testCheckModuleHasTimeToResolutionFieldOnRecordView(string $module)
    {
        $this->assertContains('time_to_resolution', $this->manager->getModuleViewFields($module, 'record'));
    }

    public function hasTimeToResolutionFieldOnRecordViewProvider(): array
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
    public function testCheckModulesHasNoFollowUpDateFieldOnView(string $module)
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

    /**
     * Checks that modules that should not have time_to_resolution on the
     * record view do not.
     *
     * @param string $module The module we would like to verify does not
     *   have time_to_resolution on its record view.
     * @dataProvider hasNoTimeToResolutionFieldOnRecordViewProvider
     */
    public function testCheckModulesHasNoTimeToResolutionFieldOnRecordView(string $module)
    {
        $this->assertNotContains('time_to_resolution', $this->manager->getModuleViewFields($module, 'record'));
    }

    public function hasNoTimeToResolutionFieldOnRecordViewProvider(): array
    {
        return [
            ['Accounts'], // not an issue type module
            ['Contacts'], // not an issue type module
        ];
    }

    /**
     * Ensure that calling save on a newly resolved Issue calculates its
     * resolution time.
     * @dataProvider providerSaveUpdatesResolutionTime
     */
    public function testSaveUpdatesResolutionTime(string $entered, string $resolved, int $expected)
    {
        $this->bean->fetched_row = array('status' => 'New');
        $this->bean->status = 'Rejected';
        $this->bean->date_entered = $entered;
        $this->bean->resolved_datetime = $resolved;
        unset($this->bean->time_to_resolution);

        $this->bean->save();

        $this->assertEquals($expected, $this->bean->time_to_resolution);
    }

    public function providerSaveUpdatesResolutionTime(): array
    {
        return [
            ['2019-05-05 5:50:00', '2019-05-05 5:55:00', 5],
        ];
    }
}
