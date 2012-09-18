<?php
//FILE SUGARCRM flav=ent ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'modules/ModuleBuilder/Module/StudioModule.php';

/**
 * Make a test StudioModule class so as not to destroy ALL of the metadata. This
 * is an ok test since the report is that portal viewdefs aren't updated but all
 * other viewdefs are.
 */
class Bug56389SugarModule extends StudioModule
{
    /**
     * Override the default remove method to include only portal viewdefs field
     * deletes
     * 
     * @param $fieldName
     */
    function removeFieldFromLayouts($fieldName)
    {
    	require_once("modules/ModuleBuilder/parsers/ParserFactory.php");
    	$sources = $this->getPortalLayoutSources();
        foreach ($sources as $defs)
        {
            //If this module type doesn't support a given metadata type, we will get an exception from getParser()
            try {
                $parser = ParserFactory::getParser($defs['type'], $this->module);
                if ($parser && method_exists($parser, 'removeField') && $parser->removeField($fieldName)) {
                    // don't populate from $_REQUEST, just save as is...
                    $parser->handleSave(false);
                }
            } catch(Exception $e){}
        }
    }
}

/**
 * Bug 56389 - Deleted module fields do not cascade to portal view defs
 */
class Bug56389Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $moduleToTest = 'Cases';
    protected $filesBackedUp = array();
    protected $filesToTest = array(
        'modules/Cases/clients/portal/views/list/list.php',
        'modules/Cases/clients/portal/views/edit/edit.php',
        'modules/Cases/clients/portal/views/detail/detail.php',
    );
    protected $filesToTearDown = array();
    protected $fieldToTest = 'name';
    
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
        
        // Run through our test files and make sure customs, working and core
        // metadata files are backed up. Core files will restore regardless, but
        // others will only restore if there was an original file.
        foreach ($this->filesToTest as $file) {
            // Set aside custom and working files.
            $custom = "custom/$file";
            $working = "custom/working/$file";
            
            $filesets = array($custom, $working);
            foreach ($filesets as $filepath) {
                $backup = $filepath . '.unittest';
                            
                // Backup custom first
                if (file_exists($filepath)) {
                    if (rename($filepath, $backup)) {
                        $this->filesBackedUp[] = $backup;
                    }
                } else {
                    $this->filesToTearDown[] = $filepath;
                }
            }
            
            // Now do core metadata files
            $backup = $file . '.unittest';
            if (file_exists($file)) {
                if (copy($file, $backup)) {
                    $this->filesBackedUp[] = $backup;
                }
            }
        }
    }
    
    public function tearDown()
    {
        // Customs were renamed, defaults were copied, both are just moved back
        foreach ($this->filesBackedUp as $file) {
            $restore = str_replace('.unittest', '', $file);
            rename($file, $restore);
        }
        
        // Kill of any custom files that were made
        foreach ($this->filesToTearDown as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        SugarTestHelper::tearDown();
    }

    /**
     * Checks whether a fieldname exists in a viewdef
     * 
     * @param string $fieldname The fieldname
     * @param array $defs The defs, as of view type
     * @return bool
     */
    protected function _fieldExistsInDefs($fieldname, $defs) {
        foreach ($defs['panels'] as $panel) {
            foreach ($panel['fields'] as $field) {
                if ((is_array($field) && isset($field['name']) && $field['name'] == $fieldname) || $field == $fieldname) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * @group Bug56389
     */
    public function testDeleteFieldRemovesPortalViewDefs()
    {
        // First test, get each def and check the test fields
        foreach ($this->filesToTest as $testfile) {
            require $testfile;
            $type = key($viewdefs[$this->moduleToTest]['portal']['view']);
            $exists = $this->_fieldExistsInDefs($this->fieldToTest, $viewdefs[$this->moduleToTest]['portal']['view'][$type]);
            $this->assertTrue($exists, "$this->fieldToTest does not exists in $type layout field for $this->moduleToTest");
            unset($viewdefs);
        }
        
        // Now handle the delete of the fields
        $sm = new Bug56389SugarModule($this->moduleToTest);
        $sm->removeFieldFromLayouts($this->fieldToTest);
        
        // Now test again, inside of custom files though since that is where 
        // changes are saved
        foreach ($this->filesToTest as $testfile) {
            require "custom/$testfile";
            $type = key($viewdefs[$this->moduleToTest]['portal']['view']);
            $exists = $this->_fieldExistsInDefs($this->fieldToTest, $viewdefs[$this->moduleToTest]['portal']['view'][$type]);
            $this->assertFalse($exists, "$this->fieldToTest DOES exists in $type layout field for $this->moduleToTest");
            unset($viewdefs);
        }
    }
}