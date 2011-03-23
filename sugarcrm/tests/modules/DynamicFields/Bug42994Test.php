<?php

class Bug42994Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $_smarty;
    private $_lang_manager;

    public function setUp()
    {
        $this->_smarty = new Sugar_Smarty();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->_lang_manager = new SugarTestLangPackCreator();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($this->_lang_manager);
    }

    public function testSetLanguageStringDependant() 
    {
        LanguageManager::clearLanguageCache('DynamicFields', $GLOBALS['current_language']);
        $mod_strings = return_module_language($GLOBALS['current_language'], 'DynamicFields');        
        $this->assertTrue(array_key_exists('LBL_DEPENDENT', $mod_strings));

        $this->_lang_manager->setModString('LBL_DEPENDENT','XXDependentXX','DynamicFields');
        $this->_lang_manager->save();
        $output = $this->_smarty->fetch('modules/DynamicFields/templates/Fields/Forms/coreDependent.tpl');

        $this->assertContains('XXDependentXX', $output);
    }
    
    public function testSetLanguageStringVisible() 
    {
        LanguageManager::clearLanguageCache('DynamicFields', $GLOBALS['current_language']);
        $mod_strings = return_module_language($GLOBALS['current_language'], 'DynamicFields');        
        $this->assertTrue(array_key_exists('LBL_VISIBLE_IF', $mod_strings));

        $this->_lang_manager->setModString('LBL_VISIBLE_IF','XXVisible ifXX','DynamicFields');
        $this->_lang_manager->save();
        $output = $this->_smarty->fetch('modules/DynamicFields/templates/Fields/Forms/coreDependent.tpl');

        $this->assertContains('XXVisible ifXX', $output);
    }

    public function testSetLanguageStringEnforced() 
    {
        LanguageManager::clearLanguageCache('DynamicFields', $GLOBALS['current_language']);
        $mod_strings = return_module_language($GLOBALS['current_language'], 'DynamicFields');        
        $this->assertTrue(array_key_exists('LBL_ENFORCED', $mod_strings));

        if ($GLOBALS['sugar_flavor'] == 'EEN') {
            $this->_lang_manager->setModString('LBL_ENFORCED','XXEnforcedXX','DynamicFields');
            $this->_lang_manager->save();
            $output = $this->_smarty->fetch('modules/DynamicFields/templates/Fields/Forms/coreDependent.tpl');

            $this->assertContains('XXEnforcedXX', $output);            
        }
    }

}
