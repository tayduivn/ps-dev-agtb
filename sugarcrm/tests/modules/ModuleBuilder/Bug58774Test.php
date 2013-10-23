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

require_once 'modules/ModuleBuilder/controller.php';

class Bug58774Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_originalRequest = array();
    protected $_originalDictionary = array();
    protected $_fileMapFiles = array();
    protected $_backedUpFiles = array();
    protected $_tearDownFiles = array(
        'custom/modules/Calls/Ext/Vardefs/vardefs.ext.php',
        'custom/modules/Calls/metadata/SearchFields.php',
        'custom/Extension/modules/Calls/Ext/Vardefs/sugarfield_duration_hours.php',        
        'cache/modules/Calls/Callvardefs.php',
    );
    
    public function setUp()
    {
        if (isset($GLOBALS['dictionary']['Call'])) {
            $this->_originalDictionary = $GLOBALS['dictionary']['Call'];
        }
        
        // Back up any current files we might have
        foreach ($this->_tearDownFiles as $file) {
            if (file_exists($file)) {
                rename($file, str_replace('.php', '-unittestbackup', $file));
                $this->_backedUpFiles[] = $file;
                // And if there are any of these files in the file map cache, 
                // handle those too
                if (SugarAutoLoader::fileExists($file)) {
                    $this->_fileMapFiles[] = $file;
                    SugarAutoLoader::delFromMap($file);
                }
            }
        }
        
        // The current user needs to be an admin user
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
        
        $this->_originalRequest = array('r' => $_REQUEST, 'p' => $_POST);
    }
    
    public function tearDown()
    {
        $_REQUEST = $this->_originalRequest['r'];
        $_POST = $this->_originalRequest['p'];
        
        SugarTestHelper::tearDown();
        
        // Remove created files
        foreach ($this->_tearDownFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
            
            if (SugarAutoLoader::fileExists($file)) {
                SugarAutoLoader::delFromMap($file);
            }
        }
        
        // Restore our backups
        foreach ($this->_backedUpFiles as $file) {
            rename(str_replace('.php', '-unittestbackup', $file), $file);
        }
        
        // Restore the file map cache
        foreach ($this->_fileMapFiles as $file) {
            SugarAutoLoader::addToMap($file);
        }
        
        // Reset the dictionary
        if (!empty($this->_originalDictionary)) {
            $GLOBALS['dictionary']['Call'] = $this->_originalDictionary;
        }
    }
    
    public function testCacheClearedAfterSavingFieldChanges()
    {
        // Setup some of the items needed in the request
        $_REQUEST = $_POST =array(
            'module' => 'ModuleBuilder',
            'action' => 'saveSugarField',
            'view_module' => 'Calls',
            'type' => 'int',
            'name' => 'duration_minutes',
            'labelValue' => 'Duration Minutes:',
            'label' => 'LBL_DURATION_MINUTES',
            'comments' => 'Call duration, minutes portion',
            'min' => '5',
            'max' => '90',
        );
        
        $controller = new ModuleBuilderController();
        $controller->action_saveSugarField();
        
        $newdefs = $this->_getNewVardefFromCache();
        
        // Handle assertions
        $this->assertNotEmpty($newdefs, "New vardef was not found");
        $this->assertTrue(isset($newdefs['fields']['duration_minutes']), "duration_minutes field not found in the vardef");
        $this->assertArrayHasKey('min', $newdefs['fields']['duration_minutes'], "Min value not saved");
        $this->assertEquals(5, $newdefs['fields']['duration_minutes']['min'], "Min did not save its value properly");
        $this->assertArrayHasKey('max', $newdefs['fields']['duration_minutes'], "Max value not saved");
        $this->assertEquals(90, $newdefs['fields']['duration_minutes']['max'], "Max did not save its value properly");
    }
    
    protected function _getNewVardefFromCache()
    {
        VardefManager::loadVardef('Calls', 'Call', true);
        return $GLOBALS['dictionary']['Call'];
    }
}