<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once 'include/SugarObjects/SugarRegistry.php';

class SugarRegistryTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_old_reporting = null;
    private $_old_globals = null;

    public function setUp() 
    {
        $this->_old_reporting = error_reporting(E_ALL);
        $this->_old_globals = $GLOBALS;
        unset($GLOBALS);
    }

    public function tearDown() 
    {
        error_reporting($this->_old_reporting);
        $GLOBALS = $this->_old_globals;
        unset($this->_old_globals);
    }

    public function testGetInstanceReturnsAnInstanceOfSugarRegistry() 
    {
        $this->assertTrue(SugarRegistry::getInstance() instanceOf SugarRegistry,'Returned object is not a SugarRegistry instance');
    }

    public function testGetInstanceReturnsSameObject() 
    {
        $one = SugarRegistry::getInstance();
        $two = SugarRegistry::getInstance();
        $this->assertSame($one, $two);
    }

    public function testParameterPassedToGetInstanceSpecifiesInstanceName() 
    {
        $foo1 = SugarRegistry::getInstance('foo');
        $foo2 = SugarRegistry::getInstance('foo');
        $this->assertSame($foo1, $foo2);

        $bar = SugarRegistry::getInstance('bar');
        $this->assertNotSame($foo1, $bar);
    }

    public function testCanSetAndGetValues() 
    {
        $random = rand(100, 200);
        $r = SugarRegistry::getInstance();
        $r->integer = $random;
        $this->assertEquals($random, $r->integer);
        $this->assertEquals($random, SugarRegistry::getInstance()->integer);
    }

    public function testIssetReturnsTrueFalse() 
    {
        $r = SugarRegistry::getInstance();
        $this->assertFalse(isset($r->foo));
        $this->assertFalse(isset(SugarRegistry::getInstance()->foo));

        $r->foo = 'bar';
        $this->assertTrue(isset($r->foo));
        $this->assertTrue(isset(SugarRegistry::getInstance()->foo));
    }

    public function testUnsetRemovesValueFromRegistry() 
    {
        $r = SugarRegistry::getInstance();
        $r->foo = 'bar';
        unset($r->foo);
        $this->assertFalse(isset($r->foo));
        $this->assertFalse(isset(SugarRegistry::getInstance()->foo));
    }

    public function testReturnsNullOnAnyUnknownValue() 
    {
        $r = SugarRegistry::getInstance();
        $this->assertNull($r->unknown);
        $this->assertNull(SugarRegistry::getInstance()->unknown);
    }

    public function testAddToGlobalsPutsRefsToAllRegistryObjectsInGlobalSpace() 
    {
        $r = SugarRegistry::getInstance();
        $r->foo = 'bar';

        $this->assertFalse(isset($GLOBALS['foo']), 'sanity check');
        $r->addToGlobals();
        $this->assertTrue(isset($GLOBALS['foo']));
    }
}

