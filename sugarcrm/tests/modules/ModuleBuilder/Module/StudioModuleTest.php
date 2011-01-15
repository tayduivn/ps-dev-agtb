<?php

require_once("modules/ModuleBuilder/Module/StudioModule.php");

class StudioModuleTest extends Sugar_PHPUnit_Framework_TestCase
{
	var $orig_list_strings = false;
	public function setUp()
    {
        $app_list_strings = array();
        if (isset($GLOBALS['app_list_strings']))
            $this->orig_list_strings = $GLOBALS['app_list_strings'];
        include "include/language/en_us.lang.php";
        $GLOBALS['app_list_strings'] = $app_list_strings;

    }
    
    public function tearDown() 
    {
       if($this->orig_list_strings)
       {
           $GLOBALS['app_list_strings'] = $this->orig_list_strings;
       } else
       {
           unset($GLOBALS['app_list_strings']);
       }
    }

    //Bug 39407
    public function testRemoveFieldFromLayoutsDocumentsException()
    {
    	$SM = new StudioModule("Documents");
        try {
            $SM->removeFieldFromLayouts("aFieldThatDoesntExist");
            $this->assertTrue(true);
        } catch (Exception $e)
        {
            $this->assertTrue(false, "Studio module threw exception :" . $e->getMessage());
        }
    }
    
}