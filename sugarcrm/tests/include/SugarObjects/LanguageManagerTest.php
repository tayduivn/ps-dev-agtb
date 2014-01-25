<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'include/SugarObjects/LanguageManager.php';
class LanguageManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $testModule = 'do_not_change';
    protected $testLanguage = 'ever';
    
    /**
     * Tests that the language file load order is correct always.
     * 
     * IF THIS TEST FAILS THEN A CHANGE WAS MADE THAT SHOULD NOT HAVE BEEN MADE.
     * 
     * @param int $index The numeric index of this path in the list
     * @param string $path The path to match to this index
     * @dataProvider languageFilePathProvider
     */
    public function testGetModuleLanguageFilePaths($index, $path)
    {
        $list = LanguageManager::getModuleLanguageFilePaths($this->testModule, $this->testLanguage);
        $this->assertArrayHasKey($index, $list);
        $this->assertEquals($path, $list[$index], "PLEASE DO NOT CHANGE THE ORDER OR VALUES OF THE LANGUAGE FILE LOAD LIST");
    }

    public function languageFilePathProvider()
    {
        return array(
            array('index' => 0, 'path' => 'modules/do_not_change/language/ever.lang.php'),
            array('index' => 1, 'path' => 'modules/do_not_change/language/ever.lang.override.php'),
            array('index' => 2, 'path' => 'custom/modules/do_not_change/language/ever.lang.php'),
            array('index' => 3, 'path' => 'custom/modules/do_not_change/Ext/Language/ever.lang.ext.php'),
        );
    }
}
