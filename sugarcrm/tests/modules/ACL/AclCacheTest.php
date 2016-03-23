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

require_once 'modules/ACL/AclCache.php';

class AclCacheTest extends PHPUnit_Framework_TestCase
{
    /** @var AclCache */
    private $cache;

    /*
     * Needed to determine if we should preserve session, or if we're creating a fake one.
     */
    private $fake_session = false;

    protected function setUp()
    {
        $this->cache = AclCache::getInstance();
        $this->cache->clear();

        if (!session_id()) {
            $this->fake_session = true;
            session_id(create_guid());
        }
    }

    protected function tearDown()
    {
        if ($this->fake_session) {
            // If we're using a fake session, we need to reset the id
            session_id('');
            $this->fake_session = false;
        }

        if ($this->cache) {
            $this->cache->clear();
        }
    }

    /**
     * @ticket BR-2747
     */
    public function testUpdate()
    {
        $this->cache->store('user_1', 'test', 'x');
        $data = $this->cache->retrieve('user_1', 'test');
        $this->assertEquals('x', $data);

        $this->cache->store('user_2', 'test', 'x');
        $data = $this->cache->retrieve('user_2', 'test');
        $this->assertEquals('x', $data);

        $this->cache->store('user_1', 'test', 'y');
        $data = $this->cache->retrieve('user_1', 'test');
        $this->assertEquals('y', $data);

        $data = $this->cache->retrieve('user_2', 'test');
        $this->assertEquals('x', $data, 'The cached ACL data for user #2 should have remained unchanged');
    }

    public function testClear()
    {
        $this->cache->store('user', 'test', 'x');
        $this->cache->clear();
        $value = $this->cache->retrieve('user', 'test');
        $this->assertNull($value);
    }
}
