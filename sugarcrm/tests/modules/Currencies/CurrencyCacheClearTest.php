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
class CurrencyCacheClearTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $testCacheFile;

    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        
        $this->testCacheFile = sugar_cached('api/metadata/metadata_unit_test.php');
        
        // Start fresh
        MetaDataManager::clearAPICache(true);
    }

    public function tearDown()
    {
        $_POST = array();
        if ( file_exists($this->testCacheFile) ) {
            @unlink($this->testCacheFile);
        }
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        
        // End fresh
        MetaDataManager::clearAPICache(true);
    }


    public function testResetMetadataCache()
    {
        // Get the private metadata manager for $platform
        $mm = MetaDataManager::getManager();
        
        // Cache file path... we will need this for tests in here
        $file = $mm->getMetadataCacheFileName();
        
        // Get the current metadata to ensure there is a cache built
        $data = $mm->getMetadata();
        
        // Assert that there is a private base metadata file
        $this->assertFileExists($file, "Metadata cache file was not created");
        $time = filemtime($file);
        
        // Test that currencies are in the metadata
        $this->assertArrayHasKey('currencies', $data, "currencies key not found in metadata");
        
        // Force a change in filemtime by sleeping. Not ideal, but it works
        sleep(1);
        
        // A save call on a Currency bean will refresh the currency section of the metadata
        $defaultCurrency = BeanFactory::newBean('Currencies');
        $defaultCurrency = $defaultCurrency->retrieve('-99');
        $defaultCurrency->save();
        
        // Test the file first
        $this->assertFileExists($file, "Metadata cache file was not found after refresh.");
        
        // Test the time on the new file
        $this->assertGreaterThan($time, filemtime($file), "Second cache file make time is not greater than the first.");
        
        // Test that currencies are still there
        $data = $mm->getMetadata();
        
        // Test that currencies are in the metadata
        $this->assertArrayHasKey('currencies', $data, "currencies key not found in metadata");
    }
}