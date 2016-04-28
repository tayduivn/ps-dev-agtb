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

require_once 'include/SugarCache/SugarCacheMemory.php';

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
     * @var \SugarCacheAbstract
     */
    protected $originSugarCache;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->cacheObserver = new CacheObserver(new NullLogger());
        $this->workload = new Workload('testRoute', array(), array());
        $this->originSugarCache = \SugarTestReflection::getProtectedValue('SugarCache', '_cacheInstance');
        $sugarCache = $this->getMock('SugarCacheMemory');
        \SugarTestReflection::setProtectedValue('SugarCache', '_cacheInstance', $sugarCache);
        \SugarTestHelper::setUp('files');
        \SugarTestHelper::saveFile('config_override.php');
        if (!\SugarAutoLoader::fileExists('config_override.php')) {
            sugar_touch('config_override.php');
        }
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        \SugarTestReflection::setProtectedValue('SugarCache', '_cacheInstance', $this->originSugarCache);
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

    /**
     * Testing is resets config.
     */
    public function testConfigResetting()
    {
        if (!is_writable('config_override.php')) {
            $this->markTestSkipped('File "config_override.php" is not writable.');
        }
        $key = 'test_config_key' . rand(1000, 9999);
        $value = array(rand(1000, 9999), rand(1000, 9999));
        $fileString = "<?php\n\n" . override_value_to_string_recursive2('sugar_config', $key, $value);
        sugar_file_put_contents('config_override.php', $fileString);

        $this->cacheObserver->onRun($this->workload, \SchedulersJob::JOB_SUCCESS);

        $this->assertEquals($value, \SugarConfig::getInstance()->get($key));
    }
}
