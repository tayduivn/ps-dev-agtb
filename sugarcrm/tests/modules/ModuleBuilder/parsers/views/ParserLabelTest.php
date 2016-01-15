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

require_once 'modules/ModuleBuilder/parsers/parser.label.php';

/**
 * @covers ParserLabel
 */
class ParserLabelTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
    }

    protected function tearDown()
    {
        global $current_language;

        SugarTestHelper::tearDown();
        LanguageManager::clearLanguageCache(null, $current_language);
        parent::tearDown();
    }

    /**
     * @dataProvider updateModuleListsProvider
     */
    public function testUpdateModuleLists($module, $labelName, $label, $listName)
    {
        global $current_language;

        SugarTestHelper::saveFile(array(
            'custom/include/language/' . $current_language . '.lang.php',
            'custom/Extension/modules/' . $module . '/Ext/Language/' . $current_language . '.lang.php'
        ));

        $strings = return_app_list_strings_language($current_language);
        $this->assertNotEquals($label, $strings[$listName][$module]);

        $parser = new ParserLabel($module);
        $parser->handleSave(array(
            'label_' . $labelName => $label,
        ), $current_language);

        $strings = return_app_list_strings_language($current_language);
        $this->assertEquals($label, $strings[$listName][$module]);
    }

    public static function updateModuleListsProvider()
    {
        return array(
            'plural' => array(
                'Accounts',
                'LBL_MODULE_NAME',
                'Companies',
                'moduleList',
            ),
            'singular' => array(
                'Accounts',
                'LBL_MODULE_NAME_SINGULAR',
                'Company',
                'moduleListSingular',
            ),
        );
    }
}
