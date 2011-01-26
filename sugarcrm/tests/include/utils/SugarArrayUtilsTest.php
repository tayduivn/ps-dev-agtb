<?php 
require_once 'include/utils/array_utils.php';

class SugarArrayUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
	
	public function test_array_merge_values()
	{	
		$array1 = array("a","b","c");
		$array2 = array("x","y","z");
		$expectedResult = array("ax","by","cz");
		
		$this->assertEquals($expectedResult, array_merge_values($array1, $array2));	
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
	
}

