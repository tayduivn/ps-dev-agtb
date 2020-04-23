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

class Bug57571Test extends TestCase
{
    private $module = 'Quotes';
    private $backedUpDefs = false;
    private $field;
    private $panel;
    private $testFile = 'custom/modules/Quotes/metadata/editviewdefs.php';

    protected function setUp() : void
    {
        // Setup our environment
        SugarTestHelper::init();
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', [$this->module]);
        
        // Backup the custom metadata for quotes if there is one
        if (file_exists($this->testFile)) {
            rename($this->testFile, $this->testFile . '.backup');
            $this->backedUpDefs = true;
        }
        
        // Write it out
        $this->addTabIndex();
    }
    
    protected function tearDown() : void
    {
        // Get rid of the custom file we created
        unlink($this->testFile);
        
        // Restore if necessary
        if ($this->backedUpDefs) {
            rename($this->testFile . '.backup', $this->testFile);
        }
        
        SugarTestHelper::tearDown();
    }
    
    public function testTabIndexFoundInViewDefs()
    {
        $parser = new GridLayoutMetaDataParser(MB_EDITVIEW, $this->module);
        $defs = $parser->getLayout();
        $this->assertNotEmpty($defs[$this->panel], "No panel named $this->panel found in the modified defs");
        $panel = $defs[$this->panel];
        
        $col = $this->getColFromPanel($panel);
        $this->assertNotEmpty($col, "No column found with the correct field name for testing");
        $this->assertTrue(isset($col['tabindex']), "Tab index was not properly fetched for this test");
        $this->assertEquals($col['tabindex'], '1', 'Tab Index was not set to 1 as expected');
    }
    
    private function addTabIndex()
    {
        require 'modules/Quotes/metadata/editviewdefs.php';
        foreach ($viewdefs['Quotes']['EditView']['panels'] as $panelname => $paneldef) {
            foreach ($paneldef as $index => $row) {
                foreach ($row as $id => $value) {
                    if (is_string($value)) {
                        // Save the field name & panel
                        $this->panel = $panelname;
                        $this->field = $value;
                        
                        // Rewrite the def
                        $viewdefs['Quotes']['EditView']['panels'][$panelname][$index][$id] = [
                            'name' => $value,
                            'tabindex' => '1',
                        ];
                        
                        break 3;
                    }
                }
            }
        }
        
        mkdir_recursive(dirname($this->testFile));
        write_array_to_file('viewdefs', $viewdefs, $this->testFile);
    }
    
    private function getColFromPanel($panel)
    {
        foreach ($panel as $row) {
            foreach ($row as $col) {
                if (isset($col['name']) && $col['name'] == $this->field) {
                    return $col;
                }
            }
        }
        
        return [];
    }
}
