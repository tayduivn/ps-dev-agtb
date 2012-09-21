<?php
//FILE SUGARCRM flav=pro ONLY
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



require_once 'include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php';

class SugarSearchEngineMetadataHelperTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $_cacheRenamed;
    private $_cacheFile;
    private $_backupCacheFile;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');        
        
        $this->_cacheFile = sugar_cached('modules/ftsModulesCache.php');
        $this->_backupCacheFile = sugar_cached('modules/ftsModulesCache.php').'.save';

        if (file_exists($this->_cacheFile))
        {
            $this->_cacheRenamed = true;
            rename($this->_cacheFile, $this->_backupCacheFile);
        }
        else
        {
            $this->_cacheRenamed = false;
        }
    }

    public function tearDown()
    {
        if ($this->_cacheRenamed)
        {
            if (file_exists($this->_backupCacheFile))
            {
                rename($this->_backupCacheFile, $this->_cacheFile);
            }
        }
        else if (file_exists($this->_cacheFile))
        {
            unlink($this->_cacheFile);
        }
        SugarTestHelper::tearDown();
    }

    public function testGetFtsSearchFields()
    {
        $ftsFields = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule('Accounts');
        $this->assertContains('name', array_keys($ftsFields));
        $this->assertArrayHasKey('name', $ftsFields['name'], 'name key not found');

        //Pass in a sugar bean for the test
        $account = BeanFactory::getBean('Accounts', null);
        $ftsFields = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule($account);
        $this->assertContains('name', array_keys($ftsFields));
    }


    public function testGetFtsSearchFieldsForAllModules()
    {
        $ftsFieldsByModule = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsForAllModules();
        $this->assertContains('Contacts', array_keys($ftsFieldsByModule));
        $this->assertContains('first_name', array_keys($ftsFieldsByModule['Contacts']));
        $this->assertArrayHasKey('name', $ftsFieldsByModule['Accounts']['name'], 'name key not found');
    }


    public function isModuleEnabledProvider()
    {
        return array(
            array('Accounts', true),
            array('Contacts', true),
            array('BadModule', false),
            array('Notifications', false),
        );
    }

    /**
     * @dataProvider isModuleEnabledProvider
     */
    public function testIsModuleFtsEnabled($module,$actualResult)
    {
        $expected = SugarSearchEngineMetadataHelper::isModuleFtsEnabled($module);
        $this->assertEquals($expected, $actualResult);
    }

    public function testIsModuleFtsDisabled()
    {
        $this->markTestIncomplete("Need to rewrite after we completely change the admin FTS implementation");
        $disabledModules = array('Contacts', 'Cases');
        write_array_to_file(SugarSearchEngineMetadataHelper::DISABLED_MODULE_CACHE_KEY,
            $disabledModules, sugar_cached('modules/ftsModulesCache.php'));

        $ret = SugarSearchEngineMetadataHelper::isModuleFtsEnabled('Accounts');
        $this->assertTrue($ret, 'Accounts should be enabled');

        $ret = SugarSearchEngineMetadataHelper::isModuleFtsEnabled('Cases');
        $this->assertFalse($ret, 'Cases should be disabled');

        $ret = SugarSearchEngineMetadataHelper::isModuleFtsEnabled('Contacts');
        $this->assertFalse($ret, 'Contacts should be disabled');
    }

}
