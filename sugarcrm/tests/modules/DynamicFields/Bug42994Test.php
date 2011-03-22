<?php

class Bug42994Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testSetLanguageStringDependant() 
    {
        LanguageManager::clearLanguageCache('DynamicFields', $GLOBALS['current_language']);
        $mod_strings = return_module_language($GLOBALS['current_language'], 'DynamicFields');        
        $this->assertTrue(array_key_exists('LBL_DEPENDENT', $mod_strings));
    }
    
    public function testSetLanguageStringVisible() 
    {
        LanguageManager::clearLanguageCache('DynamicFields', $GLOBALS['current_language']);
        $mod_strings = return_module_language($GLOBALS['current_language'], 'DynamicFields');        
        $this->assertTrue(array_key_exists('LBL_VISIBLE_IF', $mod_strings));
    }

    public function testSetLanguageStringEnforced() 
    {
        LanguageManager::clearLanguageCache('DynamicFields', $GLOBALS['current_language']);
        $mod_strings = return_module_language($GLOBALS['current_language'], 'DynamicFields');        
        $this->assertTrue(array_key_exists('LBL_ENFORCED', $mod_strings));
    }

}
