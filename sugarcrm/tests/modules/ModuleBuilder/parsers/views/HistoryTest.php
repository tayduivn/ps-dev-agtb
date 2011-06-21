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
        //can't use sys_get_temp_dir() because it doesn't work on Windows 7
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

    private function getHistoryDir()
    {
        return dirname($this->_path) . DIRECTORY_SEPARATOR . md5(basename($this->_path));
    }

    public function tearDown()
    {
        @unlink($this->_path);
        @rmdir($this->getHistoryDir());
    }
}