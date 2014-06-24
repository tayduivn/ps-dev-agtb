<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
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