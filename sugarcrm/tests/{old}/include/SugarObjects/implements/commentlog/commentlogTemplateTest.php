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
     * Checks if all modules uses basics or default has commentlog as a field
     * @param string $module The module we would like to check for whether commentlog_field exists
     * @dataProvider moduleListProvider
     */
    public function testCheckAllAvailableModuleHasCommentLogField($module)
    {
        $bean = BeanFactory::newBean($module);
        $this->assertArrayHasKey('commentlog', $bean->field_defs);
        $this->assertArrayHasKey('commentlog_link', $bean->field_defs);
    }

    /**
     * Checks if default enabled module has commentlog field in record view
     * @param string $folder The folder name of the $module
     * @param bool $default_enabled Whether commentlog field should be default enabled or not
     * @dataProvider moduleListProvider
     */
    public function testCheckDefaultEnabled(string $folder, bool $default_enabled)
    {
        // Make a file name for testing/including
        $file = 'modules/' . $folder . '/clients/base/views/record/record.php';

        // Assert that the file exists
        $this->assertFileExists($file);

        // Setup some needed vars
        $enabled = false;
        $viewdefs = [];

        // Get the view def
        include $file;

        // Get the assertion value
        foreach ($viewdefs[$folder]['base']['view']['record']['panels'][1]['fields'] as $field) {
            if (is_array($field) && $field['name'] === 'commentlog') {
                $enabled = true;
                break;
            }
        }

        $this->assertSame($enabled, $default_enabled);
    }

    public function moduleListProvider()
    {
        return array(
            array('Accounts', false),
            array('Bugs', true),
            array('Calls', false),
            array('Cases', true),
            array('Contacts', false),
            array('Contracts', false),
            array('DataPrivacy', false),
            array('KBArticles', false),
            array('KBContents', false),
            array('KBContentTemplates', false),
            array('KBDocuments', false),
            array('Leads', false),
            array('Meetings', true),
            array('Notes', false),
            array('Opportunities', true),
            array('ProductCategories', false),
            array('Quotes', false),
            array('RevenueLineItems', false),
            array('Tasks', true),
        );
    }
}
