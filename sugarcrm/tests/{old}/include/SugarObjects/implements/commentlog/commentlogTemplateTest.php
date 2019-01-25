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

use PHPUnit\Framework\TestCase;

class commentlogTemplateTest extends TestCase
{
    /**
     * Checks that modules that should have commentlog as a field do so.
     *
     * @param string $module The module we would like to check for whether
     *   commentlog exists.
     * @param bool $hasField True if it should have the field and false
     *   otherwise.
     * @dataProvider hasCommentLogFieldProvider
     */
    public function testCheckModulesHaveCommentLogField(string $module, bool $hasField)
    {
        $bean = BeanFactory::newBean($module);
        if ($hasField) {
            $this->assertArrayHasKey('commentlog', $bean->field_defs);
            $this->assertArrayHasKey('commentlog_link', $bean->field_defs);
        } else {
            $this->assertArrayNotHasKey('commentlog', $bean->field_defs);
            $this->assertArrayNotHasKey('commentlog_link', $bean->field_defs);
        }
    }

    public function hasCommentLogFieldProvider(): array
    {
        return array(
            array('Accounts', true),
            array('Bugs', true),
            array('Calls', true),
            array('Cases', true),
            array('Contacts', true),
            array('Contracts', true),
            array('DataPrivacy', true),
            array('KBArticles', false),
            array('KBContents', true),
            array('KBContentTemplates', false),
            array('KBDocuments', false),
            array('Leads', true),
            array('Meetings', true),
            array('Notes', true),
            array('Opportunities', true),
            array('ProductCategories', false),
            // FIXME: re-enable once commentlog is enabled in Quotes
            // array('Quotes', true),
            //BEGIN SUGARCRM flav=ent ONLY
            array('RevenueLineItems', true),
            //END SUGARCRM flav=ent ONLY
            array('Tasks', true),
        );
    }
}
