<?php

require_once("modules/ModuleBuilder/parsers/views/History.php");

class HistoryTest extends Sugar_PHPUnit_Framework_TestCase
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
        $this->_path = tempnam(dirname(__FILE__), 'history');
        $this->_history = new History($this->_path);
    }

    public function testConstructor()
    {
        $this->assertTrue(is_dir($this->getHistoryDir()), "__constructor() creates unique directory for file history");
    }

    public function testAppendAndRestore()
    {
        $time = $this->_history->append($this->_path);
        $this->assertTrue(file_exists($this->getHistoryDir() . DIRECTORY_SEPARATOR . $time), '->append() creates history file');
        $this->assertEquals($this->_history->restoreByTimestamp( $time ), $time, '->restoreByTimestamp() returns correct timestamp');
    }

    public function testUndoRestore()
    {
        $this->_history->undoRestore();
        $this->assertFalse(file_exists($this->_path), '->undoRestore removes file');
    }

    public function testPositioning()
    {
        $other_file = tempnam(dirname(__FILE__), 'history');
        
        $el1 = $this->_history->append($other_file);
        $el2 = $this->_history->append($other_file);
        $el3 = $this->_history->append($other_file);

        $this->assertEquals($this->_history->getCount(), 3);
        $this->assertEquals($this->_history->getFirst(), $el3);
        $this->assertEquals($this->_history->getLast(), $el1);
        $this->assertEquals($this->_history->getNth(1), $el2);
        $this->assertEquals($this->_history->getNext(), $el1);
        $this->assertFalse($this->_history->getNext());

        unlink($other_file);
    }

    private function getHistoryDir()
    {
        return dirname($this->_path) . DIRECTORY_SEPARATOR . md5(basename($this->_path));
    }

    public function tearDown()
    {
        if(file_exists($this->_path)) unlink($this->_path);
        rmdir_recursive($this->getHistoryDir());
    }
}