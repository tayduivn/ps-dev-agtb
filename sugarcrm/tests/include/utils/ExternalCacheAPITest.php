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
 
require_once('include/SugarCache/SugarCache.php');

class ExternalCacheAPITest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        $this->_cacheKey1   = 'test cache key 1 '.date("YmdHis");
        $this->_cacheValue1 = 'test cache value 1'.date("YmdHis");
        $this->_cacheKey2   = 'test cache key 2 '.date("YmdHis");
        $this->_cacheValue2 = 'test cache value 2 '.date("YmdHis");
        $this->_cacheKey3   = 'test cache key 3 '.date("YmdHis");
        $this->_cacheValue3 = array(
            'test cache value 3 key 1 '.date("YmdHis") => 'test cache value 3 value 1 '.date("YmdHis"),
            'test cache value 3 key 2 '.date("YmdHis") => 'test cache value 3 value 2 '.date("YmdHis"),
            'test cache value 3 key 3 '.date("YmdHis") => 'test cache value 3 value 3 '.date("YmdHis"),
            );
    }

    public function tearDown() 
    {
       // clear out the test cache if we haven't already
       if ( sugar_cache_retrieve($this->_cacheKey1) )
           sugar_cache_clear($this->_cacheKey1);
       if ( sugar_cache_retrieve($this->_cacheKey2) )
           sugar_cache_clear($this->_cacheKey2);
       if ( sugar_cache_retrieve($this->_cacheKey3) )
           sugar_cache_clear($this->_cacheKey3);
       SugarCache::$isCacheReset = false;
    }

    public function testSugarCacheValidate()
    {
        $this->assertTrue(sugar_cache_validate());
    }
    
    public function testStoreAndRetrieve()
    {
        sugar_cache_put($this->_cacheKey1,$this->_cacheValue1);
        sugar_cache_put($this->_cacheKey2,$this->_cacheValue2);
        sugar_cache_put($this->_cacheKey3,$this->_cacheValue3);
        $this->assertEquals(
            $this->_cacheValue1,
            sugar_cache_retrieve($this->_cacheKey1));
        $this->assertEquals(
            $this->_cacheValue2,
            sugar_cache_retrieve($this->_cacheKey2));
        $this->assertEquals(
            $this->_cacheValue3,
            sugar_cache_retrieve($this->_cacheKey3));
    }

    public function testStoreClearCacheKeyAndRetrieve()
    {
        sugar_cache_put($this->_cacheKey1,$this->_cacheValue1);
        sugar_cache_put($this->_cacheKey2,$this->_cacheValue2);
        sugar_cache_clear($this->_cacheKey1);
        $this->assertNotEquals(
            $this->_cacheValue1,
            sugar_cache_retrieve($this->_cacheKey1));
        $this->assertEquals(
            $this->_cacheValue2,
            sugar_cache_retrieve($this->_cacheKey2));
    }
    
    public function testStoreResetCacheAndRetrieve()
    {
        sugar_cache_put($this->_cacheKey1,$this->_cacheValue1);
        sugar_cache_put($this->_cacheKey2,$this->_cacheValue2);
        sugar_cache_reset();
        $this->assertNotEquals(
            $this->_cacheValue1,
            sugar_cache_retrieve($this->_cacheKey1));
        $this->assertNotEquals(
            $this->_cacheValue2,
            sugar_cache_retrieve($this->_cacheKey2));
    }

    public function testStoreAndRetrieveWithTTL()
    {
        sugar_cache_put($this->_cacheKey1,$this->_cacheValue1, 100);
        sugar_cache_put($this->_cacheKey2,$this->_cacheValue2, 100);
        sugar_cache_put($this->_cacheKey3,$this->_cacheValue3,100);
        $this->assertEquals(
            $this->_cacheValue1,
            sugar_cache_retrieve($this->_cacheKey1));
        $this->assertEquals(
            $this->_cacheValue2,
            sugar_cache_retrieve($this->_cacheKey2));
        $this->assertEquals(
            $this->_cacheValue3,
            sugar_cache_retrieve($this->_cacheKey3));
    }

    public function testStoreAndRetrieveWithTTLZero()
    {
        $sc = SugarCache::instance();
        $cacheStub = $this->getMock(get_class($sc), array('_setExternal'));
        $cacheStub->expects($this->never())
                       ->method('_setExternal');
        $cacheStub->set($this->_cacheKey1,$this->_cacheValue1,0);
    }

    public function testStoreAndRetrieveWithTTLNull()
    {
        $sc = SugarCache::instance();
        $cacheStub = $this->getMock(get_class($sc), array('_setExternal'));
        $cacheStub->expects($this->once())
                       ->method('_setExternal');
        $cacheStub->set($this->_cacheKey1,$this->_cacheValue1,null);
    }


    /**
     * @ticket 40797
     */
    public function testRetrieveNonExistantKeyReturnsNull()
    {
        $this->assertNull(sugar_cache_retrieve('iamlookingforakeythatainthere'));
    }
}
