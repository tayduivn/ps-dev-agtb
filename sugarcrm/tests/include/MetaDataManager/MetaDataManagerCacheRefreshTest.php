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

require_once 'include/MetaDataManager/MetaDataManager.php';
require_once 'modules/Administration/QuickRepairAndRebuild.php';

/**
 * Tests metadata manager caching and refreshing. This will be a somewhat slow
 * test as there will be significant file I/O due to nuking and rewriting cache
 * files.
 */
class MetaDataManagerCacheRefreshTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $accountsFile = 'modules/Accounts/clients/mobile/views/herfy/herfy.php';
    protected $casesFile = 'modules/Cases/clients/mobile/views/fandy/fandy.php';
    
    public function setUp()
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user', array(true, true));
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
        
        // Clean up test files
        $c = 0;
        foreach (array($this->accountsFile, $this->casesFile) as $file) {
            $save = $c > 0;
            if (file_exists($file)) {
                unlink($file);
                rmdir(dirname($file));
                SugarAutoLoader::delFromMap($file, $save);
            }
            $c++;
        }
    }
    
    public static function tearDownAfterClass()
    {
        // After all is said and done, reset our caches to the beginning
        MetaDataManager::clearAPICache();
    }
    
    /**
     * Tests the metadatamanager getManager method gets the right manager
     * 
     * @group MetaDataManager
     * @dataProvider managerTypeProvider
     * @param string $platform
     * @param string $manager
     */
    public function testFactoryReturnsProperManager($platform, $manager)
    {
        $mm = MetaDataManager::getManager($platform);
        $this->assertInstanceOf($manager, $mm, "MetaDataManager for $platform was not an instance of $manager");
    }

    /**
     * Tests delete and rebuild of cache files
     * 
     * @group MetaDataManager
     */
    public function testRefreshCacheCreatesNewCacheFiles()
    {
        // Start by wiping out everything
        MetaDataManager::clearAPICache();
        $basePrivate = sugar_cached('api/metadata/metadata_base_private.php');
        $basePublic  = sugar_cached('api/metadata/metadata_base_public.php');
        $this->assertFileNotExists($basePrivate, "Private base cache file found and it shouldn't be.");
        $this->assertFileNotExists($basePublic, "Public base cache file found and it shouldn't be.");
        
        // Refresh the cache and ensure that there are file in place
        MetaDataManager::refreshCache();
        $this->assertFileExists($basePrivate, "Private base cache file not found.");
        $this->assertFileExists($basePublic, "Public base cache file not found.");
    }

    /**
     * Tests that the cache files for a platform were refreshed
     * 
     * @group MetaDataManager
     * @dataProvider platformProvider
     * @param string $platform
     */
    public function testRefreshCacheCreatesNewCacheFilesForPlatform($platform)
    {
        // Get the private metadata manager for $platform
        $mm = MetaDataManager::getManager($platform);
        
        // Get the current metadata server info server time
        $data = $mm->getMetadata();
        $this->assertNotEmpty($data['server_info']['server_time'], "Server info was empty.");
        $time = $data['server_info']['server_time'];
        
        // This will wipe out and rebuild the private metadata cache for $platform
        $mm->rebuildCache();
        
        // Test the file first
        $file = $mm->getMetadataCacheFileName();
        $this->assertFileExists($file, "Private cache file for $platform was not found after refresh.");
        
        // Get the new metadata and test against what we had before
        $data = $mm->getMetadata();
        $this->assertNotEmpty($data['server_info']['server_time'], "Second server info was empty.");
        $this->assertNotEquals($time, $data['server_info']['server_time'], "Server info times are the same.");
    }

    /**
     * Essentially the same test as directly hitting metadata manager, except 
     * this tests Quick Repairs access to it.
     * 
     * @group MetaDataManager
     */
    public function testQuickRepairRefreshesCache()
    {
        $repair = new RepairAndClear();
        
        // Wipe out the cache
        $repair->clearMetadataAPICache();
        $basePrivate = sugar_cached('api/metadata/metadata_base_private.php');
        $basePublic  = sugar_cached('api/metadata/metadata_base_public.php');
        $this->assertFileNotExists($basePrivate, "Private base cache file found and it shouldn't be.");
        $this->assertFileNotExists($basePublic, "Public base cache file found and it shouldn't be.");
        
        // Refresh the cache and ensure that there are file in place
        $repair->repairMetadataAPICache();
        $this->assertFileExists($basePrivate, "Private base cache file not found.");
        $this->assertFileExists($basePublic, "Public base cache file not found.");
    }

    /**
     * Tests that a section of metadata was updated
     * 
     * @group MetaDataManager
     */
    public function testSectionCacheRefreshes()
    {
        $mmPri = MetaDataManager::getManager('base');
        $mmPub = MetaDataManager::getManager('base', true);
        
        // Get our private and public metadata
        $mdPri = $mmPri->getMetadata();
        $mdPub = $mmPub->getMetadata();
        
        MetaDataManager::refreshSectionCache(MetaDataManager::MM_SERVERINFO, array('base'));
        
        // Get the newest metadata, which should be different
        $dataPri = $mmPri->getMetadata();
        $dataPub = $mmPub->getMetadata();
        
        $this->assertNotEmpty($mdPri['server_info'], "Server info from the initial fetch is empty");
        $this->assertNotEmpty($dataPri['server_info'], "Server info from the second fetch is empty");
        $this->assertNotEquals($mdPri['server_info'], $dataPri['server_info'], "First and second metadata server_info sections are the same");
        $this->assertEquals($mdPub['_hash'], $dataPub['_hash'], "Hashes from the public metadata are different");
    }
    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * Tests module data refreshing
     * 
     * @group MetaDataManager
     */
    public function testSectionModuleCacheRefreshes()
    {
        $mm = MetaDataManager::getManager('mobile');
        
        // Get our private and public metadata
        $md = $mm->getMetadata();
        
        // Add two things: a new view to Accounts and a new View to Cases. Test
        // that the Accounts view got picked up and that the Notes view didn't.
        sugar_mkdir(dirname($this->accountsFile));
        sugar_mkdir(dirname($this->casesFile));
        
        $casesFile = '<?php
$viewdefs[\'Cases\'][\'mobile\'][\'view\'][\'fandy\'] = array(\'test\' => \'test this\');';

        $AccountsFile = '<?php
$viewdefs[\'Accounts\'][\'mobile\'][\'view\'][\'herfy\'] = array(\'test\' => \'test this\');';
        sugar_file_put_contents($this->casesFile, $casesFile);
        sugar_file_put_contents($this->accountsFile, $AccountsFile);
        SugarAutoLoader::addToMap($this->casesFile, false);
        SugarAutoLoader::addToMap($this->accountsFile); // Only save the file map cache on the second add
        
        // Refresh the modules cache
        MetaDataManager::refreshModulesCache(array('Accounts'), array('mobile'));
        
        // Get the newest metadata, which should be different
        $data = $mm->getMetadata();
        
        // Basic assertions
        $this->assertNotEmpty($md['modules']['Accounts'], "Accounts module data from the initial fetch is empty");
        $this->assertNotEmpty($data['modules']['Accounts'], "Accounts module data the second fetch is empty");
        
        // Assertions of state prior to refresh
        $this->assertArrayNotHasKey('herfy', $md['modules']['Accounts']['views'], "The test view was found in the original Accounts metadata.");
        $this->assertArrayNotHasKey('fandy', $md['modules']['Cases']['views'], "The test view was found in the original Cases metadata.");
        
        // Assertions of state after refresh
        $this->assertNotEquals($md['modules']['Accounts'], $data['modules']['Accounts'], "First and second metadata Accounts module sections are the same");
        $this->assertEquals($md['modules']['Cases'], $data['modules']['Cases'], "First and second metadata Cases module sections are different");
        $this->assertNotEmpty($data['modules']['Accounts']['views']['herfy'], "The test view was not found in the refreshed Accounts metadata.");
        $this->assertArrayNotHasKey('fandy', $md['modules']['Cases']['views'], "The test view was found in the refreshed Cases metadata.");
    }
    //END SUGARCRM flav=pro ONLY
    public function managerTypeProvider()
    {
        return array(
            //BEGIN SUGARCRM flav=ent ONLY
            array('platform' => 'portal', 'manager' => 'MetaDataManagerPortal'),
            //END SUGARCRM flav=ent ONLY
            //BEGIN SUGARCRM flav=pro ONLY
            array('platform' => 'mobile', 'manager' => 'MetaDataManagerMobile'),
            //END SUGARCRM flav=pro ONLY
            array('platform' => 'base', 'manager' => 'MetaDataManager'),
        );
    }
    
    public function platformProvider()
    {
        return array(
            //BEGIN SUGARCRM flav=ent ONLY
            array('platform' => 'portal'),
            //END SUGARCRM flav=ent ONLY
            //BEGIN SUGARCRM flav=pro ONLY
            array('platform' => 'mobile'),
            //END SUGARCRM flav=pro ONLY
            array('platform' => 'base'),
        );
    }
}