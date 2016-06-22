<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTests\JobQueue\Observer;

use Psr\Log\NullLogger;
use Sugarcrm\Sugarcrm\JobQueue\Observer\Cache as CacheObserver;
use Sugarcrm\Sugarcrm\JobQueue\Workload\Workload;

class CacheTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Workload
     */
    protected $workload;

    /**
     * @var CacheObserver
     */
    protected $cacheObserver;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->cacheObserver = new CacheObserver(new NullLogger());
        $this->workload = new Workload('testRoute', array(), array());
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Testing is resets cache.
     */
    public function testCacheResetting()
    {
        $key = 'test_cache_key' . rand(1000, 9999);
        $value = rand(1000, 9999);
        \SugarCache::instance()->set($key, $value);

        $this->cacheObserver->onRun($this->workload, \SchedulersJob::JOB_SUCCESS);

        $this->assertNull(\SugarCache::instance()->get($key), 'Cache not cleared');
    }
}
