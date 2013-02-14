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

class Bug61201Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        MetaDataManagerBug61201::resetMetadataCacheQueueState();
    }

    /**
     * Tests that the queue flag was set, that the queue prevents an immediate 
     * refresh and that a run queue actually fires a refresh of the cache
     * 
     * @group Bug61201
     */
    public function testMetaDataCacheQueueHandling()
    {
        $mm   = MetaDataManager::getManager();
        $data = $mm->getMetadata();
        $si   = $data['server_info'];
        
        // Set the queue
        MetaDataManager::enableCacheRefreshQueue();
        
        // Test the state of the queued flag
        $state = MetaDataManagerBug61201::getQueueState();
        $this->assertTrue($state, "MetaDataManager cache queue state was not properly set");
        
        // Try to refresh a section while queueing is on
        MetaDataManager::refreshSectionCache(MetaDataManager::MM_SERVERINFO);
        
        // Get the queue for checking its content
        $queue = MetaDataManagerBug61201::getQueue();
        $this->assertArrayHasKey('sections', $queue, "The queue does not have a sections element");
        $this->assertArrayHasKey(MetaDataManager::MM_SERVERINFO, $queue['sections'], "Server Info key of the cache queue was not set");
        
        // Get the metadata again and ensure it is the same
        $data = $mm->getMetadata();
        $this->assertEquals($si, $data['server_info'], "Meta Data has changed and it should not have");
        
        // Run the queue. This should fire the refresh jobs
        MetaDataManager::runCacheRefreshQueue();
        
        // Get the metadata again and ensure it is different now
        $data = $mm->getMetadata();
        $this->assertNotEquals($si, $data['server_info'], "Meta Data has not been refreshed and it should have been");
    }
}

class MetaDataManagerBug61201 extends MetaDataManager
{
    public static function resetMetadataCacheQueueState()
    {
        MetaDataManager::$isQueued = false;
        MetaDataManager::$queue    = array();
    }
    
    public static function getQueueState()
    {
        return MetaDataManager::$isQueued;
    }
    
    public static function getQueue()
    {
        return MetaDataManager::$queue;
    }
}