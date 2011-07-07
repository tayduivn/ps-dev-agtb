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
 
require_once 'include/utils/array_utils.php';

class SugarArrayUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
	
	public function test_array_merge_values()
	{	
		$array1 = array("a","b","c");
		$array2 = array("x","y","z");
		$array3 = array(1, 2, 3);
		$array4 = array("a", "b", "c", "d", "e");
		
		$expectedResult12 = array("ax","by","cz");
		$expectedResult13 = array("a1", "b2", "c3");
		$expectedResult14 = false;
		
		
		$this->assertEquals($expectedResult12, array_merge_values($array1, $array2));
		$this->assertEquals($expectedResult13, array_merge_values($array1, $array3));
		$this->assertEquals($expectedResult14, array_merge_values($array1, $array4));
			
	}
	
	
	public function test_array_search_insensitive()
	{
		$arrayLowerCase = array("alpha","bravo","charlie","delta","echo");
		$arrayUpperCase = array("ALPHA", "BRAVO", "CHARLIE", "DELTA", "ECHO");
		$arrayMixed = array("Alpha","Bravo","Charlie", "Delta", "Echo");
		$arrayEmpty = array();
		
		$this->assertTrue(array_search_insensitive("delta", $arrayLowerCase));
		$this->assertTrue(array_search_insensitive("delta", $arrayUpperCase));
		$this->assertTrue(array_search_insensitive("delta", $arrayMixed));
		$this->assertFalse(array_search_insensitive("delta", $arrayEmpty));	
	}
	
	public function test_object_to_array_recursive()
	{
		$simple = new SimpleObejct();
		
		$notSimple = new NotSimpleObject();
		$notSimple->setFoo(new SimpleObejct());
		$notObject = "foo";
		
		$simpleExpected = array('foo'=>'bar', 'b'=>1);
		$notSimpleExpected = array('foo'=>array('foo'=>'bar', 'b'=>1), 'b'=>1);
		$notObjectExpected = 'foo';
		
		$this->assertEquals($simpleExpected, object_to_array_recursive($simple));
		$this->assertEquals($notSimpleExpected, object_to_array_recursive($notSimple));
		$this->assertEquals($notObjectExpected, object_to_array_recursive($notObject));
		
	}
	
	public function test_overide_value_to_string()
	{
		$name = 'name';
		$value_name = 1;
		$value = 4;
		
		$expected = '$name[1] = 4;';
		
		$this->assertEquals($expected, override_value_to_string($name, $value_name, $value));
		
	}
	
	//To do: test eval == true.
	public function test_override_value_to_string_recursive()
	{
		$key_names = array(1, 2, 3, 4, 5);
		global $array_name; 
		$array_name= 'name';
		$value = 'foo';
	
		
		$expectedNoEval = '$name[1][2][3][4][5]='."'".'foo'."';";
		$expectedEval = true;
		
		$this->assertEquals($expectedNoEval, override_value_to_string_recursive($key_names, $array_name, $value));
		global $name;
		
		$array = override_value_to_string_recursive($key_names, $array_name, $value, true);
	} 
	
	
	//array_name is never used in this function...
	public function test_override_recursive_helper()
	{
		$key_names = array(1, 2, 3, 4, 5);
		$array_name = 'name';
		$value = 'foo';
		
		$expected = '$name[1][2][3][4][5]='."'".$value."';";
		
		$this->assertEquals($expected, override_value_to_string_recursive($key_names, $array_name, $value));	
	} 

	//Todo: hit the if statement
	public function test_setDeepArrayValue()
	{
		$arrayActualSimple = array(1=>'a');
		setDeepArrayValue($arrayActualSimple, 1, 'b');
		$arrayExpectedSimple = array(1=>'b');
		
		$this->assertEquals($arrayExpectedSimple, $arrayActualSimple);	
	}	
}

class SimpleObejct
{
	public $foo = 'bar';
	public $b = 1;
}

class NotSimpleObject
{
	public $foo;
	public $b = 1;
	public function setFoo($input)
	{
		$this->foo = $input;
	}
}
