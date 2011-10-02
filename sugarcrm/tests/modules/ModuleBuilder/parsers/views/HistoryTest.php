<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

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
        $other_file = tempnam(sys_get_temp_dir(), 'history');
        
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
        return dirname($this->_path);
    }
    
}