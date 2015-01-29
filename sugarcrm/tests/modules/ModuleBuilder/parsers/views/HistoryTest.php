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

require_once("modules/ModuleBuilder/parsers/views/History.php");

class HistoryTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    private $_path;
    
    /**
     * @var History
     */
    private $_history;

    public function setUp()
    {
        $this->_path = tempnam(sys_get_temp_dir() . 'tmp', 'history');
        $this->_history = new History($this->_path);
    }
    
    public function tearDown()
    {
        // Bug 54466 Clean up all test files that were created
        $dirname = $this->getHistoryDir();
        $files = glob($dirname . '/history*');
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    public function testConstructor()
    {
        $this->assertTrue(is_dir($this->getHistoryDir()), "__constructor() creates unique directory for file history");
    }

    public function testAppendAndRestore()
    {
        $time = $this->_history->append($this->_path);
        $this->assertTrue(file_exists($this->_history->getFileByTimestamp($time)), '->append() creates history file');
        $this->assertEquals($this->_history->restoreByTimestamp( $time ), $time, '->restoreByTimestamp() returns correct timestamp');
    }

    public function testUndoRestore()
    {
        $this->_history->undoRestore();
        $this->assertFalse(file_exists($this->_path), '->undoRestore removes file');
    }

    public function testPositioning()
    {
        $tempFile = tempnam(sys_get_temp_dir() . 'tmp', 'history');
        
        // Pause for a second in between each append for different timestamps
        $el1 = $this->_history->append($tempFile);
        $el2 = $this->_history->append($tempFile);
        $el3 = $this->_history->append($tempFile);

        // Grab our values for testing
        $getFirst = $this->_history->getFirst();
        $getLast  = $this->_history->getLast();
        $getNth1  = $this->_history->getNth(1);
        $getNext  = $this->_history->getNext();
        
        // Assertions
        $this->assertEquals($el3, $getFirst, "$el3 was not the timestamp returned by getFirst() [$getFirst]");
        $this->assertEquals($el1, $getLast, "$el1 was not the timestamp returned by getLast() [$getLast]");
        $this->assertEquals($el2, $getNth1, "$el2 was not the timestamp returned by getNth(1) [$getNth1]");
        $this->assertEquals($el1, $getNext, "$el1 was not the timestamp returned by getNext() [$getNext]");
        
        // Last assertion
        $getNext  = $this->_history->getNext();
        $this->assertFalse($getNext, "Expected getNext() [$getNext] to return false");
        
        // Clean up
        unlink($tempFile);
    }

    private function getHistoryDir()
    {
        return dirname($this->_path);
    }
    
}
